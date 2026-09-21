@echo off
setlocal
cd /d "%~dp0"
echo Building Laravel compile-time caches (this takes a couple of minutes on this drive, please wait)...
call php artisan view:cache
call php artisan event:cache
call php artisan filament:cache-components
echo.
echo Caches built. Restarting the dev server to load them...
taskkill /F /IM php.exe >nul 2>&1
timeout /t 1 /nobreak >nul
start "WorxNet Dev Server" cmd /k "php artisan serve"
echo.
echo Done.
echo - The FIRST time you open each admin item it may take up to ~60s (warming OPcache).
echo   Do not refresh - just wait. Every later visit to that section is fast.
echo - If any page ever shows a 500 error, run clear-optimize.bat then reload.
echo - After editing PHP/Blade/Livewire code, run clear-optimize.bat (or restart serve.bat).
endlocal