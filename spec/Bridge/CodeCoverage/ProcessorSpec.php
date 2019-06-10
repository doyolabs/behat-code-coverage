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

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage;

use Behat\Mink\Driver\DriverInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Driver\Dummy;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use PhpSpec\ObjectBehavior;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Driver\Driver;
use SebastianBergmann\CodeCoverage\Filter;

class ProcessorSpec extends ObjectBehavior
{
    public function let(
        Driver $driver
    ) {
        $filter = new Filter();
        $filter->addFileToWhitelist(__FILE__);
        $this->beConstructedWith($driver, $filter);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Processor::class);
    }

    public function its_code_coverage_should_be_mutable(
        Dummy $driver
    ) {
        $codeCoverage = new CodeCoverage($driver->getWrappedObject());
        $this->getCodeCoverage()->shouldHaveType(CodeCoverage::class);
        $this->setCodeCoverage($codeCoverage);
        $this->getCodeCoverage()->shouldReturn($codeCoverage);
    }

    public function its_stop_should_be_callable(
        DriverInterface $driver,
        TestCase $testCase
    ) {
        $testCase->getName()->shouldBeCalled()->willReturn('some-id');
        $driver->start(true)->shouldBeCalled();
        $driver->stop()->shouldBeCalled()->willReturn([]);
        $this->start($testCase);
        $this->stop();
    }

    public function it_should_patch_coverage_data_when_test_completed(
        TestCase $testCase
    ) {
        $testCase->getName()->willReturn('some-test');
        $testCase->getResult()->willReturn(TestCase::RESULT_PASSED);

        $this->addTestCase($testCase);
        $this->complete();

        $coverage = $this->getCodeCoverage();
        $coverage->getTests()->shouldHaveKey('some-test');
        $coverage->getTests()->shouldHaveKeyWithValue('some-test', ['status' => TestCase::RESULT_PASSED]);
    }
}
