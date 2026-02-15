# Nilufar Music Bot 🎵

A powerful Telegram bot that lets you search, download, and listen to high-quality music directly in your chat. It features a smart playback queue and integrates with your YouTube and Yandex Music accounts to provide personalized recommendations.

## ✨ Features

- **🔍 Music Search**: Search for any song or video (powered by `yt-dlp`) and receive the audio file instantly.
- **▶️ Play & Download**: Get high-quality MP3s sent directly to your Telegram chat.
- **playlist Queue System**: Build a listening queue! Add tracks from search results and play them sequentially.
- **🤖 Auto-Play / Recommendations**: When your queue finishes, the bot automatically suggests and plays tracks based on your listening history.
- **🔄 Account Sync**:
    - **Google Login**: Sync your YouTube Liked Videos history.
    - **Yandex Login**: Sync your Yandex Music history.
- **📜 History Tracking**: The bot learns from what you listen to for better future recommendations.

## 🚀 Commands

- `/start` - Initialize the bot and get a welcome message.
- `/search <query>` - Search for a song (e.g., `/search faded`).
    - **Buttons**:
        - `▶️ Play Now`: Downloads and sends the track immediately.
        - `➕ Add to Queue`: Adds the track to your playback queue.
- `/login` - Receive authentication links to connect your Google or Yandex accounts.
- `/next` - Play the next track in your queue. If the queue is empty, a recommendation will play.

## 🛠 Tech Stack

- **Framework**: Laravel 12 (PHP 8.2+)
- **Platform**: Telegram Bot API (Webhooks)
- **Core Engine**: `yt-dlp` for media extraction
- **Queue**: Laravel Queues (Database/Redis) for async downloads
- **Database**: MySQL/SQLite for user data and history

## 📦 Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for detailed installation and configuration instructions.
