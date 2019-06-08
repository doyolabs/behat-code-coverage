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

namespace Doyo\Behat\Coverage\Bridge\CodeCoverage\Session;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Driver\Dummy;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use SebastianBergmann\CodeCoverage\Filter;

class RemoteSession extends Session
{
    const HEADER_SESSION_KEY   = 'HTTP_DOYO_COVERAGE_SESSION';
    const HEADER_TEST_CASE_KEY = 'HTTP_DOYO_COVERAGE_TESTCASE';

    public static function startSession()
    {
        if (!isset($_SERVER[static::HEADER_SESSION_KEY])) {
            return null;
        }

        $name    = $_SERVER[static::HEADER_SESSION_KEY];
        $session = new static($name);
        if (isset($_SERVER[static::HEADER_TEST_CASE_KEY])) {
            $name     = $_SERVER[static::HEADER_TEST_CASE_KEY];
            $testCase = new TestCase($name);
            $session->setTestCase($testCase);

            $session->start();
            register_shutdown_function([$session, 'shutdown']);
        }

        return $session;
    }

    public function init(array $config)
    {
        $filter = new Filter();
        if (isset($config['filterOptions'])) {
            $filter->setWhitelistedFiles($config['filterOptions']['whitelistedFiles']);
        }

        $processor    = new Processor(new Dummy(), $filter);
        $codeCoverage = $processor->getCodeCoverage();
        if (isset($config['codeCoverageOptions'])) {
            foreach ($config['codeCoverageOptions'] as $method => $option) {
                $method = 'set'.ucfirst($method);
                \call_user_func_array([$codeCoverage, $method], [$option]);
            }
            $processor->setCodeCoverageOptions($config['codeCoverageOptions']);
        }
        $this->setProcessor($processor);
        $this->reset();
    }
}
