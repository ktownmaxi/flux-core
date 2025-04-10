<?php

namespace FluxErp\Invokable;

use Cron\CronExpression;
use FluxErp\Actions\Order\ReplicateOrder;
use FluxErp\Console\Scheduling\Repeatable;
use FluxErp\Enums\OrderTypeEnum;
use FluxErp\Models\Order;
use FluxErp\Models\OrderType;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Traits\LogsActivity;
use Throwable;

class ProcessSubscriptionOrder implements Repeatable
{
    public function __invoke(int|string $orderId, int|string $orderTypeId): bool
    {
        $order = Order::query()
            ->whereKey($orderId)
            ->first();

        $orderType = OrderType::query()
            ->whereKey($orderTypeId)
            ->first();

        if (! $order || ! $orderType) {
            return false;
        }

        if (! in_array(
            $order->orderType->order_type_enum,
            [OrderTypeEnum::Subscription, OrderTypeEnum::PurchaseSubscription]
        )) {
            return false;
        }

        // Update parent_id and performance period
        $latestChild = $order->children()
            ->select(['id', 'system_delivery_date_end'])
            ->orderBy('system_delivery_date_end', 'DESC')
            ->first();
        $order->parent_id = $order->id;
        $order->order_type_id = $orderType->id;
        $order->system_delivery_date = $latestChild?->system_delivery_date_end?->addDay() ??
            $order->system_delivery_date ?? $order->order_date;

        $currentDate = now();
        if ($currentDate->startOfDay()->equalTo($order->system_delivery_date)) {
            $order->system_delivery_date_end = $currentDate->addDay();
        } elseif ($currentDate->startOfDay()->isBefore($order->system_delivery_date)) {
            $order->system_delivery_date_end = $order->system_delivery_date->copy()->addDay();
        } else {
            $order->system_delivery_date_end = $currentDate;
        }

        try {
            ReplicateOrder::make($order)->validate()->execute();
        } catch (Throwable $e) {
            $activity = activity()
                ->event(static::class)
                ->byAnonymous();

            if (in_array(LogsActivity::class, class_uses_recursive($order))) {
                $activity->performedOn($order);
            }

            if ($e instanceof ValidationException) {
                $activity->withProperties(['data' => $order, 'errors' => $e->errors()]);
            }

            $activity->log(class_basename($e));

            return false;
        }

        return true;
    }

    public static function defaultCron(): ?CronExpression
    {
        return null;
    }

    public static function description(): ?string
    {
        return 'Process given Subscription Order.';
    }

    public static function isRepeatable(): bool
    {
        return true;
    }

    public static function name(): string
    {
        return class_basename(static::class);
    }

    public static function parameters(): array
    {
        return [
            'orderId' => null,
            'orderTypeId' => null,
        ];
    }
}
