<?php

include __DIR__.'/../../../vendor/autoload.php';

use Symfony\Component\HttpFoundation\JsonResponse;
use Test\Doyo\Behat\Coverage\Fixtures\src\Foo;
use Test\Doyo\Behat\Coverage\Fixtures\src\Hello;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Cache;
use Test\Doyo\Behat\Coverage\Fixtures\src\blacklist\blacklist;

$cache = new Cache('test');
$cache->startCoverage();

$data = [
    'foo' => Foo::say(),
    'hello' => Hello::say(),
    'blacklist' => blacklist::say()
];
$response = new JsonResponse($data);

$response->send();
