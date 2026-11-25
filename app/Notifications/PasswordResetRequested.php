<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetRequested extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private User $account) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->account->displayName().' የይለፍ ቃል ጠይቋል')
            ->line('ከባለሙያው ገጽ የይለፍ ቃል መቀየር ይችላሉ።')
            ->action('ባለሙያውን ክፈት', route('admin.experts.show', $this->account));
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
