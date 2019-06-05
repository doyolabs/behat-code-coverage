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

use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Maintain backward compatibility with symfony version.
 */
class EventDispatcher
{
    private $version = '4.2';

    /**
     * @var SymfonyEventDispatcher
     */
    private $dispatcher;

    public function __construct()
    {
        $dispatcher = new SymfonyEventDispatcher();
        $r          = new \ReflectionObject($dispatcher);
        $params     = $r->getMethod('dispatch')->getParameters();

        if ('event' === $params[0]->getName()) {
            $this->version = '4.3';
        }

        $this->dispatcher = $dispatcher;
    }

    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->dispatcher->addSubscriber($subscriber);
    }

    /**
     * @param Event  $event
     * @param string $eventName
     *
     * @return \Symfony\Component\EventDispatcher\Event|\Symfony\Contract\EventDispatcher\Event
     */
    public function dispatch($event, $eventName = null)
    {
        $dispatcher = $this->dispatcher;
        if ('4.2' === $this->version) {
            return $dispatcher->dispatch($eventName, $event);
        }

        return $dispatcher->dispatch($event, $eventName);
    }
}
