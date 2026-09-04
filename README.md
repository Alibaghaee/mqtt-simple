Full Local Setup

This version does not use an external broker. Mosquitto 2 runs inside Docker with TCP on port 1883 and keeps the username/password configuration. The Publisher and Subscriber connect to the mqtt service hostname.

Setup

cp .env.example .env
make clean
make dev
make install
make quality

Real MQTT Integration Test

make integration-test

This test actually connects to Mosquitto running inside Docker, creates a subscriber and publisher, and sends a message end-to-end.

Running the Publisher and Subscriber

In two separate terminals:

make subscriber
make publisher

The Publisher sends a timestamp and a random value between 20 and 30 every second to the test/your_name topic.

The Subscriber receives the message and publishes it to React through Soketi using the mqtt.telemetry channel and the telemetry.updated event.

Running the Frontend

make frontend

Then open http://localhost:5173.

The frontend connects to Soketi via WebSocket on localhost:6001 and updates the Gauge in realtime.

To build the frontend:

make frontend-build

Tests

make quality includes PHP-CS-Fixer, PHPStan, and unit/feature tests.

make integration-test runs a real integration test against the internal MQTT broker.

First Startup

Run:

docker compose up -d --build

On the first startup, the local Mosquitto password file is created.

On subsequent startups, the existing password file is reused and updated instead of attempting to recreate it.

Open the React Gauge at:

http://localhost:5173

To reset all local volumes and recreate the broker from scratch:

docker compose down -v --remove-orphans
docker compose up -d --build