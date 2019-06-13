<?php

/*
 * This file is part of the doyo/code-coverage project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Spec\Doyo\Behat\CodeCoverage;

use Doyo\Behat\CodeCoverage\Extension;
use PhpSpec\ObjectBehavior;

class ExtensionSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(Extension::class);
    }

    public function it_should_be_a_behat_extension()
    {
        $this->shouldImplement(\Behat\Testwork\ServiceContainer\Extension::class);
    }
}
