<?php

/*
 * This file is part of the DoyoUserBundle project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doyo\Behat\Coverage\Listener;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Testwork\EventDispatcher\Event\ExerciseCompleted;
use Doyo\Behat\Coverage\Bridge\Aggregate;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Event\RefreshEvent;
use Doyo\Behat\Coverage\Event\ReportEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BehatEventListener implements EventSubscriberInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * @var CoverageEvent
     */
    protected $coverageEvent;

    public function __construct(
        EventDispatcherInterface $dispatcher
    ) {
        $this->dispatcher = $dispatcher;
        $this->coverageEvent = new CoverageEvent();
    }

    public static function getSubscribedEvents()
    {
        return [
            ExerciseCompleted::BEFORE => 'refreshCoverage',
            ScenarioTested::BEFORE    => 'startCoverage',
            ExampleTested::BEFORE     => 'startCoverage',
            ScenarioTested::AFTER     => 'startCoverage',
            ExampleTested::AFTER      => 'stopCoverage',
            ExerciseCompleted::AFTER  => 'generateReport',
        ];
    }

    public function refreshCoverage()
    {
        $dispatcher      = $this->dispatcher;
        $event           = new RefreshEvent();
        $coverageEvent = $this->coverageEvent;

        $coverageEvent->setCoverageId(null);
        $coverageEvent->setAggregate(new Aggregate());
        $dispatcher->dispatch(CoverageEvent::REFRESH, $event);
    }

    public function startCoverage(ScenarioScope $scope)
    {
        $scenario   = $scope->getScenario();
        $id         = $scope->getFeature()->getFile().':'.$scenario->getLine();
        $dispatcher = $this->dispatcher;
        $coverageEvent = $this->coverageEvent;

        $coverageEvent->setCoverageId($id);
        $dispatcher->dispatch(CoverageEvent::START, $coverageEvent);
        $this->coverageEvent = $coverageEvent;
    }

    public function stopCoverage()
    {
        $dispatcher = $this->dispatcher;
        $event      = $this->coverageEvent;
        $dispatcher->dispatch(CoverageEvent::STOP, $event);
    }

    public function generateReport()
    {
        $dispatcher = $this->dispatcher;
        $event      = new ReportEvent();
        $dispatcher->dispatch(ReportEvent::BEFORE_PROCESS, $event);
        $dispatcher->dispatch(ReportEvent::PROCESS, $event);
        $dispatcher->dispatch(ReportEvent::AFTER_PROCESS, $event);
    }
}
