<?php

declare(strict_types=1);

use MqttRealtime\Domain\TelemetryMessage;
use Ramsey\Uuid\Uuid;

it('serializes and restores a telemetry message', function (): void {
    $message = new TelemetryMessage(
        eventId: Uuid::uuid7()->toString(),
        sequence: 1,
        timestamp: '2026/08/25 14:30:01',
        value: 27,
    );

    $restored = TelemetryMessage::fromJson($message->toJson());

    expect($restored->eventId)->toBe($message->eventId)
        ->and($restored->sequence)->toBe(1)
        ->and($restored->value)->toBe(27);
});

it('rejects values outside the assignment range', function (): void {
    new TelemetryMessage(
        eventId: Uuid::uuid7()->toString(),
        sequence: 1,
        timestamp: '2026/08/25 14:30:01',
        value: 31,
    );
})->throws(InvalidArgumentException::class);
