<?php

namespace spec\Doyo\Behat\Coverage\Listener;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Mink;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\RemoteSession;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\SessionInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Listener\RemoteCoverageListener;
use GuzzleHttp\ClientInterface;
use PhpSpec\ObjectBehavior;
use SebastianBergmann\CodeCoverage\Filter;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Behat\Mink\Session as MinkSession;
use Psr\Http\Message\ResponseInterface;

class RemoteCoverageListenerSpec extends ObjectBehavior
{
    function let(
        SessionInterface $session,
        ClientInterface $client,
        ProcessorInterface $processor
    )
    {
        $filter = new Filter();
        $processor->getCodeCoverageFilter()->willReturn($filter);
        $processor->getCodeCoverageOptions()->willReturn([]);
        $session->getProcessor()->willReturn($processor);

        $session->setCodeCoverageOptions(Argument::any())->willReturn(null);
        $session->setFilterOptions(Argument::any())->willReturn(null);
        $session->save()->willReturn(null);
        $session->getName()->willReturn('some-session');
        $filter = new Filter();
        $this->beConstructedWith($session, [],$filter);
        $this->setHttpClient($client);
        $this->setRemoteUrl('http://example.org');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoteCoverageListener::class);
    }

    function it_should_listen_to_coverage_events()
    {
        $this->shouldImplement(EventSubscriberInterface::class);

        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::BEFORE_START);
        $this->getSubscribedEvents()->shouldHaveKey(CoverageEvent::COMPLETED);
    }

    function its_coverageRefresh_should_init_new_coverage_session(
        ClientInterface $client,
        SessionInterface $session,
        ResponseInterface $response,
        ProcessorInterface $processor
    )
    {
        $client->request('POST','http://example.org', Argument::any())
            ->shouldBeCalled()
            ->willReturn($response);
        $session->getName()->shouldBeCalled()->willReturn('some-session');
        $response->getStatusCode()
            ->shouldBeCalled()
            ->willReturn(202);

        $this->coverageRefresh();
        $this->hasInitialized()->shouldBe(true);
    }

    function its_hasInitialized_returns_false_when_init_failed(
        ClientInterface $client
    )
    {
        $e = new \Exception('some');
        $client->request(Argument::allOf())
            ->willThrow($e);
        $this->coverageRefresh();
        $this->hasInitialized()->shouldBe(false);
    }

    function its_beforeCoverageStart_add_coverage_session_header(
        CoverageEvent $event,
        SessionInterface $session,
        TestCase $testCase,
        Mink $mink,
        DriverInterface $driver,
        MinkSession $minkSession
    )
    {
        $mink->getSession()->shouldBeCalled()->willReturn($minkSession);
        $minkSession->getDriver()->shouldBeCalled()->willReturn($driver);
        $driver->setRequestHeader(RemoteSession::HEADER_SESSION_KEY, 'spec-remote')
            ->shouldBeCalled();
        $driver->setRequestHeader(RemoteSession::HEADER_TEST_CASE_KEY, 'test-case')
            ->shouldBeCalled();

        $this->setMink($mink);

        $session->getName()->shouldBeCalled()->willReturn('spec-remote');
        $testCase->getName()->shouldBeCalled()->willReturn('test-case');
        $event->getTestCase()->willReturn($testCase);


        $this->beforeCoverageStart($event);
    }
}
