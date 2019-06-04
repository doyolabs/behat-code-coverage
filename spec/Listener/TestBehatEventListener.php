<?php


namespace spec\Doyo\Behat\Coverage\Listener;


use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Listener\BehatEventListener;

class TestBehatEventListener extends BehatEventListener
{
    /**
     * @return CoverageEvent
     */
    public function getCoverageEvent(): CoverageEvent
    {
        return $this->coverageEvent;
    }

    /**
     * @param CoverageEvent $coverageEvent
     */
    public function setCoverageEvent(CoverageEvent $coverageEvent)
    {
        $this->coverageEvent = $coverageEvent;
    }
}
