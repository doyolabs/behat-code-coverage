<?php

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\TestResult;
use Prophecy\Argument;

class TestCaseSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('some-id');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TestCase::class);
    }

    function its_name_should_be_mutable()
    {
        $this->getName()->shouldReturn('some-id');
    }

    function its_result_should_be_mutable()
    {
        $this->setResult(TestCase::RESULT_PASSED);
        $this->getResult()->shouldReturn(TestCase::RESULT_PASSED);
    }

}
