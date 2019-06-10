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

namespace spec\Doyo\Behat\Coverage;

use Behat\Testwork\ServiceContainer\Extension as ExtensionInterface;
use Doyo\Behat\Coverage\Extension;
use PhpSpec\ObjectBehavior;

class ExtensionSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Extension::class);
    }

    public function it_should_be_a_behat_extension()
    {
        $this->shouldImplement(ExtensionInterface::class);
    }
}
