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

use Doyo\Behat\Coverage\Event\CoverageEvent;
use spec\Doyo\Behat\Coverage\Listener\AbstractSessionCoverageListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class LocalCoverageListener extends AbstractSessionCoverageListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            CoverageEvent::REFRESH      => 'coverageRefresh',
            CoverageEvent::START        => 'coverageStarted',
            CoverageEvent::COMPLETED    => 'coverageCompleted',
        ];
    }

    public function coverageRefresh()
    {
        $this->session->reset();
    }

    public function coverageStarted(CoverageEvent $event)
    {
        $session = $this->session;
        $session->refresh();
        $session->setTestCase($event->getTestCase());
        $session->save();
    }

    public function coverageCompleted(CoverageEvent $event)
    {
        $session = $this->session;
        $session->refresh();

        $processor = $this->session->getProcessor();
        if (null !== $processor) {
            $event->getProcessor()->merge($processor);
        }

        if($session->hasExceptions()){
            foreach($session->getExceptions() as $exception){
                $event->getConsoleIO()->sessionError($session->getName(),$exception->getMessage());
            }
        }
    }
}
