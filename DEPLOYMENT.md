# Deployment Guide for Music Bot

## Prerequisites

- **Server**: Linux VPS (Ubuntu/Debian recommended) or Laravel Forge/DigitalOcean App Platform.
- **PHP**: 8.2 or higher.
- **Database**: MySQL or SQLite.
- **Tools**: Composer, Git, Supervisor, Nginx.
- **System Dependencies**: `python3` (for yt-dlp), `ffmpeg`.

## Installation

1.  **Clone the Repository**

    ```bash
    git clone https://github.com/your-repo/nilufar-bot.git
    cd nilufar-bot
    ```

2.  **Install Dependencies**

    ```bash
    composer install --optimize-autoloader --no-dev
    ```

3.  **Environment Configuration**

    ```bash
    cp .env.example .env
    nano .env
    ```

    Set the following keys:
    - `APP_URL`: Your domain (https is required for Telegram Webhooks).
    - `DB_CONNECTION`, `DB_DATABASE`, etc.
    - `TELEGRAM_BOT_TOKEN`: From BotFather.
    - `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`.
    - `YANDEX_CLIENT_ID`, `YANDEX_CLIENT_SECRET`, `YANDEX_REDIRECT_URI`.

4.  **Generate Application Key & Migrate**

    ```bash
    php artisan key:generate
    php artisan migrate --force
    ```

5.  **Install yt-dlp**
    The bot expects `yt-dlp` at `storage/bin/yt-dlp`.
    ```bash
    mkdir -p storage/bin
    curl -L https://github.com/yt-dlp/yt-dlp/releases/latest/download/yt-dlp -o storage/bin/yt-dlp
    chmod +x storage/bin/yt-dlp
    ```
    _Note: Ensure `python3` is installed on the server._

## Process Management (Supervisor)

The bot uses Laravel Queues for downloading files. You must run a queue worker.

1.  **Install Supervisor**

    ```bash
    sudo apt-get install supervisor
    ```

2.  **Configure Worker**
    Create `/etc/supervisor/conf.d/nilufar-worker.conf`:

    ```ini
    [program:nilufar-worker]
    process_name=%(program_name)s_%(process_num)02d
    command=php /path/to/nilufar-bot/artisan queue:work --sleep=3 --tries=3 --max-time=3600
    autostart=true
    autorestart=true
    user=www-data
    numprocs=2
    redirect_stderr=true
    stdout_logfile=/path/to/nilufar-bot/storage/logs/worker.log
    ```

3.  **Start Worker**
    ```bash
    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl start nilufar-worker:*
    ```

## Webhook Setup

After starting the server (Nginx/Apache), set the Telegram webhook:

```bash
curl "https://api.telegram.org/bot<YOUR_BOT_TOKEN>/setWebhook?url=https://<YOUR_DOMAIN>/api/telegram/webhook"
```

## Troubleshooting

- **Audio not sending**: Check `storage/logs/laravel.log` and `worker.log`. Frequent cause: file size > 50MB (Telegram limit) or `yt-dlp` failure.
- **yt-dlp errors**: Try running `./storage/bin/yt-dlp -U` to update it.
- **Search fails**: YouTube may block server IPs. Consider using proxies or cookies with `yt-dlp`.
