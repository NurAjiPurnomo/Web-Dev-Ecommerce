<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
\App\Models\Product::all()->each(function($p) {
    $p->sold = 0;
    $p->save();
});
echo "Done Resetting!";
