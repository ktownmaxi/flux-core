<?php

namespace FluxErp\Notifications\QueueMonitor\Job;

use FluxErp\Contracts\HasToastNotification;
use FluxErp\Models\QueueMonitor;
use FluxErp\Notifications\Notification;
use FluxErp\Support\Notification\BroadcastNowChannel;
use FluxErp\Support\Notification\ToastNotification\ToastNotification;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Channels\MailChannel;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\WebPush\WebPushMessage;
use Ramsey\Uuid\Uuid;

class JobFinishedNotification extends Notification implements HasToastNotification
{
    public function __construct(public QueueMonitor $model)
    {
        $this->id = Uuid::uuid5(Uuid::NAMESPACE_URL, $this->model->job_batch_id ?? $this->model->job_id);
    }

    public function toArray(object $notifiable): array
    {
        return $this->toToastNotification($notifiable)->toArray();
    }

    public function toMail(object $notifiable): MailMessage
    {
        return $this->toToastNotification($notifiable)->toMail();
    }

    public function toToastNotification(object $notifiable): ToastNotification
    {
        return ToastNotification::make()
            ->id($this->id)
            ->notifiable($notifiable)
            ->title(__(':job_name is finished', ['job_name' => __($this->model->getJobName())]))
            ->description(
                __(':time elapsed', ['time' => $this->model->getElapsedInterval()]) .
                ($this->model->message ? '<br>' . $this->model->message : '')
            )
            ->persistent()
            ->accept(unserialize($this->model->accept) ?: null)
            ->reject(unserialize($this->model->reject) ?: null)
            ->progress($this->model->jobBatch?->progress ?? $this->model->progress)
            ->attributes([
                'state' => $this->model->state,
            ]);
    }

    public function toWebPush(object $notifiable): ?WebPushMessage
    {
        return $this->toToastNotification($notifiable)->toWebPush($notifiable);
    }

    public function via(object $notifiable): array
    {
        $via = [BroadcastNowChannel::class, DatabaseChannel::class];
        if ($this->model
            ->queueMonitorables()
            ->where('queue_monitorable_type', morph_alias($notifiable::class))
            ->where('queue_monitorable_id', $notifiable->id)
            ->where('notify_on_finish', true)
            ->exists()
        ) {
            $via[] = MailChannel::class;
        }

        return $via;
    }
}
