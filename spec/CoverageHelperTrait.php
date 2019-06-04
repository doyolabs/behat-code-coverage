<?php


namespace spec\Doyo\Behat\Coverage;


use Doyo\Behat\Coverage\Bridge\Compat;
use SebastianBergmann\CodeCoverage\CodeCoverage;

trait CoverageHelperTrait
{
    public function getDriverSubject($driver)
    {
        $driver = $driver->beADoubleOf(Compat::getDriverClass('Dummy'));
        return $driver;
    }

    public function getCoverageSubject($driver)
    {
        $coverage = new CodeCoverage($driver->getWrappedObject());
        return $coverage;
    }
}
