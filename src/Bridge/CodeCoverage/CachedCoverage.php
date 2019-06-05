<?php

namespace Doyo\Behat\Coverage\Bridge\CodeCoverage;

use Doyo\Behat\Coverage\Event\CoverageEvent;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CachedCoverage implements \Serializable, EventSubscriberInterface
{
    const CACHE_KEY = 'subject';

    /**
     * @var null|string
     */
    private $namespace;

    /**
     * @var null|string
     */
    private $coverageId;

    /**
     * @var null|FilesystemAdapter
     */
    private $adapter;

    /**
     * @var array
     */
    private $coverage = [];

    /**
     * @var array
     */
    private $filter = [];

    /**
     * @var CodeCoverage
     */
    private $codeCoverage;

    /**
     * @var \Exception[]
     */
    private $exceptions = [];

    public function __construct($namespace)
    {
        $dir = sys_get_temp_dir().'/doyo/behat-coverage-extension';
        $adapter = new FilesystemAdapter($namespace,0, $dir);
        $this->adapter = $adapter;
        $this->namespace = $namespace;

        $this->readCache();
    }

    public function initialize()
    {
        $this->coverageId = null;
        $this->coverage = [];
        $this->exceptions = [];

        $this->save();
    }

    public function serialize()
    {
        $data = [
            $this->coverageId,
            $this->coverage
        ];
        return serialize($data);
    }

    public function unserialize($serialized)
    {
        list($this->coverageId, $this->coverage) = unserialize($serialized);
    }

    /**
     * @return string|null
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @return string|null
     */
    public function getCoverageId()
    {
        return $this->coverageId;
    }

    /**
     * @param string|null $coverageId
     * @return CachedCoverage
     */
    public function setCoverageId(string $coverageId): CachedCoverage
    {
        $this->coverageId = $coverageId;
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
     * @return CachedCoverage
     */
    public function setAdapter(FilesystemAdapter $adapter): CachedCoverage
    {
        $this->adapter = $adapter;
        return $this;
    }

    /**
     * @return array
     */
    public function getCoverage(): array
    {
        return $this->coverage;
    }

    /**
     * @param array $coverage
     * @return CachedCoverage
     */
    public function setCoverage(array $coverage): CachedCoverage
    {
        $this->coverage = $coverage;
        return $this;
    }

    public function getFilter(): array
    {
        return $this->filter;
    }

    /**
     * @param  Filter|array $filter
     *
     * @return $this
     */
    public function setFilter($filter)
    {
        if($filter instanceof Filter){
            $whitelistedFiles = $filter->getWhitelistedFiles();
            $filter = [];
            $filter['addFilesToWhitelist'] = $whitelistedFiles;
        }

        $this->filter = $filter;

        return $this;
    }

    public function save()
    {
        $adapter = $this->adapter;
        $item = $adapter->getItem(static::CACHE_KEY);
        $item->set($this);
        $adapter->save($item);
    }

    public function readCache()
    {
        $adapter = $this->adapter;
        $cached = $adapter->get(static::CACHE_KEY, function(){
            return false;
        });

        if($cached instanceof CachedCoverage){
            $this->coverageId = $cached->getCoverageId();
            $this->coverage = $cached->getCoverage();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            CoverageEvent::REFRESH => 'onCoverageRefresh',
            CoverageEvent::START => 'onCoverageStarted',
            CoverageEvent::STOP => ['onCoverageStopped', 10],
        ];
    }

    /**
     * @return Filter
     */
    public function createFilter()
    {
        $config = $this->filter;
        $filter = new Filter();
        foreach ($config as $method => $value){
            call_user_func_array([$filter, $method], [$value]);
        }
        return $filter;
    }

    public function startCoverage($driver = null)
    {
        if(is_null($this->coverageId)){
            return;
        }
        $filter = $this->createFilter();
        try{
            $coverage = new CodeCoverage($driver, $filter);
            $coverage->start($this->getCoverageId());

            $this->codeCoverage = $coverage;
        }catch (\Exception $e){
            $this->exceptions[] = sprintf(
                "Error in starting code coverage:\n%s",
                $e->getMessage()
            );
        }
        register_shutdown_function([$this,'shutdown']);
    }

    public function onCoverageRefresh()
    {
        $this->initialize();
    }

    public function onCoverageStarted(CoverageEvent $event)
    {
        $this->coverageId = $event->getCoverageId();
        $this->save();
    }

    public function onCoverageStopped(CoverageEvent $event)
    {
        $this->readCache();
        $event->updateCoverage($this->coverage);
    }

    public function shutdown()
    {
        $codeCoverage = $this->codeCoverage;

        if(is_null($codeCoverage)){
            return;
        }

        $data = $codeCoverage->stop();
        $this->coverage = $data;
        $this->save();
        $this->codeCoverage = null;
    }
}
