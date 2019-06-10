<?php

/*
 * This file is part of the doyo/behat-code-coverage project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

include __DIR__.'/../../../vendor/autoload.php';

use Doyo\Bridge\CodeCoverage\Session\LocalSession;
use Symfony\Component\HttpFoundation\JsonResponse;
use Test\Doyo\Behat\Coverage\Fixtures\src\blacklist\blacklist;
use Test\Doyo\Behat\Coverage\Fixtures\src\Foo;
use Test\Doyo\Behat\Coverage\Fixtures\src\Hello;

LocalSession::startSession('local');
LocalSession::startSession('index');

$data = [
    'foo'       => Foo::say(),
    'hello'     => Hello::say(),
    'blacklist' => blacklist::say(),
];
$response = new JsonResponse($data);

$response->send();
