#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use MqttRealtime\Support\ContainerFactory;

$config = ContainerFactory::config();
$logger = ContainerFactory::logger($config);
$subscriber = ContainerFactory::subscriber($config, $logger);
$running = true;

pcntl_async_signals(true);
pcntl_signal(SIGINT, function () use (&$running): void {
    $running = false;
});
pcntl_signal(SIGTERM, function () use (&$running): void {
    $running = false;
});

$logger->info('MQTT subscriber started.');

try {
    $subscriber->run(fn (): bool => ! $running);
} catch (Throwable $exception) {
    $logger->critical('MQTT subscriber stopped unexpectedly.', ['exception' => $exception]);
    exit(1);
}

$logger->info('MQTT subscriber stopped gracefully.');
