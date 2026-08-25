<?php

declare(strict_types=1);

use MqttRealtime\Infrastructure\Resilience\CircuitBreaker;
use MqttRealtime\Infrastructure\Resilience\CircuitState;

it('opens after the configured number of failures', function (): void {
    $breaker = new CircuitBreaker(2, 60);

    expect(fn () => $breaker->call(fn () => throw new RuntimeException('one')))->toThrow(RuntimeException::class);
    expect(fn () => $breaker->call(fn () => throw new RuntimeException('two')))->toThrow(RuntimeException::class);
    expect($breaker->state())->toBe(CircuitState::Open);
});

it('resets after a successful half-open probe', function (): void {
    $breaker = new CircuitBreaker(1, 1);

    expect(fn () => $breaker->call(fn () => throw new RuntimeException('boom')))->toThrow(RuntimeException::class);
    sleep(1);

    $result = $breaker->call(fn () => 'ok');

    expect($result)->toBe('ok')
        ->and($breaker->state())->toBe(CircuitState::Closed);
});
