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

namespace Doyo\Behat\Coverage\Bridge\CodeCoverage\Driver;

use SebastianBergmann\CodeCoverage\Version;

if (version_compare(Version::id(), '6.0', '<')) {
    class BaseDummy extends \Doyo\Behat\Coverage\Bridge\CodeCoverage\Driver\Dummy5
    {
    }
} else {
    class BaseBaseDummy extends \Doyo\Behat\Coverage\Bridge\CodeCoverage\Driver\Compat\BaseDummy6
    {
    }
}

/**
 * A dumb driver to prevent error.
 */
class Dummy extends BaseBaseDummy
{
}
