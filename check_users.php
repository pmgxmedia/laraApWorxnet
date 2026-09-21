<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::latest()->take(5)->get(['id','name','email','role','provider_company_id']);
foreach ($users as $u) {
    echo $u->id.' | name=['.$u->name.'] | role='.$u->role.' | company='.$u->provider_company_id.PHP_EOL;
}
