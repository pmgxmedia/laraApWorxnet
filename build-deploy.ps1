<#
    Packages the Laravel app for cPanel deployment WITHOUT SSH/Composer.

    Produces:
        deploy\laraapp.zip        -> extract into /home/USER/laraapp   (outside web root)
        deploy\public_html.zip    -> extract into /home/USER/public_html
        deploy\worxdatabase.sql   -> import via phpMyAdmin

    Run from the project root:  powershell -ExecutionPolicy Bypass -File build-deploy.ps1
#>
$ErrorActionPreference = 'Stop'

$root      = $PSScriptRoot
$deploy    = Join-Path $root 'deploy'
# Stage outside the project folder - copying 10k files inside OneDrive is extremely slow
$stageRoot = Join-Path ([System.IO.Path]::GetTempPath()) 'worxnet-deploy'
$stageApp  = Join-Path $stageRoot 'laraapp'
$stageWeb  = Join-Path $stageRoot 'public_html'
$zipApp    = Join-Path $deploy 'laraapp.zip'
$zipWeb    = Join-Path $deploy 'public_html.zip'

Write-Host '==> Cleaning staging folders'
if (Test-Path $stageRoot) { Remove-Item $stageRoot -Recurse -Force }
if (Test-Path $deploy)    { Remove-Item $deploy -Recurse -Force }
New-Item -ItemType Directory -Path $deploy, $stageApp, $stageWeb | Out-Null

Write-Host '==> Building production Vite assets (npm run build)'
$env:VITE_PUSHER_APP_KEY = ''
$env:VITE_PUSHER_HOST = ''
$env:VITE_PUSHER_PORT = ''
$env:VITE_PUSHER_SCHEME = ''
$env:VITE_PUSHER_APP_CLUSTER = 'mt1'
Push-Location $root
try {
    npm run build
    if ($LASTEXITCODE -ne 0) { throw 'npm run build failed' }
} finally {
    Pop-Location
}

Write-Host '==> Copying application (excluding dev-only files)'
$excludeDirs  = @('node_modules', '.git', 'deploy')
$excludeFiles = @('*.log', '.phpunit.result.cache', '*.zip', '*.sql', '.env',
                  'serve.bat', 'optimize.bat', 'clear-optimize.bat', 'check_users.php')
robocopy $root $stageApp /E /XD $excludeDirs /XF $excludeFiles /NFL /NDL /NJH /NJS /NP | Out-Null
if ($LASTEXITCODE -ge 8) { throw "robocopy failed with code $LASTEXITCODE" }
$global:LASTEXITCODE = 0

# public/storage is a junction on dev machines - the server gets a real folder in public_html instead
if (Test-Path (Join-Path $stageApp 'public\storage')) {
    Remove-Item (Join-Path $stageApp 'public\storage') -Recurse -Force
}

Write-Host '==> Stripping local runtime caches and logs'
$strip = @(
    (Join-Path $stageApp 'storage\logs'),
    (Join-Path $stageApp 'storage\framework\views'),
    (Join-Path $stageApp 'storage\framework\sessions'),
    (Join-Path $stageApp 'storage\framework\cache\data'),
    (Join-Path $stageApp 'bootstrap\cache')
)
foreach ($dir in $strip) {
    if (-not (Test-Path $dir)) { New-Item -ItemType Directory -Path $dir -Force | Out-Null }
    Get-ChildItem $dir -Recurse -Force -File | Where-Object { $_.Name -ne '.gitignore' } | Remove-Item -Force
}

Write-Host '==> Applying production config patches'
# 1. Public disk root can be pointed at the web-accessible storage folder (replaces artisan storage:link)
$fsConfig = Join-Path $stageApp 'config\filesystems.php'
$content  = Get-Content $fsConfig -Raw
$old = "'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),"
$new = "'public' => [
            'driver' => 'local',
            'root' => env('PUBLIC_DISK_ROOT', storage_path('app/public')),"
if (-not $content.Contains($old)) { throw 'filesystems.php patch target not found' }
Set-Content $fsConfig -Value ($content.Replace($old, $new)) -NoNewline -Encoding UTF8

# 2. Production .env
$appKey = (& php -r "echo 'base64:'.base64_encode(random_bytes(32));").Trim()
if ($appKey -notmatch '^base64:.{44}$') { throw "APP_KEY generation failed: $appKey" }

$envFile = @'
APP_NAME=WorxNet
APP_ENV=production
APP_KEY=__APP_KEY__
APP_DEBUG=false
APP_URL=https://worxnet.co.za
APP_TIMEZONE=Africa/Johannesburg

LOG_CHANNEL=stack
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=REPLACE_DB_NAME
DB_USERNAME=REPLACE_DB_USER
DB_PASSWORD=REPLACE_DB_PASSWORD

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=public
PUBLIC_DISK_ROOT=/home/REPLACE_CPANEL_USER/public_html/storage
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true

MEMCACHED_HOST=127.0.0.1
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=465
MAIL_USERNAME=REPLACE_MAIL_USER
MAIL_PASSWORD=REPLACE_MAIL_PASSWORD
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="hello@worxnet.co.za"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_HOST=
PUSHER_PORT=443
PUSHER_SCHEME=https
PUSHER_APP_CLUSTER=mt1
LARAVEL_WEBSOCKETS_PORT=6001

VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_HOST="${PUSHER_HOST}"
VITE_PUSHER_PORT="${PUSHER_PORT}"
VITE_PUSHER_SCHEME="${PUSHER_SCHEME}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
'@
$envFile = $envFile.Replace('__APP_KEY__', $appKey)
Set-Content (Join-Path $stageApp '.env') -Value $envFile -Encoding UTF8

Write-Host '==> Assembling public_html package'
# Fresh Vite build in the web root (what the browser loads) ...
Copy-Item (Join-Path $root 'public\build') (Join-Path $stageWeb 'build') -Recurse -Force
# ... and a copy inside the app folder so @vite can read the manifest (Laravel 9 public_path)
Copy-Item (Join-Path $root 'public\build') (Join-Path $stageApp 'public\build') -Recurse -Force

# Pre-patched front controller: loads the app from /home/USER/laraapp
$indexPhp = @'
<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| WorxNet cPanel split deployment
|--------------------------------------------------------------------------
| The Laravel application lives one level above the web root in /laraapp;
| only this public folder sits in public_html.
*/

if (file_exists($maintenance = __DIR__.'/../laraapp/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../laraapp/vendor/autoload.php';

$app = require_once __DIR__.'/../laraapp/bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);
'@
Set-Content (Join-Path $stageWeb 'index.php') -Value $indexPhp -Encoding UTF8
Copy-Item (Join-Path $root 'public\.htaccess') (Join-Path $stageWeb '.htaccess') -Force
Copy-Item (Join-Path $root 'public\favicon.ico') (Join-Path $stageWeb 'favicon.ico') -Force
Copy-Item (Join-Path $root 'public\robots.txt') (Join-Path $stageWeb 'robots.txt') -Force

# Real storage folder in the web root (replaces the artisan storage:link symlink).
# PUBLIC_DISK_ROOT in .env points Laravel's public disk straight at this folder.
Copy-Item (Join-Path $root 'storage\app\public') (Join-Path $stageWeb 'storage') -Recurse -Force

Write-Host '==> Verifying the staged app boots (php artisan route:list)'
Push-Location $stageApp
try {
    $routeOut = & php artisan route:list --json 2>&1
    if ($LASTEXITCODE -ne 0) { throw "artisan route:list failed:`n$routeOut" }
    Write-Host ($routeOut | Select-Object -First 12 | Out-String)
} finally {
    Pop-Location
}

# Caches recreated by the verification run are stripped again so the server rebuilds them with server paths
Get-ChildItem (Join-Path $stageApp 'bootstrap\cache') -Recurse -Force -File | Where-Object { $_.Name -ne '.gitignore' } | Remove-Item -Force
Get-ChildItem (Join-Path $stageApp 'storage\logs') -Recurse -Force -File | Where-Object { $_.Name -ne '.gitignore' } | Remove-Item -Force

Write-Host '==> Creating zips'
Add-Type -AssemblyName System.IO.Compression.FileSystem
[System.IO.Compression.ZipFile]::CreateFromDirectory($stageApp, $zipApp, [System.IO.Compression.CompressionLevel]::Optimal, $false)
[System.IO.Compression.ZipFile]::CreateFromDirectory($stageWeb, $zipWeb, [System.IO.Compression.CompressionLevel]::Optimal, $false)
Copy-Item (Join-Path $root 'worxdatabase.sql') (Join-Path $deploy 'worxdatabase.sql') -Force

$appSize = [Math]::Round((Get-Item $zipApp).Length / 1MB, 1)
$webSize = [Math]::Round((Get-Item $zipWeb).Length / 1MB, 1)
Write-Host ''
Write-Host "DONE  deploy\laraapp.zip       $appSize MB  -> extract to /home/USER/laraapp"
Write-Host "DONE  deploy\public_html.zip   $webSize MB  -> extract to public_html"
Write-Host "DONE  deploy\worxdatabase.sql          -> import via phpMyAdmin"
Write-Host ''
Write-Host "New production APP_KEY generated. Fill in the REPLACE_* values in laraapp\.env after upload."
