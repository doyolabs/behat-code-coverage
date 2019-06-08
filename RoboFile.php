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

/*
 * This file is part of the doyo/behat-code-coverage project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Lurker\Event\FilesystemEvent;
use Robo\Tasks;

/**
 * This is project's console commands configuration for Robo task runner.
 *
 * @see http://robo.li/
 */
class RoboFile extends Tasks
{
    private $coverage = false;

    private $watch = false;

    /**
     * @param array $options
     */
    public function watch($options = ['coverage' => false])
    {
        $this->watch = true;

        $paths = [
            'src',
            'tests',
            'spec',
            'features',
        ];

        $this->taskWatch()
            ->monitor(
                $paths,
                function (FilesystemEvent $event) use ($options) {
                    $resource = (string) $event->getResource();
                    if (
                        false !== strpos($resource, 'build')
                        || false !== strpos($resource, 'var')
                    ) {
                        return;
                    }
                    $this->test($options);
                },
                FilesystemEvent::ALL
            )
            ->run();
    }

    public function test()
    {
        $this->taskExec('clear')->run();

        if ($this->coverage) {
            $this->taskFilesystemStack()
                ->mkdir(__DIR__.'/build', 0775)
                ->mkdir(__DIR__.'/build/cov', 0775)
                ->run();
        }

        /** @var \Robo\Result[] $results */
        $results   = [];
        $results[] = $this->configurePhpSpec()->run();
        $results[] = $this->configurePhpUnit()->run();

        if (!$this->watch) {
            $results[] = $this->configureBehat()->run();
        }

        $hasError = false;
        foreach ($results as $result) {
            if (0 !== $result->getExitCode()) {
                $hasError = true;
            }
        }

        if ($this->coverage) {
            $this->mergeCoverage();
        }

        if (!$this->watch) {
            if ($hasError) {
                throw new \Robo\Exception\TaskException($this, 'Tests failed');
            }
        }
    }

    public function coverage()
    {
        $this->coverage = true;
        $this->test();
    }

    public function mergeCoverage()
    {
        $this
            ->taskExec('phpdbg -qrr ./vendor/bin/phpcov')
            ->arg('merge')
            ->option('clover', 'build/logs/clover.xml')
            ->option('html', 'build/html')
            ->option('text')
            ->option('ansi')
            ->arg('build/cov')
            ->run();
    }

    /**
     * @return \Robo\Task\Base\Exec|\Robo\Task\Testing\Behat
     */
    private function configureBehat()
    {
        $task = $this->taskBehat();
        $task->noInteraction()
            ->format('progress')
            ->colors();

        if ($this->coverage) {
            $task->option('coverage');
            $command = $task->getCommand();
            $task    = $this->taskExec('phpdbg -qrr '.$command);
        } else {
            $task->option('tags', '~@remote');
        }

        return $task;
    }

    /**
     * @return \Robo\Task\Base\Exec|\Robo\Task\Testing\Phpspec
     */
    private function configurePhpSpec()
    {
        $task = $this->taskPhpspec();
        $task->noCodeGeneration()
            ->noInteraction()
            ->format('dot');

        if ($this->coverage) {
            $task->option('coverage');
            $task = $this->taskExec('phpdbg -qrr '.$task->getCommand());
        }

        return $task;
    }

    /**
     * @return \Robo\Task\Base\Exec|\Robo\Task\Testing\PHPUnit
     */
    private function configurePhpUnit()
    {
        $task = $this->taskPhpUnit();

        if ($this->coverage) {
            $task = $this->taskExec('phpdbg -qrr '.$task->getCommand());
            $task->option('coverage-php', 'build/cov/01-phpunit.cov')
                ->option('coverage-html', 'build/phpunit');
        }

        return $task;
    }
}
