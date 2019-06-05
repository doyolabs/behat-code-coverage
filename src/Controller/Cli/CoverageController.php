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

/*
 * This file is part of the doyo/behat-code-coverage project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Doyo\Behat\Coverage\Controller\Cli;

use Behat\Testwork\Cli\Controller;
use Doyo\Behat\Coverage\Event\ReportEvent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Code Coverage Cli Controller.
 *
 * @author Anthonius Munthi <me@itstoni.com>
 */
class CoverageController implements Controller, EventSubscriberInterface
{
    /**
     * @var StyleInterface|null
     */
    private $style;

    /**
     * CoverageController constructor.
     *
     * @param StyleInterface|null $style
     */
    public function __construct(StyleInterface $style)
    {
        $this->style = $style;
    }

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
            ReportEvent::BEFORE_PROCESS => 'onBeforeReportProcess',
            ReportEvent::AFTER_PROCESS  => 'onAfterReportProcess',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasParameterOption(['--coverage'])) {
            $this->style->note('Running with code coverage');
        }
    }

    public function onBeforeReportProcess(ReportEvent $event)
    {
        $io = $this->style;
        $io->section('behat coverage reports process started');
        $event->setIO($io);
    }

    public function onAfterReportProcess(ReportEvent $event)
    {
        $exceptions = $event->getExceptions();
        $io = $event->getIO();
        if(0 === count($exceptions)){
            $this->style->success('behat coverage reports process completed');
            return;
        }

        $io->newLine(2);
        $io->section('behat coverage reports process failed');
        foreach($exceptions as $exception){
            $io->error($exception->getMessage());
        }
    }
}
