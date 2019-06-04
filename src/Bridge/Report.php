<?php

namespace Doyo\Behat\Coverage\Bridge;

use Doyo\Behat\Coverage\Event\ReportEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Report implements EventSubscriberInterface
{
    /**
     * @var object
     */
    private $processor;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $target;

    public static function getSubscribedEvents()
    {
        return [
            ReportEvent::PROCESS => 'onReportProcess'
        ];
    }

    /**
     * @return object
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * @param object $processor
     * @return Report
     */
    public function setProcessor($processor)
    {
        $this->processor = $processor;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Report
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param string $target
     * @return Report
     */
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }

    public function onReportProcess(ReportEvent $event)
    {
        $coverage = $event->getCoverage();

        /* @todo process this error message */
        try{
            $this->processor->process($coverage, $this->target, $this->name);
        }catch (\Exception $e){

        }
    }
}
