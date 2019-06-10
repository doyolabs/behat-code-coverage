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

namespace Doyo\Behat\Coverage\Listener;

use Doyo\Behat\Coverage\Event\CoverageEvent;
use Doyo\Bridge\CodeCoverage\Exception\SessionException;
use Doyo\Bridge\CodeCoverage\Session\RemoteSession;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RemoteCoverageListener extends AbstractSessionCoverageListener implements EventSubscriberInterface
{
    /**
     * @var \Behat\Mink\Mink
     */
    private $mink;

    /**
     * @var ClientInterface
     */
    private $httpClient;

    private $remoteUrl;

    /**
     * @var bool
     */
    private $initialized = false;

    /**
     * @var SessionException
     */
    private $refreshException;

    public static function getSubscribedEvents()
    {
        return [
            CoverageEvent::BEFORE_START => 'beforeCoverageStart',
            CoverageEvent::REFRESH      => 'coverageRefresh',
            CoverageEvent::COMPLETED    => 'coverageCompleted',
        ];
    }

    public function setMink($mink)
    {
        $this->mink = $mink;
    }

    public function setRemoteUrl($url)
    {
        $this->remoteUrl = $url;
    }

    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function coverageRefresh()
    {
        $client          = $this->httpClient;
        $session         = $this->session;
        $processor       = $session->getProcessor();
        $filter          = $processor->getCodeCoverageFilter();
        $coverageOptions = $processor->getCodeCoverageOptions();
        $url             = $this->remoteUrl;

        $data = [
            'filterOptions'       => [
                'whitelistedFiles' => $filter->getWhitelistedFiles(),
            ],
            'codeCoverageOptions' => $coverageOptions,
        ];
        $body    = json_encode($data);
        $options = [
            'body'  => $body,
            'query' => [
                'action'  => 'init',
                'session' => $this->session->getName(),
            ],
        ];

        try {
            $this->initialized = false;
            $client->request('POST', $url, $options);
            $this->initialized = true;
        } catch (\Exception $e) {
            $message                = $this->getExceptionMessage($e);
            $this->initialized      = false;
            $this->refreshException = new SessionException($message);
        }
    }

    public function beforeCoverageStart(CoverageEvent $event)
    {
        if ($this->initialized) {
            $this->doBeforeCoverageStart($event);
        }
    }

    public function coverageCompleted(CoverageEvent $event)
    {
        if ($this->initialized) {
            $this->doCoverageComplete($event);
        }
    }

    private function doBeforeCoverageStart(CoverageEvent $event)
    {
        $sessionName  = $this->session->getName();
        $testCaseName = $event->getTestCase()->getName();

        $mink = $this->mink;

        /** @var \Behat\Mink\Driver\Goutte\Client $client */
        $driver = $mink->getSession()->getDriver();
        $driver->setRequestHeader(RemoteSession::HEADER_SESSION_KEY, $sessionName);
        $driver->setRequestHeader(RemoteSession::HEADER_TEST_CASE_KEY, $testCaseName);

        /* patch for browserkit driver */
        if (method_exists($driver, 'getClient')) {
            $client = $driver->getClient();
            $client->setServerParameters([
                RemoteSession::HEADER_SESSION_KEY   => $sessionName,
                RemoteSession::HEADER_TEST_CASE_KEY => $testCaseName,
            ]);
        }
    }

    private function doCoverageComplete(CoverageEvent $event)
    {
        $session = $this->session;
        $client  = $this->httpClient;
        $uri     = $this->remoteUrl;

        $options = [
            'query' => [
                'action'  => 'read',
                'session' => $session->getName(),
            ],
        ];

        try {
            $response  = $client->request('GET', $uri, $options);
            $data      = $response->getBody()->getContents();
            $session   = unserialize($data);
            $processor = $session->getProcessor();
            $event->getProcessor()->merge($processor);
        } catch (\Exception $exception) {
            $message = $this->getExceptionMessage($exception);
            $event->getConsoleIO()->sessionError($session->getName(), $message);
        }
    }

    private function getExceptionMessage(\Exception $exception): string
    {
        $message = $exception->getMessage();

        if (!$exception instanceof RequestException) {
            return $message;
        }

        $response = $exception->getResponse();
        if (!$response instanceof ResponseInterface) {
            return $message;
        }

        $contentType = $response->getHeader('Content-Type');
        if (\in_array('application/json', $contentType, true)) {
            $data    = json_decode($response->getBody()->getContents(), true);
            $message = $data['message'];
        }

        return $message;
    }
}
