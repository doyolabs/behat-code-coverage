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

namespace Doyo\Behat\Coverage\Controller\Cli;

use Behat\Testwork\Cli\Controller;
use Doyo\Behat\Coverage\Bridge\Symfony\Event;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Event\ReportEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Code Coverage Cli Controller.
 *
 * @author Anthonius Munthi <me@itstoni.com>
 */
class CoverageController implements Controller, EventSubscriberInterface
{
    /**
     * @var bool
     */
    private $coverageEnabled = false;

    /**
     * {@inheritdoc}
     */
    public function configure(Command $command)
    {
        $command->addOption('coverage', null, InputOption::VALUE_NONE, 'Collecting code coverage');
    }

    public static function getSubscribedEvents()
    {
        return [
            ReportEvent::BEFORE_PROCESS => [
                ['validateEvent', 1000],
                ['beforeReportProcess']
            ],
            ReportEvent::AFTER_PROCESS    => ['afterReportProcess', 1000],
            CoverageEvent::COMPLETED => ['validateEvent', 1000],
            CoverageEvent::BEFORE_REFRESH => ['validateEvent', 1000],
            CoverageEvent::BEFORE_START   => ['validateEvent', 1000],
            CoverageEvent::BEFORE_STOP    => ['validateEvent', 1000],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasParameterOption(['--coverage'])) {
            $this->coverageEnabled = true;
        }
    }

    public function validateEvent(Event $event)
    {
        if (!$this->coverageEnabled) {
            $event->stopPropagation();
        }
    }

    public function beforeReportProcess(ReportEvent $event)
    {
        $event->getConsoleIO()->section('generating code coverage report');
    }

    public function afterReportProcess(ReportEvent $event)
    {
        $io = $event->getConsoleIO();

        if(!$io->hasError()){
            $io->success('behat code coverage generated');
        }else{
            $io->error('behat generate code coverage error');
        }
    }
}
