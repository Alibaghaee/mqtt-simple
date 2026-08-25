#!/usr/bin/env php
<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap.php';

use MqttRealtime\Support\ContainerFactory;

$config = ContainerFactory::config();
$logger = ContainerFactory::logger($config);
$publisher = ContainerFactory::publisher($config, $logger);
$running = true;

pcntl_async_signals(true);
pcntl_signal(SIGINT, function () use (&$running): void {
    $running = false;
});
pcntl_signal(SIGTERM, function () use (&$running): void {
    $running = false;
});

$logger->info('MQTT publisher started.');

try {
    $publisher->run(fn (): bool => ! $running);
} catch (Throwable $exception) {
    $logger->critical('MQTT publisher stopped unexpectedly.', ['exception' => $exception]);
    exit(1);
}

$logger->info('MQTT publisher stopped gracefully.');
