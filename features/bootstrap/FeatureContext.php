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
     * @Given I run behat with config:
     */
    public function iRun(PyStringNode $node = null)
    {
        $configFile = 'behat.yaml';

        if (!is_null($node)) {
            $configFile = $this->createConfig($node->getRaw());
        }

        $this->runBehat($configFile);
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
        Assert::file($this->fixturesDir.'/'.$file);
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

    private function runBehat($config)
    {
        $php      = (new \Symfony\Component\Process\ExecutableFinder())->find('phpdbg');
        $commands = [];
        $behat    = realpath(__DIR__.'/../../vendor/bin/behat');

        if (is_null($php)) {
            $php = (new \Symfony\Component\Process\PhpExecutableFinder())->find();
        }

        $commands[] = $php;
        if (false !== strpos($php, 'phpdbg')) {
            $commands[] = '-qrr';
        }

        $commands = array_merge($commands, [
            $behat,
            '--no-interaction',
            '--config='.$config,
        ]);

        $process = new Symfony\Component\Process\Process($commands, $this->fixturesDir);
        $process->run();

        $this->output = $process->getOutput();
    }
}
