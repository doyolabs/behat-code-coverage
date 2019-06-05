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

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Webmozart\Assert\Assert;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private $fixturesDir;

    private $output;

    public function __construct()
    {
        $this->fixturesDir = realpath(__DIR__.'/../../tests/Fixtures');
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario()
    {
        $this->output = '';
    }

    /**
     * @Given I run behat
     * @Given I run behat with :arguments
     * @Given I run behat with :arguments and config:
     *
     * @param mixed|null $arguments
     */
    public function iRun($arguments = null, PyStringNode $node = null)
    {
        $configFile = 'behat.yaml';

        if (null !== $node) {
            $configFile = $this->createConfig($node->getRaw());
        }

        $this->runBehat($configFile, $arguments);
    }

    /**
     * @Then file :file should exist
     *
     * @param mixed $file
     */
    public function fileExist($file)
    {
        Assert::file($this->fixturesDir.'/'.$file);
    }

    /**
     * @Then directory :directory should exist
     *
     * @param mixed $file
     */
    public function directoryExist($file)
    {
        Assert::directory($this->fixturesDir.'/'.$file);
    }

    /**
     * @Then console output should contain :string
     *
     * @param string $string
     */
    public function consoleOutputContain($string)
    {
        Assert::contains($this->output, $string);
    }

    private function createConfig($yaml)
    {
    }

    /**
     * @Given I say foo
     */
    public function iSayFoo()
    {
        $this->output .= "\n".\Test\Doyo\Behat\Coverage\Fixtures\src\Foo::say();
    }

    /**
     * @Given I say hello
     */
    public function iSayHello()
    {
        $this->output .= "\n".\Test\Doyo\Behat\Coverage\Fixtures\src\Hello::say();
    }

    private function runBehat($config, $additional = [])
    {
        $php      = (new \Symfony\Component\Process\ExecutableFinder())->find('phpdbg');
        $commands = [];
        $behat    = realpath(__DIR__.'/../../vendor/bin/behat');

        if (null === $php) {
            $php = (new \Symfony\Component\Process\PhpExecutableFinder())->find();
        }

        $commands[] = $php;
        if (false !== strpos($php, 'phpdbg')) {
            $commands[] = '-qrr';
        }

        $commands = array_merge($commands, [
            $behat,
            $additional,
            '--no-interaction',
            '--config='.$config,
        ]);

        $process = new Symfony\Component\Process\Process($commands, $this->fixturesDir);
        $process->run();

        $this->output = $process->getOutput();
    }
}
