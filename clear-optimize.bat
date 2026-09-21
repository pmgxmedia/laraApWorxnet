@echo off
setlocal
cd /d "%~dp0"
echo Clearing Laravel caches...
call php artisan config:clear
call php artisan view:clear
call php artisan event:clear
call php artisan route:clear
call php artisan filament:clear-caches
echo Caches cleared.
endlocal