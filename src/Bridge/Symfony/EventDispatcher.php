<?php


namespace Doyo\Behat\Coverage\Bridge\Symfony;

use Symfony\Component\EventDispatcher\EventDispatcher as SymfonyEventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Maintain backward compatibility with symfony version
 *
 * @package Doyo\Behat\Coverage\Bridge\Symfony
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
        $r = new \ReflectionObject($dispatcher);
        $params = $r->getMethod('dispatch')->getParameters();

        if('event' === $params[0]->getName()){
            $this->version = '4.3';
        }

        $this->dispatcher = $dispatcher;
    }

    public function addSubscriber(EventSubscriberInterface $subscriber)
    {
        $this->dispatcher->addSubscriber($subscriber);
    }

    /**
     * @param Event     $event
     * @param string    $eventName
     *
     * @return \Symfony\Component\EventDispatcher\Event|\Symfony\Contract\EventDispatcher\Event
     */
    public function dispatch($event, $eventName = null)
    {
        $dispatcher = $this->dispatcher;
        if('4.2' === $this->version){
            return $dispatcher->dispatch($eventName, $event);
        }
        return $dispatcher->dispatch($event, $eventName);
    }
}
    
