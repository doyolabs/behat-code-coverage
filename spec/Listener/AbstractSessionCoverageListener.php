<?php


namespace spec\Doyo\Behat\Coverage\Listener;


use Doyo\Behat\Coverage\Bridge\CodeCoverage\Driver\Dummy;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\SessionInterface;
use Doyo\Behat\Coverage\Console\ConsoleIO;
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

    public function renderException(ConsoleIO $consoleIO, SessionInterface $session)
    {
        if(!$session->hasExceptions()){
            return;
        }

        foreach($session->getExceptions() as $exception){
            $consoleIO->sessionError($session->getName(),$exception->getMessage());
        }
    }
}
