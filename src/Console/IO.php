<?php


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
        $message = sprintf('<info><comment>sessions.%s</comment> >> %s</info>', $sessionName, $message);
        $this->writeln($message);
    }
}
