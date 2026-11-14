<?php
use App\Kernel;
require_once dirname(__DIR__).'/vendor/autoload_runtime.php';
$kernel = new Kernel('test', true);
$kernel->boot();
