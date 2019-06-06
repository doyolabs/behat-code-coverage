<?php

namespace spec\Doyo\Behat\Coverage\Bridge;

use SebastianBergmann\CodeCoverage\Driver\Driver;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Bridge\LocalCoverage;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Event\ReportEvent;
use PhpSpec\ObjectBehavior;
use SebastianBergmann\CodeCoverage\Filter;
use Prophecy\Argument;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Webmozart\Assert\Assert;

class LocalCoverageSpec extends ObjectBehavior
{
    protected $data = [ __DIR__.'/TestClass.php' => [
        9 => 1,
        10 => 1,
        11 => 1,
        12 => 1,
        13 => 1,
    ]];

    function let(
        Driver $driver,
        Processor $coverage
    ){
        $filter = new Filter();
        $filter->addFileToWhitelist(__DIR__.'/TestClass.php');
        $coverage->beConstructedWith([$driver->getWrappedObject(), $filter]);
        $this->beConstructedWith($coverage);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(LocalCoverage::class);
    }

    function it_should_be_an_event_subscriber()
    {
        $this->shouldImplement(EventSubscriberInterface::class);
    }

    function it_should_subscribe_coverage_event()
    {
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(CoverageEvent::START, 'onCoverageStarted');
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(CoverageEvent::STOP, 'onCoverageStopped');
        $this->getSubscribedEvents()->shouldHaveKeyWithValue(CoverageEvent::REFRESH, 'onCoverageRefresh');
    }

    function it_should_handle_coverage_start_event(
        CoverageEvent $event,
        TestCase $testCase,
        Processor $coverage
    )
    {
        $event->getTestCase()->willReturn($testCase);
        $coverage->start($testCase)->shouldBeCalled();
        $this->onCoverageStarted($event);
    }

    function it_should_handle_coverage_stop_event(
        Processor $coverage,
        CoverageEvent $event,
        TestCase $testCase
    )
    {
        $data = ['somedata'];
        $name = 'some-name';
        $testCase->getName()->willReturn($name);

        $event->getCoverage()->willReturn($data);
        $event->getTestCase()->willReturn($testCase);

        $coverage->append($data, $name, Argument::cetera())->shouldBeCalled();

        $coverage->start($testCase)->shouldBeCalled();
        $coverage->stop()->shouldBeCalled();
        $coverage->addTestCase($testCase)->shouldBeCalled();

        $this->onCoverageStarted($event);
        $this->onCoverageStopped($event);
    }

    function it_should_handle_before_report_process_event(
        ReportEvent $event,
        Processor $coverage
    )
    {
        $coverage->complete()->shouldBeCalledOnce();

        $this->onBeforeReportProcess($event);
    }

    function it_should_handle_coverage_refresh_event(
        Processor $coverage
    )
    {
        $coverage->clear()->shouldBeCalled();
        $this->onCoverageRefresh();
    }
}
