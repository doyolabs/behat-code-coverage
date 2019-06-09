<?php


namespace Doyo\Behat\Coverage\Console;

use Symfony\Component\Console\Style\StyleInterface as BaseInterface;

interface ConsoleIO extends BaseInterface
{
    public function sessionError($sessionName, $message);

    public function hasError(): bool;
    
    public function setHasError(bool $flag);
}
