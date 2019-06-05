<?php


namespace Doyo\Behat\Coverage\Bridge\Symfony;

if(class_exists(' \Symfony\Contracts\EventDispatcher\Event')){
    class BaseEvent extends \Symfony\Contracts\EventDispatcher\Event
    {
        public function getVersion()
        {
            return '4.3';
        }
    }
}else{
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
