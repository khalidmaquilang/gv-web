<?php

declare(strict_types=1);

namespace App\Filament\Components\Notification;

use App\Features\User\Models\User;
use Filament\Notifications\Events\DatabaseNotificationsSent;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Collection;

class Notification extends FilamentNotification
{
    protected ?BaseNotification $notification = null;

    public function notification(BaseNotification $notification): static
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * @param  Model | Authenticatable | Collection<int, User> | array<Model | Authenticatable>  $users
     */
    public function sendToDatabase(Model|Authenticatable|Collection|array $users, bool $isEventDispatched = false): static
    {
        if (! is_iterable($users)) {
            $users = [$users];
        }

        foreach ($users as $user) {
            $user->notify($this->toDatabase());

            if ($this->notification instanceof \Illuminate\Notifications\Notification) {
                $user->notify($this->notification);
            }

            if ($isEventDispatched) {
                DatabaseNotificationsSent::dispatch($user);
            }
        }

        return $this;
    }
}
