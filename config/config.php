<?php

declare(strict_types=1);

use MqttRealtime\Support\Env;

return [
    'mqtt' => [
        'host' => Env::string('MQTT_HOST'),
        'port' => Env::int('MQTT_PORT', 1883),
        'username' => Env::string('MQTT_USERNAME', ''),
        'password' => Env::string('MQTT_PASSWORD', ''),
        'topic' => Env::string('MQTT_TOPIC', 'test/your_name'),
        'qos' => Env::int('MQTT_QOS', 1),
        'connect_timeout' => Env::int('MQTT_CONNECT_TIMEOUT', 5),
        'socket_timeout' => Env::int('MQTT_SOCKET_TIMEOUT', 5),
        'keep_alive' => Env::int('MQTT_KEEP_ALIVE', 30),
    ],
    'realtime' => [
        'app_id' => Env::string('PUSHER_APP_ID'),
        'key' => Env::string('PUSHER_APP_KEY'),
        'secret' => Env::string('PUSHER_APP_SECRET'),
        'host' => Env::string('PUSHER_HOST', 'soketi'),
        'port' => Env::int('PUSHER_PORT', 6001),
        'scheme' => Env::string('PUSHER_SCHEME', 'http'),
        'channel' => Env::string('PUSHER_CHANNEL', 'mqtt.telemetry'),
        'event' => Env::string('PUSHER_EVENT', 'telemetry.updated'),
    ],
    'redis' => [
        'url' => Env::string('REDIS_URL', 'redis://redis:6379'),
        'idempotency_ttl' => Env::int('IDEMPOTENCY_TTL', 3600),
    ],
    'circuit_breaker' => [
        'failure_threshold' => Env::int('CIRCUIT_BREAKER_FAILURE_THRESHOLD', 5),
        'recovery_timeout' => Env::int('CIRCUIT_BREAKER_RECOVERY_TIMEOUT', 10),
    ],
    'publisher' => [
        'interval' => Env::int('PUBLISH_INTERVAL_SECONDS', 1),
    ],
    'logging' => [
        'level' => Env::string('LOG_LEVEL', 'info'),
    ],
];
