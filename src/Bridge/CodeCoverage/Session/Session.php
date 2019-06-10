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

namespace Doyo\Behat\Coverage\Bridge\CodeCoverage\Session;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Exception\SessionException;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

abstract class Session implements \Serializable, SessionInterface
{
    const CACHE_KEY = 'session';

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var TestCase
     */
    protected $testCase;

    /**
     * @var FilesystemAdapter|null
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var ProcessorInterface
     */
    protected $processor;

    /**
     * @var \Exception[]
     */
    protected $exceptions = [];

    /**
     * @var bool
     */
    protected $hasStarted = false;

    /**
     * Code coverage for this session.
     *
     * @var CodeCoverage
     */
    protected $codeCoverage;

    protected $patchXdebug = true;

    public function __construct($name, $patchXdebug = true)
    {
        $dir               = sys_get_temp_dir().'/doyo/behat-coverage-extension';
        $adapter           = new FilesystemAdapter($name, 0, $dir);
        $this->adapter     = $adapter;
        $this->name        = $name;
        $this->patchXdebug = $patchXdebug;
        $this->refresh();
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->testCase   = null;
        $this->exceptions = [];
        $this->processor->clear();

        $this->save();
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $data = [
            $this->name,
            $this->testCase,
            $this->exceptions,
            $this->processor,
            $this->patchXdebug,
        ];

        return serialize($data);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->name,
            $this->testCase,
            $this->exceptions,
            $this->processor,
            $this->patchXdebug
        ) = unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function refresh()
    {
        $adapter = $this->adapter;
        $cached  = $adapter->getItem(static::CACHE_KEY)->get();

        if ($cached instanceof self) {
            $this->name        = $cached->getName();
            $this->testCase    = $cached->getTestCase();
            $this->exceptions  = $cached->getExceptions();
            $this->processor   = $cached->getProcessor();
            $this->patchXdebug = $cached->getPatchXdebug();
        }
    }

    public function setPatchXdebug(bool $flag)
    {
        $this->patchXdebug = $flag;
    }

    /**
     * @return bool
     */
    public function getPatchXdebug(): bool
    {
        return $this->patchXdebug;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return TestCase
     */
    public function getTestCase()
    {
        return $this->testCase;
    }

    /**
     * {@inheritdoc}
     */
    public function setTestCase(TestCase $testCase = null)
    {
        $this->testCase = $testCase;

        return $this;
    }

    /**
     * @return FilesystemAdapter|null
     */
    public function getAdapter(): FilesystemAdapter
    {
        return $this->adapter;
    }

    /**
     * @param FilesystemAdapter|null $adapter
     */
    public function setAdapter(FilesystemAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @param ProcessorInterface $processor
     */
    public function setProcessor(ProcessorInterface $processor = null)
    {
        $this->processor = $processor;
    }

    /**
     * @return ProcessorInterface|null
     */
    public function getProcessor()
    {
        return $this->processor;
    }

    /**
     * {@inheritdoc}
     */
    public function hasExceptions()
    {
        return \count($this->exceptions) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getExceptions()
    {
        return $this->exceptions;
    }

    /**
     * @param \Exception $e
     */
    public function addException(\Exception $e)
    {
        $id                    = md5($e->getMessage());
        $this->exceptions[$id] = $e;
    }

    /**
     * @codeCoverageIgnore
     */
    public function xdebugPatch()
    {
        if (!$this->patchXdebug) {
            return;
        }

        $filter    = $this->getProcessor()->getCodeCoverageFilter();
        $options   = $filter->getWhitelistedFiles();
        $filterKey = 'whitelistedFiles';

        if (
            !\extension_loaded('xdebug')
            || !\function_exists('xdebug_set_filter')
            || !isset($options[$filterKey])
        ) {
            return;
        }

        $dirs = [];
        foreach ($options[$filterKey] as $fileName => $status) {
            $dir = \dirname($fileName);
            if (!\in_array($dir, $dirs, true)) {
                $dirs[] = $dir;
            }
        }

        xdebug_set_filter(
            XDEBUG_FILTER_CODE_COVERAGE,
            XDEBUG_PATH_WHITELIST,
            $dirs
        );
    }

    /**
     * {@inheritdoc}
     */
    final public function start($driver = null)
    {
        if (null === $this->testCase) {
            return;
        }
        try {
            $this->hasStarted   = false;
            $this->codeCoverage = $this->createCodeCoverage($driver);
            $this->xdebugPatch();
            $this->codeCoverage->start($this->testCase->getName());
            $this->hasStarted = true;
        } catch (\Exception $e) {
            $message = sprintf(
                "Can not start code coverage with error message:\n%s",
                $e->getMessage()
            );
            $exception = new SessionException($message);
            throw $exception;
        }
    }

    public function stop()
    {
        try {
            $codeCoverage = $this->codeCoverage;
            $processor    = $this->processor;

            $codeCoverage->stop();

            $processor->merge($codeCoverage);
        } catch (\Exception $e) {
            throw new SessionException(
                sprintf(
                    'Failed to stop coverage for session %s: %s',
                    $this->name,
                    $e->getMessage()
                )
            );
        }
        $this->hasStarted = false;
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $adapter = $this->adapter;
        $item    = $adapter->getItem(static::CACHE_KEY);

        $item->set($this);
        $adapter->save($item);
    }

    public function shutdown()
    {
        if ($this->hasStarted && null !== $this->processor) {
            try {
                $this->stop();
            } catch (\Exception $e) {
                $this->addException(new SessionException($e->getMessage()));
            }
        }
        $this->hasStarted   = false;
        $this->save();
    }

    /**
     * @param mixed|null $driver
     *
     * @return CodeCoverage
     */
    protected function createCodeCoverage($driver): CodeCoverage
    {
        $filter   = $this->processor->getCodeCoverageFilter();
        $options  = $this->processor->getCodeCoverageOptions();
        $coverage = new CodeCoverage($driver, $filter);
        foreach ($options as $method => $option) {
            if (method_exists($coverage, $method)) {
                $method = 'set'.ucfirst($method);
                \call_user_func_array([$coverage, $method], [$option]);
            }
        }

        return $coverage;
    }
}
