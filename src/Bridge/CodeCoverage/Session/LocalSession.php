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

namespace Doyo\Behat\Coverage\Bridge\CodeCoverage\Session;

class LocalSession extends Session
{
    public static function startSession($name): bool
    {
        $self = new static($name);
        try {
            $self->start();
            register_shutdown_function([$self, 'shutdown']);

            return true;
        } catch (\Exception $exception) {
            $self->addException($exception);
            $self->save();

            return false;
        }
    }
}
