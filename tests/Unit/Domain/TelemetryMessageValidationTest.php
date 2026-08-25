<?php

declare(strict_types=1);

use MqttRealtime\Domain\TelemetryMessage;
use Ramsey\Uuid\Uuid;

it('rejects invalid JSON payloads', function (): void {
    TelemetryMessage::fromJson('{not-json');
})->throws(JsonException::class);

it('rejects payloads with missing required fields', function (): void {
    TelemetryMessage::fromJson(json_encode([
        'event_id' => Uuid::uuid7()->toString(),
        'sequence' => 1,
        'timestamp' => '2026/08/25 17:00:00',
    ], JSON_THROW_ON_ERROR));
})->throws(InvalidArgumentException::class, 'Missing field: value');

it('rejects a non-positive sequence', function (): void {
    new TelemetryMessage(
        eventId: Uuid::uuid7()->toString(),
        sequence: 0,
        timestamp: '2026/08/25 17:00:00',
        value: 25,
    );
})->throws(InvalidArgumentException::class);
