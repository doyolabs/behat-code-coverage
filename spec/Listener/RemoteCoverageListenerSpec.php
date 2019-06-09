<?php

namespace spec\Doyo\Behat\Coverage\Listener;

use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Driver\GoutteDriver;
use Behat\Mink\Mink;
use Behatch\HttpCall\Request\BrowserKit;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\RemoteSession;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\SessionInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Console\ConsoleIO;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Behat\Coverage\Listener\RemoteCoverageListener;
use Goutte\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\StreamInterface;
use SebastianBergmann\CodeCoverage\Filter;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Behat\Mink\Session as MinkSession;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpFoundation\Response;

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

    private function decorateCoverageRefresh(
        ClientInterface $client,
        SessionInterface $session,
        ResponseInterface $response
    )
    {
        $client->request('POST','http://example.org', Argument::any())
            ->shouldBeCalled()
            ->willReturn($response);
        $session->getName()->shouldBeCalled()->willReturn('spec-remote');
    }

    function its_coverageRefresh_should_init_new_coverage_session(
        ClientInterface $client,
        SessionInterface $session,
        ResponseInterface $response
    )
    {
        $this->decorateCoverageRefresh($client,$session, $response);
        $this->coverageRefresh();
    }

    function its_coverageRefresh_should_handle_guzzle_exception_error(
        ClientInterface $client,
        ResponseInterface $response,
        StreamInterface $body,
        RequestException $exception
    )
    {

        $this->decorateRequestException($exception, $response, $body);
        $client
            ->request(Argument::cetera())
            ->willThrow($exception->getWrappedObject());
        ;
        $this->coverageRefresh();
    }

    function its_beforeCoverageStart_add_coverage_session_header(
        CoverageEvent $event,
        SessionInterface $session,
        TestCase $testCase,
        Mink $mink,
        GoutteDriver $driver,
        MinkSession $minkSession,
        ClientInterface $guzzleClient,
        Client $goutteClient,
        ResponseInterface $response
    )
    {
        $mink->getSession()->shouldBeCalled()->willReturn($minkSession);
        $minkSession->getDriver()->shouldBeCalled()->willReturn($driver);
        $driver->setRequestHeader(RemoteSession::HEADER_SESSION_KEY, 'spec-remote')
            ->shouldBeCalled();
        $driver->setRequestHeader(RemoteSession::HEADER_TEST_CASE_KEY, 'test-case')
            ->shouldBeCalled();
        $driver->getClient()->willReturn($goutteClient);
        $goutteClient->setServerParameters(Argument::any())->shouldBeCalledOnce();

        $this->setHttpClient($guzzleClient);
        $this->setMink($mink);

        $testCase->getName()->shouldBeCalled()->willReturn('test-case');
        $event->getTestCase()->willReturn($testCase);

        $this->decorateCoverageRefresh($guzzleClient,$session, $response);
        $this->coverageRefresh();
        $this->beforeCoverageStart($event);
    }

    private function decorateRequestException(
        RequestException $requestException,
        ResponseInterface $response,
        StreamInterface $body
    )
    {
        $data = <<<JSON
{
    "message": "some error"
}
JSON;

        $requestException->getResponse()
            ->shouldBeCalled()
            ->willReturn($response);
        $response->getHeader('Content-Type')
            ->shouldBeCalled()
            ->willReturn(['application/json']);
        $response->getBody()
            ->shouldBeCalled()
            ->willReturn($body);
        $body->getContents()
            ->shouldBeCalled()
            ->willReturn($data);
    }

    function its_coverageCompleted_should_handle_guzzle_exception(
        ClientInterface $client,
        ResponseInterface $response,
        RequestException $exception,
        SessionInterface $session,
        StreamInterface $body,
        CoverageEvent $event,
        ConsoleIO $consoleIO
    )
    {
        $event->getConsoleIO()->willReturn($consoleIO);

        $client->request(Argument::cetera())
            ->shouldBeCalled()
            ->willThrow($exception->getWrappedObject());
        $this->decorateCoverageRefresh($client, $session, $response);
        $this->coverageRefresh();

        $consoleIO->sessionError('spec-remote', 'some error')
            ->shouldBeCalledOnce();


        $this->decorateRequestException($exception, $response, $body);
        $this->coverageCompleted($event);
    }

    function its_coverageCompleted_should_handle_raw_exception(
        ClientInterface $client,
        ResponseInterface $response,
        RequestException $exception,
        SessionInterface $session,
        StreamInterface $body,
        CoverageEvent $event,
        ConsoleIO $consoleIO
    )
    {
        $e = new \Exception('some error');
        $client->request('GET', Argument::cetera())
            ->willThrow($e);
        $event->getConsoleIO()->willReturn($consoleIO);
        $consoleIO
            ->sessionError('spec-remote','some error')
            ->shouldBeCalledOnce();

        $this->decorateCoverageRefresh($client, $session, $response);
        $this->coverageRefresh();
        $this->coverageCompleted($event);
    }

    function it_should_handle_coverage_completed_event(
        ClientInterface $client,
        SessionInterface $session,
        ResponseInterface $response,
        CoverageEvent $event,
        StreamInterface $body,
        ProcessorInterface $sessionProcessor,
        ProcessorInterface $processor,
        ConsoleIO $consoleIO
    )
    {
        $filter = new Filter();
        $sessionProcessor->getCodeCoverageFilter()->willReturn($filter);
        $sessionProcessor->getCodeCoverageOptions()->willReturn([]);
        $session->getProcessor()
            ->willReturn($sessionProcessor->getWrappedObject());
        $data = serialize($session);

        $client->request('GET', Argument::cetera())
            ->shouldBeCalledOnce()
            ->willReturn($response);
        $response->getBody()
            ->shouldBeCalledOnce()
            ->willReturn($body);
        $body->getContents()
            ->shouldBeCalledOnce()
            ->willReturn($data);

        $event->getProcessor()->willReturn($processor);
        $processor
            ->merge(Argument::any())
            ->shouldBeCalledOnce();

        $this->decorateCoverageRefresh($client, $session, $response);
        $this->coverageRefresh();
        $this->coverageCompleted($event);
    }
}
