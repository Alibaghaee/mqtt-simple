<?php

declare(strict_types=1);

use MqttRealtime\Infrastructure\Clock\RandomValueGenerator;

it('generates values only inside the frontend gauge range', function (): void {
    $generator = new RandomValueGenerator();

    foreach (range(1, 100) as $_) {
        $value = $generator->generate();

        expect($value)->toBeInt()->toBeGreaterThanOrEqual(20)->toBeLessThanOrEqual(30);
    }
});
