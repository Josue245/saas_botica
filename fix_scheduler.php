<?php
$path = 'routes/console.php';
$c = file_get_contents($path);

if (strpos($c, 'CheckExpiredSubscriptionsJob') !== false) {
    echo "Job ya registrado\n";
} else {
    $c = str_replace(
        "<?php",
        "<?php\nuse App\Jobs\CheckExpiredSubscriptionsJob;",
        $c
    );
    $c = rtrim($c) . "\n\nSchedule::job(new CheckExpiredSubscriptionsJob)->dailyAt('02:00');\n";
    file_put_contents($path, $c);
    echo "Job registrado en scheduler OK\n";
}
