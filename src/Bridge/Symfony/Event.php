<?php

/*
 * This file is part of the doyo/behat-coverage-extension project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doyo\Behat\Coverage\Bridge\Symfony;

if (class_exists(' \Symfony\Contracts\EventDispatcher\Event')) {
    class BaseEvent extends \Symfony\Contracts\EventDispatcher\Event
    {
        public function getVersion()
        {
            return '4.3';
        }
    }
} else {
    class BaseEvent extends \Symfony\Component\EventDispatcher\Event
    {
        public function getVersion()
        {
            return '4.3';
        }
    }
}

class Event extends BaseEvent
{
}
