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

namespace Test\Doyo\Bridge\CodeCoverage\Session;

use Doyo\Behat\Coverage\Extension;
use Doyo\Bridge\CodeCoverage\Exception\SessionException;
use Doyo\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Bridge\CodeCoverage\Session\LocalSession;
use Doyo\Bridge\CodeCoverage\TestCase as CoverageTestCase;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeCoverage\Filter;

/**
 * Class LocalSessionTest.
 */
class LocalSessionTest extends TestCase
{
    protected function setUp()
    {
        if (!Extension::canCollectCodeCoverage()) {
            $this->markTestSkipped('xdebug or phpdbg not available');
        }
    }

    public function testStartSession()
    {
        $name = 'test.local';

        $filter    = new Filter();
        $processor = $this->createMock(ProcessorInterface::class);
        $processor->method('getCodeCoverageOptions')
            ->willReturn([]);
        $processor->method('getCodeCoverageFilter')
            ->willReturn($filter);
        $testCase = new CoverageTestCase('test');

        $session = new LocalSession($name, false);
        $session->setTestCase($testCase);
        $session->setProcessor($processor);
        $session->save();

        $this->assertTrue(LocalSession::startSession($name));
    }

    public function testStartSessionHandleError()
    {
        $name = 'test.local';

        $processor = $this->createMock(ProcessorInterface::class);
        $processor->method('getCodeCoverageFilter')
            ->willThrowException(new SessionException());
        $testCase = new CoverageTestCase('test');

        $session = new LocalSession($name, false);
        $session->setTestCase($testCase);
        $session->setProcessor($processor);
        $session->save();

        $this->assertFalse(LocalSession::startSession($name));

        $session->refresh();
        $this->assertTrue($session->hasExceptions());
    }
}
