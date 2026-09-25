<?php

use App\Notifications\NewLeadNotification;
use Illuminate\Support\Facades\Notification;

test('leads:mail-test envia a notificação para o destinatário configurado', function () {
    Notification::fake();
    config(['leads.notify_email' => 'corretor@augepanamby.net.br']);

    $this->artisan('leads:mail-test')->assertSuccessful();

    Notification::assertSentOnDemandTimes(NewLeadNotification::class, 1);
    Notification::assertSentOnDemand(
        NewLeadNotification::class,
        fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'corretor@augepanamby.net.br'
    );
});

test('leads:mail-test aceita destinatário explícito', function () {
    Notification::fake();

    $this->artisan('leads:mail-test', ['email' => 'wagner.duarte@vendasvitta.com.br'])->assertSuccessful();

    Notification::assertSentOnDemand(
        NewLeadNotification::class,
        fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'wagner.duarte@vendasvitta.com.br'
    );
});

test('leads:mail-test com --dry-run renderiza os dois formatos sem enviar', function () {
    Notification::fake();

    $this->artisan('leads:mail-test', ['--dry-run' => true])
        ->expectsOutputToContain('view [html] emails.lead-notification')
        ->expectsOutputToContain('view [text] emails.lead-notification-text')
        ->assertSuccessful();

    Notification::assertNothingSent();
});

test('leads:mail-test falha quando não há destinatário configurado', function () {
    Notification::fake();
    config(['leads.notify_email' => '']);

    $this->artisan('leads:mail-test')->assertFailed();

    Notification::assertNothingSent();
});
