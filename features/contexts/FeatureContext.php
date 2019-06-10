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

namespace Test\Doyo\Behat\Coverage\Contexts;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Doyo\Bridge\CodeCoverage\Driver\Dummy;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\Process\Process;
use Webmozart\Assert\Assert;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    private $fixturesDir;

    private $output;

    private $exitCode;

    private $errorOutput;

    private $codeCoverage;

    public function __construct()
    {
        $this->fixturesDir = realpath(__DIR__.'/../../tests/Fixtures');
    }

    /**
     * @BeforeScenario
     */
    public function beforeScenario()
    {
        $this->output       = '';
        $this->exitCode     = null;
        $this->errorOutput  = '';
        $this->codeCoverage = null;
    }

    /**
     * @Then file :file line :start to :end should be covered
     * @Then file :file line :start should be covered
     *
     * @param string      $file
     * @param string|null $line
     * @param mixed|null  $start
     * @param mixed|null  $end
     */
    public function fileShouldBeCovered($file, $start = null, $end=null)
    {
        /** @var \SebastianBergmann\CodeCoverage\CodeCoverage $coverage */
        $data     = $this->getCoverageData();
        $realpath = realpath(__DIR__.'/../../tests/Fixtures/'.$file);

        Assert::keyExists($data, $realpath, 'File is not covered: '.$file);

        $end = $end ?: $start;
        for ($line=$start; $line <= $end; ++$line) {
            Assert::keyExists($data[$realpath], $line, 'line '.$line.' is not covered');
            Assert::notEmpty($data[$realpath][$line], 'line '.$line.' is not covered');
        }
    }

    /**
     * @Then file :file should not be covered
     *
     * @param string $file
     */
    public function fileShouldNotBeCovered($file)
    {
        $realpath = realpath(__DIR__.'/../../tests/Fixtures/'.$file);

        Assert::fileExists($realpath, 'source file not exists: '.$file);

        /** @var \SebastianBergmann\CodeCoverage\CodeCoverage $coverage */
        $data = $this->getCoverageData();

        Assert::keyNotExists($data, $realpath, sprintf(
            'file %s is covered',
            $file
        ));
    }

    /**
     * @return array
     */
    private function getCoverageData()
    {
        $coverageFile = __DIR__.'/../../tests/Fixtures/build/cov/behat.cov';
        Assert::fileExists($coverageFile, 'Code coverage is not generated');
        $patchedFile = $coverageFile.'.php';

        if (null === $this->codeCoverage) {
            $contents = file_get_contents($coverageFile);
            $pattern  = '/^\$coverage\s\=.*/im';
            $contents = preg_replace($pattern, '', $contents);

            file_put_contents($patchedFile, $contents);
        }

        $driver   = new Dummy();
        $coverage = new CodeCoverage($driver);

        include $patchedFile;

        return $coverage->getData();
    }

    /**
     * @Given I run behat with coverage
     * @Given I run behat with coverage and profile :profile
     *
     * @param mixed|null $profile
     */
    public function iRunBehatWithCoverage($profile = null)
    {
        $this->iRun('--coverage', $profile);
    }

    /**
     * @Given I run behat
     * @Given I run behat with :argument
     * @Given I run behat with :argument and config:
     * @Given I run behat with :argument and config file :configFile
     * @Given I run behat with profile :profile
     *
     * @param mixed|null        $argument
     * @param PyStringNode|null $node
     * @param string|null       $configFile
     * @param mixed|null        $profile
     */
    public function iRun($argument = null, $profile = null, PyStringNode $node = null, $configFile = null)
    {
        if (null === $configFile) {
            $configFile = 'behat.yaml';
        }

        $arguments = [];
        if (null !== $argument) {
            $arguments[] = $argument;
        }

        if (null !== $profile) {
            $arguments[] = '--profile='.$profile;
        }

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

    /**
     * @Then console error output should contain :expected
     *
     * @param string $string
     * @param mixed  $expected
     */
    public function consoleOutputErrorContain($expected)
    {
        Assert::contains($this->errorOutput, $expected);
    }

    /**
     * @Then the process exit code should be :expected
     *
     * @param int $expected
     */
    public function processExitCodeShouldBe($expected)
    {
        Assert::eq($this->exitCode, (int) $expected);
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
        $behat    = realpath(__DIR__.'/../../tests/Fixtures/bin/behat');

        if (null === $php) {
            $php = (new \Symfony\Component\Process\PhpExecutableFinder())->find();
        }

        $commands[] = $php;
        if (false !== strpos($php, 'phpdbg')) {
            $commands[] = '-qrr';
        }

        $commands[] = $behat;

        $commands = array_merge($commands, $additional, [
            '--no-interaction',
            '--config='.$config,
        ]);

        $process = new Process($commands, $this->fixturesDir);
        $process->setTimeout(0);
        $process->run();

        $this->output      = $process->getOutput();
        $this->exitCode    = $process->getExitCode();
        $this->errorOutput = $process->getErrorOutput();
    }
}
