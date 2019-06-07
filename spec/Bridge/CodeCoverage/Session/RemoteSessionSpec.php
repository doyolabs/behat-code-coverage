<?php

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage\Session;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\RemoteSession;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;

class RemoteSessionSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('spec-remote');
        $this->reset();
        $this->refresh();
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoteSession::class);
        $this->reset();
    }

    function it_should_init_coverage_session()
    {
        $config =[
            'filterOptions' => ['filter'],
            'codeCoverageOptions' => ['coverage'],
        ];

        $this->init($config);

        $this->getFilterOptions()->shouldContain('filter');
        $this->getCodeCoverageOptions()->shouldContain('coverage');
    }

    function it_should_start_new_session(Request $request)
    {
        $_SERVER[RemoteSession::HEADER_SESSION_KEY] = 'spec-remote-session-test';
        $_SERVER[RemoteSession::HEADER_TEST_CASE_KEY] = 'spec-test-case';

        $sesion = $this->startSession($request);
        $sesion->shouldHaveType(RemoteSession::class);
        $sesion->getTestCase()->getName()->shouldReturn('spec-test-case');
        $sesion->getName()->shouldReturn('spec-remote-session-test');
    }

    function its_stop_should_merge_coverage_data(
        Processor $processor
    )
    {
        $data = ['aggregate'];
        $merged = ['merged'];
        $this->setData($data);

        $processor->updateCoverage($data)->shouldBeCalled();
        $processor->getData()->shouldBeCalled()->willReturn($merged);
        $processor->stop()->shouldBeCalled();

        $this->setProcessor($processor);
        $this->stop();

        $this->getData()->shouldReturn($merged);
    }

}
