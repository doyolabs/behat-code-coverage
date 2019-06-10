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

namespace Test\Doyo\Behat\Coverage;

use Doyo\Behat\Coverage\Bridge\Report;
use Doyo\Behat\Coverage\Controller\Cli\CoverageController;
use Doyo\Behat\Coverage\Listener\BehatEventListener;
use Doyo\Behat\Coverage\Listener\LocalCoverageListener;
use Doyo\Behat\Coverage\Listener\RemoteCoverageListener;
use Doyo\Bridge\CodeCoverage\Driver\Dummy;
use Doyo\Bridge\CodeCoverage\Processor;
use Doyo\Bridge\CodeCoverage\Session\LocalSession;
use Doyo\Bridge\CodeCoverage\Session\RemoteSession;
use PHPUnit\Framework\TestCase;

class ExtensionTest extends TestCase
{
    use BehatApplicationTesterTrait;

    /**
     * @param string $name
     * @param string $expected
     * @dataProvider getTestDefaultsConfig
     */
    public function testDefaultsConfig($name, $expected)
    {
        $container = $this->getContainer();

        $this->assertTrue($container->hasParameter($name), 'Parameter '.$name.' not exists');
        $this->assertEquals($container->getParameter($name), $expected);
    }

    public function getTestDefaultsConfig()
    {
        return [
            ['doyo.coverage.controller.cli.class', CoverageController::class],
            ['doyo.coverage.listener.behat.class', BehatEventListener::class],
            ['doyo.coverage.driver.dummy.class', Dummy::class],
        ];
    }

    /**
     * @param string     $id
     * @param mixed|null $expectedClass
     * @dataProvider getTestServiceLoaded
     */
    public function testServiceLoaded($id, $expectedClass = null)
    {
        $container = $this->getContainer();

        $this->assertTrue($container->has($id), 'Service "'.$id.'" is not loaded');

        $service = $container->get($id);

        if (null !== $expectedClass) {
            $this->assertEquals(
                $expectedClass,
                \get_class($service)
            );
        }
    }

    /**
     * @return array
     */
    public function getTestServiceLoaded()
    {
        return [
            ['doyo.coverage.listener.behat', BehatEventListener::class],
            ['doyo.coverage.controller.cli', CoverageController::class],
            ['doyo.coverage.driver.dummy', Dummy::class],
            ['doyo.coverage.controller.cli', CoverageController::class],
            ['doyo.coverage.report.clover', Report::class],
            ['doyo.coverage.sessions.local.driver', LocalSession::class],
            ['doyo.coverage.sessions.local.processor', Processor::class],
            ['doyo.coverage.sessions.local', LocalCoverageListener::class],
            ['doyo.coverage.sessions.remote.driver', RemoteSession::class],
            ['doyo.coverage.sessions.remote.processor', Processor::class],
            ['doyo.coverage.sessions.remote', RemoteCoverageListener::class],
        ];
    }

    /**
     * @dataProvider getTestCoverageFilter
     *
     * @param mixed $expected
     * @param mixed $assertType
     */
    public function testCoverageFilterConfig($expected, $assertType = true)
    {
        $filter = $this->getContainer()->get('doyo.coverage.filter');
        $files  = $filter->getWhitelistedFiles();

        if (!$assertType) {
            $this->assertArrayNotHasKey($expected, $files, 'File should not be covered: '.$expected);
        } else {
            $this->assertArrayHasKey($expected, $files, 'File should be covered: '.$expected);
        }
    }

    public function getTestCoverageFilter()
    {
        return [
            [__DIR__.'/Fixtures/src/Foo.php'],
            [__DIR__.'/Fixtures/src/Hello.php'],
            [__DIR__.'/Fixtures/src/whitelist/test.php'],
            [__DIR__.'/Fixtures/src/test.yaml', false],
            [__DIR__.'/Fixtures/src/blacklist/blacklist.php', false],
            [__DIR__.'/Fixtures/files/file.php'],
            [__DIR__.'/Fixtures/style1/style1.php'],
            [__DIR__.'/Fixtures/src/subdir/blacklist/blacklist.php', false],
        ];
    }
}
