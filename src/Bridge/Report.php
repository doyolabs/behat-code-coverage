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
            ReportEvent::PROCESS => 'onReportProcess',
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
     *
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
     *
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
     *
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
        $io       = $event->getIO();
        /* @todo process this error message */
        try {
            $this->processor->process($coverage, $this->target, $this->name);
            $io->text(
                sprintf(
                    '<info>Generated <comment>%s</comment> to <comment>%s</comment></info>',
                    $this->name,
                    $this->target
                ));
        } catch (\Exception $e) {
            $io->error('Failed to generate '.$this->name.' report.');
        }
    }
}
