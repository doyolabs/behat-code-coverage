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

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage\Session;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Driver\Dummy;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Exception\SessionException;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\Session;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class SessionSpec extends ObjectBehavior
{
    public function let(
        ProcessorInterface $processor
    ) {
        $filter = new Filter();
        $this->beAnInstanceOf(TestSession::class);
        $this->beConstructedWith('spec-test');
        $processor->getCodeCoverageFilter()->willReturn($filter);
        $processor->getCodeCoverageOptions()->willReturn([]);
        $this->setProcessor($processor);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(Session::class);
    }

    public function it_should_be_serializable()
    {
        $this->shouldImplement(\Serializable::class);
    }

    public function its_test_case_should_be_mutable(
        TestCase $testCase
    ) {
        $this->setTestCase($testCase)->shouldReturn($this);
        $this->getTestCase()->shouldReturn($testCase);
    }

    public function its_cache_adapter_should_be_mutable(
        FilesystemAdapter $adapter
    ) {
        $this->getAdapter()->shouldHaveType(FilesystemAdapter::class);
        $this->setAdapter($adapter);
        $this->getAdapter()->shouldReturn($adapter);
    }

    public function its_name_should_be_mutable()
    {
        $this->getName()->shouldReturn('spec-test');
    }

    public function its_exceptions_should_be_mutable()
    {
        $exception = new \Exception('some-error');
        $this->hasExceptions()->shouldBe(false);
        $this->addException($exception);
        $this->hasExceptions()->shouldBe(true);
        $this->getExceptions()->shouldContain($exception);
    }

    public function its_xdebugPatch_should_be_mutable()
    {
        $this->getPatchXdebug()->shouldReturn(true);
        $this->setPatchXdebug(false);
        $this->getPatchXdebug()->shouldReturn(false);
    }

    public function it_should_create_and_reset_session(
        TestCase $testCase,
        ProcessorInterface $processor
    ) {
        $processor->clear()->shouldBeCalledOnce();
        $this->setProcessor($processor);
        $this->setTestCase($testCase);
        $this->save();
        $this->getTestCase()->shouldHaveType(TestCase::class);

        $this->reset();
        $this->getTestCase()->shouldBeNull();
    }

    public function its_start_should_not_process_with_null_testCase(
        Dummy $driver
    ) {
        $this->setTestCase(null);
        $driver->start(Argument::cetera())->shouldNotBeCalled();

        $this->start($driver->getWrappedObject());
    }

    public function its_start_should_handle_coverage_start_error(
        Dummy $driver
    ) {
        $e = new \Exception('some error');
        $driver
            ->start(Argument::cetera())
            ->shouldBeCalled()
            ->willThrow($e);
        $testCase = new TestCase('some');
        $this->setTestCase($testCase);
        $this
            ->shouldThrow(SessionException::class)
            ->duringStart($driver);
    }

    public function its_stop_should_handle_coverage_stop_error(
        Dummy $driver
    ) {
        $e        = new \Exception('some error');
        $testCase = new TestCase('test-case');

        $driver->start(Argument::cetera())->shouldBeCalled();
        $driver->stop()->willThrow($e);

        $this->setTestCase($testCase);
        $this->start($driver);
        $this
            ->shouldThrow(SessionException::class)
            ->duringStop();
    }

    public function it_should_start_and_stop_code_coverage(
        ProcessorInterface $processor,
        Dummy $driver,
        TestCase $testCase
    ) {
        $options = [
            'addUncoveredFilesFromWhitelist' => false,
        ];
        $testCase->getName()->shouldBeCalledOnce()->willReturn('some-test');
        $processor->merge(Argument::type(CodeCoverage::class))
            ->shouldBeCalled();
        $processor->getCodeCoverageOptions()
            ->willReturn($options);

        $driver->start(Argument::any())->shouldBeCalledOnce();
        $driver->stop()->shouldBeCalledOnce()->willReturn([]);

        $this->setProcessor($processor);
        $this->setTestCase($testCase);
        $this->start($driver);

        $this->stop();
        $this->hasExceptions()->shouldBe(false);
    }

    public function its_should_stop_coverage_during_shutdown(
        Dummy $driver
    ) {
        $testCase = new TestCase('test-case');

        $driver->start(Argument::cetera())->shouldBeCalledOnce();
        $driver->stop()->willReturn([])->shouldBeCalledOnce();

        $this->setTestCase($testCase);
        $this->start($driver);
        $this->shutdown();
    }

    public function it_should_handle_error_during_coverage_stop(
        Dummy $driver
    ) {
        $e = new \Exception('some error');

        $testCase = new TestCase('test-case');
        $driver->start(Argument::cetera())->shouldBeCalledOnce();
        $driver->stop()->willThrow($e)->shouldBeCalledOnce();

        $this->setTestCase($testCase);
        $this->start($driver);
        $this->shutdown();
        $this->hasExceptions()->shouldBe(true);
    }
}
