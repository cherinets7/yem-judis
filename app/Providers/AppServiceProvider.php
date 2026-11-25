<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        ResetPassword::toMailUsing(function (object $notifiable, string $token): MailMessage {
            $url = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            return (new MailMessage)
                ->subject('የይለፍ ቃል መቀየሪያ')
                ->line('የይለፍ ቃል መልሶ ማግኛ ተጠይቋል። ከታች ያለውን አገናኝ ይጫኑ።')
                ->action('የይለፍ ቃል ቀይር', $url)
                ->line('አገናኙ ለአንድ ሰዓት ይሰራል። ይህንን ካልጠየቁ መልዕክቱን ችላ ይበሉ።');
        });
    }
}
