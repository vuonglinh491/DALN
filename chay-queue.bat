@echo off
chcp 65001 >nul
echo Dang chay hang doi xu ly tai lieu (de mo cua so nay trong luc dung website). Nhan Ctrl+C de dung.
php artisan queue:work --tries=1 --timeout=600 --sleep=3
