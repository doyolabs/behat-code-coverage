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

namespace Test\Doyo\Behat\Coverage;

use Doyo\Behat\Coverage\Bridge\CachedCoverage;
use Doyo\Behat\Coverage\Bridge\Compat;
use Doyo\Behat\Coverage\Bridge\LocalCoverage;
use Doyo\Behat\Coverage\Bridge\Report;
use Doyo\Behat\Coverage\Controller\Cli\CoverageController;
use Doyo\Behat\Coverage\Listener\BehatEventListener;
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
            [
                'doyo.coverage.options',
                Compat::getCoverageValidConfigs(),
            ],
            ['doyo.coverage.driver.dummy.class', Compat::getDriverClass('Dummy')],
            ['doyo.coverage.local.class', LocalCoverage::class],
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
            ['doyo.coverage.driver.dummy', Compat::getDriverClass('Dummy')],
            ['doyo.coverage.local', LocalCoverage::class],
            ['doyo.coverage.controller.cli', CoverageController::class],
            ['doyo.coverage.report.clover', Report::class],
            ['doyo.coverage.cached.test', CachedCoverage::class],
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
