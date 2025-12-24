<?php

require_once __DIR__ . '/vendor/autoload.php';

$reflector = new ReflectionClass('App\Repository\FoodRepository');
$interface = new ReflectionClass('App\Repository\FoodRepositoryInterface');

$missing = [];
foreach ($interface->getMethods() as $method) {
    if (!$reflector->hasMethod($method->name)) {
        $missing[] = $method->name;
    }
}

if (empty($missing)) {
    echo 'All interface methods implemented' . PHP_EOL;
} else {
    echo 'Missing methods: ' . implode(', ', $missing) . PHP_EOL;
}