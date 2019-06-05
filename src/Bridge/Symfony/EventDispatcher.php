<?php


namespace Doyo\Behat\Coverage\Bridge\Symfony;

use Symfony\Component\EventDispatcher\EventDispatcher as BaseEventDispatcher;

/**
 * Maintain backward compatibility with symfony version
 *
 * @package Doyo\Behat\Coverage\Bridge\Symfony
 */
class EventDispatcher extends BaseEventDispatcher
{
    private $version = '4.2';

    public function __construct()
    {
        parent::__construct();

        $r = new \ReflectionClass('\Symfony\Component\EventDispatcher\EventDispatcher');
        $params = $r->getMethod('dispatch')->getParameters();
        if('event' === $params[0]->getName()){
            $this->version = '4.3';
        }
    }

    /**
     * @param Event     $event
     * @param string    $eventName
     *
     * @return Event
     */
    public function dispatch($event, $eventName = null)
    {
        if('4.2' === $this->version){
            return parent::dispatch($eventName, $event);
        }
        return parent::dispatch($event, $eventName);
    }
}
