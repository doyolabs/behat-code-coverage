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

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use Doyo\Behat\Coverage\Bridge\Exception\CacheException;
use SebastianBergmann\CodeCoverage\Filter;
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
     * @var array
     */
    protected $filterOptions = [];

    /**
     * @var Processor
     */
    protected $processor;

    /**
     * @var array
     */
    protected $codeCoverageOptions = [];

    /**
     * @var \Exception[]
     */
    protected $exceptions = [];

    /**
     * @var bool
     */
    protected $hasStarted = false;

    public function __construct($sessionName)
    {
        $dir               = sys_get_temp_dir().'/doyo/behat-coverage-extension';
        $adapter           = new FilesystemAdapter($sessionName, 0, $dir);
        $this->adapter     = $adapter;
        $this->name = $sessionName;

        $this->refresh();
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->testCase   = null;
        $this->data       = [];
        $this->processor  = null;
        $this->exceptions = [];

        $this->save();
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        $data = [
            $this->testCase,
            $this->data,
            $this->codeCoverageOptions,
            $this->filterOptions,
            $this->exceptions,
        ];

        return serialize($data);
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list(
            $this->testCase,
            $this->data,
            $this->codeCoverageOptions,
            $this->filterOptions,
            $this->exceptions
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
            $this->testCase                   = $cached->getTestCase();
            $this->data                       = $cached->getData() ?: [];
            $this->exceptions                 = $cached->getExceptions();
            $this->filterOptions              = $cached->getFilterOptions();
            $this->codeCoverageOptions        = $cached->getCodeCoverageOptions();
        }
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
     *
     * @return Cache
     */
    public function setAdapter(FilesystemAdapter $adapter): self
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return Cache
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function getCodeCoverageOptions(): array
    {
        return $this->codeCoverageOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function setCodeCoverageOptions(array $codeCoverageOptions): self
    {
        $this->codeCoverageOptions = $codeCoverageOptions;

        return $this;
    }

    /**
     * @param Processor $processor
     *
     * @return Cache
     */
    public function setProcessor(Processor $processor = null)
    {
        $this->processor = $processor;

        return $this;
    }

    public function getProcessor()
    {
        return $this->processor;
    }

    public function getFilterOptions(): array
    {
        return $this->filterOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilterOptions(array $filterOptions)
    {
        $this->filterOptions = $filterOptions;

        return $this;
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
     * {@inheritdoc}
     */
    final public function start($driver = null)
    {
        if (null === $this->testCase) {
            return;
        }
        try {
            $processor = $this->processor;
            if (null === $processor) {
                $processor = $this->createCodeCoverage($driver);
            }
            $processor->start($this->testCase);
            $this->hasStarted = true;
        } catch (\Exception $e) {
            $message = sprintf(
                "Can not start code coverage in namespace: %s :\n%s",
                $this->name,
                $e->getMessage()
            );
            $this->exceptions[] = $message;
        }
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

    /**
     * @return Filter
     */
    protected function createCodeCoverageFilter()
    {
        $config = $this->filterOptions;
        $filter = new Filter();
        foreach ($config as $method => $value) {
            $method = 'set'.ucfirst($method);
            \call_user_func_array([$filter, $method], [$value]);
        }

        return $filter;
    }

    public function shutdown()
    {
        if ($this->hasStarted) {
            try {
                $this->stop();
            } catch (\Exception $e) {
                $this->exceptions[] = new CacheException($e->getMessage());
            }
        }

        $this->processor    = null;
        $this->hasStarted   = false;
        $this->save();
    }

    /**
     * @param mixed|null $driver
     *
     * @return Processor
     */
    protected function createCodeCoverage($driver = null)
    {
        $coverage = $this->processor;
        $filter   = $this->createCodeCoverageFilter();
        $options  = $this->codeCoverageOptions;

        if (null === $coverage) {
            $coverage        = new Processor($driver, $filter);
            $this->processor = $coverage;
        }

        foreach ($options as $method => $option) {
            $method = 'set'.ucfirst($method);
            \call_user_func_array([$coverage, $method], [$option]);
        }

        return $coverage;
    }
}
