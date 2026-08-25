<?php

declare(strict_types=1);

namespace MqttRealtime\Infrastructure\Mqtt;

interface MqttTransport
{
    public function connect(): void;

    public function disconnect(): void;

    public function publish(string $topic, string $payload, int $qos): void;

    /** @param callable(string,string):void $handler */
    public function subscribe(string $topic, int $qos, callable $handler): void;

    public function loop(): void;

    public function interrupt(): void;
}
