<?php

declare(strict_types=1);

namespace MqttRealtime\Infrastructure\Realtime;

use Pusher\Pusher;

final class SoketiBroadcaster implements Broadcaster
{
    private Pusher $pusher;

    public function __construct(
        string $appId,
        string $key,
        string $secret,
        string $host,
        int $port,
        string $scheme,
    ) {
        $this->pusher = new Pusher(
            $key,
            $secret,
            $appId,
            [
                'host' => $host,
                'port' => $port,
                'scheme' => $scheme,
                'useTLS' => $scheme === 'https',
                'cluster' => 'mt1',
            ],
        );
    }

    public function broadcast(string $channel, string $event, array $payload): void
    {
        $this->pusher->trigger($channel, $event, $payload);
    }
}
