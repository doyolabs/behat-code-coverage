<?php

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage\Session;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Processor;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\ProcessorInterface;
use Doyo\Behat\Coverage\Bridge\CodeCoverage\Session\RemoteSession;
use PhpSpec\Exception\Example\SkippingException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use SebastianBergmann\CodeCoverage\Filter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Process\Process;

class RemoteSessionSpec extends ObjectBehavior
{
    function let(
        ProcessorInterface $processor
    )
    {
        $this->beConstructedWith('spec-remote');

        $processor->getCodeCoverageOptions()->willReturn([]);
        $processor->clear()->willReturn(null);
        $this->setProcessor($processor);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RemoteSession::class);
    }

    function it_should_init_coverage_session()
    {
        $config =[
            'filterOptions' => [
                'whitelistedFiles' => [
                    __FILE__ => true
                ]
            ],
            'codeCoverageOptions' => [
                'addUncoveredFilesFromWhitelist' => false
            ],
        ];
        $this->init($config);
        $processor = $this->getProcessor();
        $processor->shouldHaveType(Processor::class);
        $processor->getCodeCoverageOptions()->shouldHaveKeyWithValue('addUncoveredFilesFromWhitelist', false);
        $processor->getCodeCoverageFilter()->shouldHaveType(Filter::class);
        $processor->getCodeCoverageFilter()->getWhitelistedFiles()->shouldHaveKeyWithValue(__FILE__,true);
    }

    function it_should_start_new_session(
        Request $request,
        ProcessorInterface $processor
    )
    {
        if(!extension_loaded('xdebug')){
            throw new SkippingException('xdebug not loaded');
        }
        $this->startSession()->shouldReturn(null);


        $_SERVER[RemoteSession::HEADER_SESSION_KEY] = 'spec-remote';
        $_SERVER[RemoteSession::HEADER_TEST_CASE_KEY] = 'spec-test-case';

        $filter = new Filter();
        $processor->getCodeCoverageFilter()->willReturn($filter);
        $this->setProcessor($processor);
        $this->save();

        $session = $this->startSession($request);
        $session->shouldHaveType(RemoteSession::class);
        $session->getName()->shouldReturn('spec-remote');
        $session->getTestCase()->getName()->shouldReturn('spec-test-case');
    }
}
