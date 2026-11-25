<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PasswordResetRequested extends Notification
{
    use Queueable;

    public function __construct(private User $account) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->account->displayName().' የይለፍ ቃል መልሶ ማግኛ ጠይቋል',
            'body' => 'ከባለሙያው ገጽ የይለፍ ቃል መቀየር ይችላሉ።',
            'url' => route('admin.experts.show', $this->account, false),
        ];
    }
}
