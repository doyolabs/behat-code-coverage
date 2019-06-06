<?php

require __DIR__.'/../../../vendor/autoload.php';

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Controller\RemoteController;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Cache;

$cache = new Cache('remote');
$cache->startCoverage();
RemoteController::create()->getResponse()->send();
