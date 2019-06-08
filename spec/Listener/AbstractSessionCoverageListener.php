<?php


namespace spec\Doyo\Behat\Coverage\Listener;


use Doyo\Behat\Coverage\Bridge\CodeCoverage\Driver\Dummy;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\SessionInterface;
use SebastianBergmann\CodeCoverage\Filter;

class AbstractSessionCoverageListener
{
    /**
     * @var SessionInterface
     */
    protected $session;

    public function __construct(
        SessionInterface $session
    )
    {
        $this->session = $session;
    }
}
