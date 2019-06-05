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

namespace Doyo\Behat\Coverage\Remote;

use Doyo\Behat\Coverage\Bridge\Aggregate;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

/**
 * Class CoverageRepository.
 *
 * @method void      setCoverage(Aggregate $coverage)
 * @method Aggregate getCoverage()
 * @method bool      hasCoverage()
 * @method void      delCoverage()
 **
 * @method void   setFilter(Filter $filter)
 * @method bool   hasFilter()
 * @method Filter getFilter()
 **
 * @method void   setCoverageId(string $id)
 * @method bool   hasCoverageId()
 * @method string getCoverageId()
 **
 * @method void delete($id)
 * @method bool has($id)
 */
class CoverageRepository
{
    /**
     * @var FilesystemAdapter
     */
    private $cache;

    /**
     * @var CodeCoverage
     */
    private $coverage;

    public function __construct()
    {
        $dir         = sys_get_temp_dir().'/doyo/behat-coverage';
        $this->cache = new FilesystemAdapter('coverage', 0, $dir);
    }

    public function start()
    {
        $id      = $this->getCoverageId();
        $filter  = $this->getFilter();
        $factory = $this->getDriverFactory();
        $driver  = $factory->create();

        $coverage = new CodeCoverage($driver, $filter);
        $coverage->start($id);
        $this->coverage = $coverage;
        register_shutdown_function([$this, 'stop']);
    }

    public function stop()
    {
        $coverage = $this->coverage;
        $this->coverage->stop(true);

        if (!$this->hasCoverage()) {
            $this->setCoverage(new Aggregate());
        }

        $aggregat = $this->getCoverage();

        foreach ($coverage->getData() as $class => $counts) {
            $aggregat->update($class, $counts);
        }

        $this->setCoverage($aggregat);
    }

    public function set($id, $data)
    {
        $data  = serialize($data);
        $cache = $this->cache;
        $item  = $cache->getItem($id);
        $item->set($data);
        $cache->save($item);
    }

    public function get($id)
    {
        if (!$this->has($id)) {
            throw new \Exception('Cache item: '.$id.' is not set.');
        }
        $value = $this->cache->get($id, function ($item) {
            return $item;
        });

        return unserialize($value);
    }

    public function __call($name, $arguments)
    {
        $map         = ['has' => 'hasItem', 'del' => 'delete'];
        $cacheMethod = ['get', 'set', 'has', 'del'];
        $cache       = $this->cache;

        $lower = strtolower($name);
        $sub   = substr($lower, 0, 3);

        if (\in_array($sub, $cacheMethod, true) && \strlen($name) > 3) {
            $arg0 = substr($lower, 3);
            $name = substr($lower, 0, 3);
            array_unshift($arguments, $arg0);
        }

        $callable = [$this, $name];

        if (!method_exists($this, $name)) {
            $name = $map[$name] ?? $name;
            if (!method_exists($cache, $name)) {
                throw new \Exception('Method not exists: '.$name);
            }
            $callable = [$cache, $name];
        }

        return \call_user_func_array($callable, $arguments);
    }
}
