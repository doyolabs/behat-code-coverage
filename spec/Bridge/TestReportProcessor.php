<?php

/*
 * This file is part of the doyo/behat-code-coverage project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace spec\Doyo\Behat\Coverage\Bridge;

use SebastianBergmann\CodeCoverage\CodeCoverage;

class TestReportProcessor
{
    public function process(CodeCoverage $coverage, $target, $name)
    {
    }
}
