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

namespace spec\Doyo\Behat\Coverage\Bridge\CodeCoverage;

use Doyo\Behat\Coverage\Bridge\CodeCoverage\TestCase;
use PhpSpec\ObjectBehavior;

class TestCaseSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('some-id');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(TestCase::class);
    }

    public function its_name_should_be_mutable()
    {
        $this->getName()->shouldReturn('some-id');
    }

    public function its_result_should_be_mutable()
    {
        $this->setResult(TestCase::RESULT_PASSED);
        $this->getResult()->shouldReturn(TestCase::RESULT_PASSED);
    }
}
