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

namespace Doyo\Behat\Coverage\Listener;

use Doyo\Behat\Coverage\Console\ConsoleIO;
use Doyo\Bridge\CodeCoverage\Session\SessionInterface;

class AbstractSessionCoverageListener
{
    /**
     * @var SessionInterface
     */
    protected $session;

    public function __construct(
        SessionInterface $session
    ) {
        $this->session = $session;
    }

    public function renderException(ConsoleIO $consoleIO, SessionInterface $session)
    {
        if (!$session->hasExceptions()) {
            return;
        }

        foreach ($session->getExceptions() as $exception) {
            $consoleIO->sessionError($session->getName(), $exception->getMessage());
        }
    }
}
