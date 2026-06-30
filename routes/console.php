<?php
use App\Jobs\CheckExpiredSubscriptionsJob;

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new CheckExpiredSubscriptionsJob)->dailyAt('02:00');

// Backup diario de todos los tenants a las 3am
Schedule::command('tenant:backup')->dailyAt('03:00');

// Verificar suscripciones vencidas diariamente a las 2am  
// Schedule::job(new App\Jobs\CheckExpiredSubscriptionsJob)->dailyAt('02:00');
