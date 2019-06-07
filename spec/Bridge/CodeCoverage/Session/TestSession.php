<?php


namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage\Session;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\Session;

class TestSession extends Session
{
    public function stop()
    {
        $this->data = $this->processor->stop();
    }
}
