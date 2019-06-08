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

namespace Doyo\Behat\Coverage\Listener;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\RemoteSession;
use Doyo\Behat\Coverage\Event\CoverageEvent;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use spec\Doyo\Behat\Coverage\Listener\AbstractSessionCoverageListener;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

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

    private $hasInitialized = false;

    private $minkSessionName;

    private $remoteUrl;

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

    public function setMinkSessionName($name)
    {
        $this->minkSessionName = $name;
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
        $this->hasInitialized = false;
        try {
            $response = $client->request(
                'POST',
                $url,
                $options
            );
            if (Response::HTTP_ACCEPTED === $response->getStatusCode()) {
                $this->hasInitialized = true;
            }
        } catch (\Exception $e) {
            $this->hasInitialized = false;
        }
    }

    public function hasInitialized()
    {
        return $this->hasInitialized;
    }

    public function beforeCoverageStart(CoverageEvent $event)
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

    public function coverageCompleted(CoverageEvent $event)
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
            $response = $client->request('GET', $uri, $options);
            if (Response::HTTP_OK === $response->getStatusCode()) {
                $data      = $response->getBody()->getContents();
                $processor = unserialize($data);
                $event->getProcessor()->merge($processor);
            }
        } catch (\Exception $exception) {
            $event->addException($exception);
        }
    }
}
