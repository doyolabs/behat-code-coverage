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

namespace Doyo\Behat\Coverage\Console;

use Symfony\Component\Console\Style\SymfonyStyle;

class IO extends SymfonyStyle implements ConsoleIO
{
    private $hasError = false;

    public function setHasError(bool $flag)
    {
        $this->hasError = $flag;
    }

    public function hasError(): bool
    {
        return true === $this->hasError;
    }

    public function sessionError($sessionName, $message)
    {
        $this->hasError = true;
        $message        = sprintf('<info><comment>sessions.%s</comment> >> %s</info>', $sessionName, $message);
        $this->writeln($message);
    }
}
