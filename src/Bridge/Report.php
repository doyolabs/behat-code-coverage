<?php

/*
 * This file is part of the doyo/behat-code-coverage project.
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
    private $reportProcessor;

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
    public function getReportProcessor()
    {
        return $this->reportProcessor;
    }

    /**
     * @param object $reportProcessor
     *
     * @return Report
     */
    public function setReportProcessor($reportProcessor)
    {
        $this->reportProcessor = $reportProcessor;

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
        $coverage = $event->getProcessor()->getCodeCoverage();
        $io       = $event->getConsoleIO();

        try {
            $this->reportProcessor->process($coverage, $this->target, $this->name);
            $io->text(
                sprintf(
                    '<info><comment>%s</comment> processed to: <comment>%s</comment></info>',
                    $this->name,
                    $this->target
                ));
        } catch (\Exception $e) {
            $message = sprintf(
                "failed to generate %s report. with Processor message:\n%s",
                $this->name,
                $e->getMessage()
            );
            $io->error($message);
        }
    }
}
