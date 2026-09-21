@echo off
setlocal
cd /d "%~dp0"
echo.
echo  Stopping any running PHP dev server...
taskkill /F /IM php.exe >nul 2>&1
timeout /t 1 /nobreak >nul
echo  Starting WorxNet dev server at http://127.0.0.1:8000
echo.
start "WorxNet Dev Server" cmd /k "php artisan serve"
echo  Done. The new window may take ~30-40s on first load to warm the cache.
echo.
endlocal