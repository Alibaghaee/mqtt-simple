## اجرای کامل محلی

این نسخه از Broker خارجی استفاده نمی‌کند. Mosquitto 2 داخل Docker با TCP روی پورت 1883 اجرا می‌شود و username/password  را نگه می‌دارد. Publisher و Subscriber به hostname سرویس `mqtt` وصل می‌شوند.
### راه‌اندازی

```bash
cp .env.example .env
make clean
make dev
make install
make quality
```

### تست Integration واقعی MQTT

```bash
make integration-test
```

این تست واقعاً به Mosquitto داخل Docker وصل می‌شود، یک subscriber و publisher می‌سازد و یک پیام را end-to-end عبور می‌دهد.

### اجرای Publisher و Subscriber

در دو ترمینال جدا:

```bash
make subscriber
```

```bash
make publisher
```

Publisher هر ثانیه timestamp و یک مقدار تصادفی بین 20 و 30 را روی `test/your_name` منتشر می‌کند. Subscriber پیام را دریافت کرده و از طریق Soketi روی channel `mqtt.telemetry` و event `telemetry.updated` برای React منتشر می‌کند.

### اجرای Frontend

```bash
make frontend
```

سپس `http://localhost:5173` را باز کنید. Frontend از WebSocket روی `localhost:6001` به Soketi وصل می‌شود و Gauge را به صورت realtime تغییر می‌دهد.

برای build فرانت:

```bash
make frontend-build
```

## تست‌ها

`make quality` شامل PHP-CS-Fixer، PHPStan و تست‌های unit/feature است. `make integration-test` تست واقعی Broker داخلی را اجرا می‌کند.


### First startup

Run:

```bash
docker compose up -d --build
```

اولین راه‌اندازی، فایل رمز عبور محلی Mosquitto را ایجاد می‌کند. راه‌اندازی‌های بعدی، به جای تلاش برای ایجاد مجدد فایل موجود، آن را مجدداً استفاده و به‌روزرسانی می‌کنند.

React gauge را در آدرس `http://localhost:5173` باز کنید.

برای تنظیم مجدد تمام volume های محلی و ایجاد مجدد broker از ابتدا:

```bash
docker compose down -v --remove-orphans
docker compose up -d --build
```
