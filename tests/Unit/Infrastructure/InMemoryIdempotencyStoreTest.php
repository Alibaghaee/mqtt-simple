<?php

declare(strict_types=1);

use MqttRealtime\Infrastructure\Persistence\InMemoryIdempotencyStore;

it('tracks processed event ids', function (): void {
    $store = new InMemoryIdempotencyStore();

    expect($store->has('event-1'))->toBeFalse();

    $store->markProcessed('event-1', 60);

    expect($store->has('event-1'))->toBeTrue();
});
