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

use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use phpDocumentor\Reflection\Types\Parent_;
use Symfony\Component\HttpFoundation\Request;

class RemoteSession extends Session
{
    const HEADER_SESSION_KEY   = 'HTTP_DOYO_COVERAGE_SESSION';
    const HEADER_TEST_CASE_KEY = 'HTTP_DOYO_COVERAGE_TESTCASE';

    public static function startSession(Request $request = null)
    {
        if (!isset($_SERVER[static::HEADER_SESSION_KEY])) {
            return;
        }

        $name    = $_SERVER[static::HEADER_SESSION_KEY];
        $session = new static($name);
        if (isset($_SERVER[static::HEADER_TEST_CASE_KEY])) {
            $name     = $_SERVER[static::HEADER_TEST_CASE_KEY];
            $testCase = new TestCase($name);
            $session->setTestCase($testCase);

            $session->start();
            register_shutdown_function([$session,'shutdown']);

            $session->xdebugPatch();
        }

        return $session;
    }

    /**
     * @codeCoverageIgnore
     */
    public function xdebugPatch()
    {
        $options = $this->filterOptions;
        $filterKey = 'whitelistedFiles';

        if(
            !extension_loaded('xdebug')
            || !function_exists('xdebug_set_filter')
            || !isset($options[$filterKey])
        ){
            return;
        }

        $dirs = [];
        foreach($options[$filterKey] as $fileName => $status){
            $dir = dirname($fileName);
            if(!in_array($dir, $dirs)){
                $dirs[] = $dir;
            }
        }

        xdebug_set_filter(
            XDEBUG_FILTER_CODE_COVERAGE,
            XDEBUG_PATH_WHITELIST,
            $dirs
        );
    }

    public function init(array $config)
    {
        if (isset($config['codeCoverageOptions'])) {
            $this->setCodeCoverageOptions($config['codeCoverageOptions']);
        }

        if (isset($config['filterOptions'])) {
            $this->setFilterOptions($config['filterOptions']);
        }

        $this->reset();
    }

    public function stop()
    {
        $processor = $this->processor;
        $aggregate = $this->data;

        $processor->stop();
        $processor->updateCoverage($aggregate);

        $this->data = $processor->getData();
    }
}
