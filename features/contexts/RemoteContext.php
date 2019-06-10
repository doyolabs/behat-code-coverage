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

use Behat\Gherkin\Node\PyStringNode;
use Behatch\Context\RestContext;
use Doyo\Bridge\CodeCoverage\Controller\RemoteController;
use Doyo\Bridge\CodeCoverage\Session\SessionInterface;
use Webmozart\Assert\Assert;

class RemoteContext extends RestContext
{
    public function iSendARequestTo($method, $url, PyStringNode $body = null, $files = [])
    {
        $this->iAddHeaderEqualTo('accept', 'application/json');

        return parent::iSendARequestTo($method, $url, $body, $files);
    }

    /**
     * @Given I initialize new remote session :name with:
     *
     * @param string       $name
     * @param PyStringNode $body
     */
    public function iInitializeNewRemoteSession($name, PyStringNode $body = null)
    {
        $url = '/coverage.php?action=init&session='.$name;
        $this->iSendARequestTo('POST', $url, $body);
    }

    /**
     * @Given I read coverage session :name
     *
     * @param string $name
     */
    public function iReadCoverageSession(string $name)
    {
        $url = '/coverage.php?action=read&session='.$name;
        $this->iAddHeaderEqualTo('accept', 'application/php-serialized-object');
        $this->iSendARequestTo('GET', $url);
    }

    /**
     * @Then the content should be serialized
     * @Then the content should be a :type
     *
     * @param mixed|null $type
     */
    public function theContentShouldBeASerializedObject($type = null)
    {
        $contentType = $this->request->getHttpHeader('Content-Type');
        Assert::eq($contentType, RemoteController::SERIALIZED_OBJECT_CONTENT_TYPE);

        $map = [
            'session' => SessionInterface::class,
        ];
        if (null !== $type) {
            $content = $this->getSession()->getPage()->getContent();
            $content = unserialize($content);
            Assert::isInstanceOf($content, $map[$type]);
        }
    }
}
