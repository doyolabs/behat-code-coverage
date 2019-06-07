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

namespace Doyo\Behat\Coverage\Listener;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\SessionInterface;
use Doyo\Behat\Coverage\Bridge\Exception\CacheException;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Event\ReportEvent;
use SebastianBergmann\CodeCoverage\Filter;
use spec\Doyo\Behat\Coverage\Listener\AbstractSessionCoverageListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocalCoverageListener extends AbstractSessionCoverageListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            CoverageEvent::REFRESH      => 'onCoverageRefresh',
            CoverageEvent::START        => 'onCoverageStarted',
            CoverageEvent::STOP         => ['onCoverageStopped', 100],
            ReportEvent::BEFORE_PROCESS => 'onReportBeforeProcess',
        ];
    }

    public function onCoverageRefresh()
    {
        $this->session->reset();
    }

    public function onCoverageStarted(CoverageEvent $event)
    {
        $session = $this->session;

        $session->setTestCase($event->getTestCase());
        $session->save();
    }

    public function onCoverageStopped(CoverageEvent $event)
    {
        $session = $this->session;

        $session->refresh();
        $event->updateCoverage($session->getData());

        if ($session->hasExceptions()) {
            $message = implode("\n", $session->getExceptions());
            throw new CacheException($message);
        }
    }

    public function onReportBeforeProcess(ReportEvent $event)
    {
        $session = $this->session;
        $session->refresh();

        if (!$session->hasExceptions()) {
            return;
        }

        foreach ($session->getExceptions() as $exception) {
            $event->addException($exception);
        }
    }
}
