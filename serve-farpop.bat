@echo off
title FARPOP 1.1 - porta 8001
cd /d "%~dp0"
echo.
echo  FARPOP 1.1  ^>  http://127.0.0.1:8001/login
echo  (TELREG 2.0 costuma usar a porta 8000)
echo.
php artisan serve --host=127.0.0.1 --port=8001
pause
