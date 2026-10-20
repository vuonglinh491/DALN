@echo off
chcp 65001 >nul
echo Website: http://localhost:8000
php artisan serve --host=127.0.0.1 --port=8000
