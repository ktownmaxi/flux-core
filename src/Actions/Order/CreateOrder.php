<?php

namespace FluxErp\Actions\Order;

use FluxErp\Actions\BaseAction;
use FluxErp\Http\Requests\CreateOrderRequest;
use FluxErp\Models\Currency;
use FluxErp\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class CreateOrder extends BaseAction
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->rules = (new CreateOrderRequest())->rules();
    }

    public static function models(): array
    {
        return [Order::class];
    }

    public function execute(): Order
    {
        $this->data['currency_id'] = $this->data['currency_id'] ?? Currency::query()->first()?->id;
        $addresses = Arr::pull($this->data, 'addresses', []);

        $order = new Order($this->data);
        if ($order->shipping_costs_net_price) {
            $order->shipping_costs_vat_rate_percentage = 0.190000000;   // TODO: Make this percentage NOT hardcoded!
            $order->shipping_costs_gross_price = net_to_gross(
                $order->shipping_costs_net_price,
                $order->shipping_costs_vat_rate_percentage
            );
            $order->shipping_costs_vat_price = bcsub(
                $order->shipping_costs_gross_price,
                $order->shipping_costs_net_price
            );
        }
        $order->save();

        if ($addresses) {
            $order->addresses()->attach($addresses);
        }

        return $order->refresh();
    }

    public function validate(): static
    {
        $validator = Validator::make($this->data, $this->rules);
        $validator->addModel(new Order());

        $this->data = $validator->validate();

        return $this;
    }
}
