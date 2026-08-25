<?php

declare(strict_types=1);

namespace MqttRealtime\Domain;

use InvalidArgumentException;
use JsonException;
use Ramsey\Uuid\Uuid;

final class TelemetryMessage
{
    public function __construct(
        public string $eventId,
        public int $sequence,
        public string $timestamp,
        public int $value,
    ) {
        if (! Uuid::isValid($eventId)) {
            throw new InvalidArgumentException('eventId must be a valid UUID.');
        }

        if ($sequence < 1) {
            throw new InvalidArgumentException('sequence must be greater than zero.');
        }

        if ($value < 20 || $value > 30) {
            throw new InvalidArgumentException('value must be between 20 and 30.');
        }
    }

    /** @throws JsonException */
    public function toJson(): string
    {
        return json_encode([
            'event_id' => $this->eventId,
            'sequence' => $this->sequence,
            'timestamp' => $this->timestamp,
            'value' => $this->value,
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
    }

    /** @throws JsonException */
    public static function fromJson(string $payload): self
    {
        $data = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);

        if (! is_array($data)) {
            throw new InvalidArgumentException('MQTT payload must be a JSON object.');
        }

        foreach (['event_id', 'sequence', 'timestamp', 'value'] as $field) {
            if (! array_key_exists($field, $data)) {
                throw new InvalidArgumentException("Missing field: {$field}");
            }
        }

        return new self(
            eventId: (string) $data['event_id'],
            sequence: (int) $data['sequence'],
            timestamp: (string) $data['timestamp'],
            value: (int) $data['value'],
        );
    }
}
