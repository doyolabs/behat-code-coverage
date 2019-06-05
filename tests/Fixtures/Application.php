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

/*
 * This file is part of the doyo/behat-code-coverage project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Test\Doyo\Behat\Coverage\Fixtures;

use Behat\Testwork\Cli\DebugCommand;
use Behat\Testwork\Cli\DumpReferenceCommand;
use Behat\Testwork\ServiceContainer\Configuration\ConfigurationLoader;
use Behat\Testwork\ServiceContainer\ContainerLoader;
use Behat\Testwork\ServiceContainer\Exception\ConfigurationLoadingException;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;

class Application extends BaseApplication
{
    /**
     * @var ConfigurationLoader
     */
    private $configurationLoader;

    /**
     * @var ExtensionManager
     */
    private $extensionManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * Initializes application.
     *
     * @param string $name
     * @param string $version
     */
    public function __construct($name, $version, ConfigurationLoader $configLoader, ExtensionManager $extensionManager)
    {
        putenv('COLUMNS=9999');

        $this->configurationLoader = $configLoader;
        $this->extensionManager    = $extensionManager;

        parent::__construct($name, $version);

        $this->input  = new ArrayInput([], $this->getDefaultInputDefinition());
        $this->output = new StreamOutput(fopen('php://memory', 'w', false));
        $this->setAutoExit(false);
    }

    /**
     * Gets the default input definition.
     *
     * @return InputDefinition An InputDefinition instance
     */
    public function getDefaultInputDefinition()
    {
        return new InputDefinition([
            new InputOption('--profile', '-p', InputOption::VALUE_REQUIRED, 'Specify config profile to use.'),
            new InputOption('--config', '-c', InputOption::VALUE_REQUIRED, 'Specify config file to use.'),
            new InputOption(
                '--verbose', '-v', InputOption::VALUE_OPTIONAL,
                'Increase verbosity of exceptions.'.PHP_EOL.
                'Use -vv or --verbose=2 to display backtraces in addition to exceptions.'
            ),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message.'),
            new InputOption('--config-reference', null, InputOption::VALUE_NONE, 'Display the configuration reference.'),
            new InputOption('--debug', null, InputOption::VALUE_NONE, 'Provide debugging information about current environment.'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display version.'),
            new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question.'),
            new InputOption(
                '--colors', null, InputOption::VALUE_NONE,
                'Force ANSI color in the output. By default color support is'.PHP_EOL.
                'guessed based on your platform and the output if not specified.'
            ),
            new InputOption('--no-colors', null, InputOption::VALUE_NONE, 'Force no ANSI color in the output.'),
        ]);
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        // xdebug's default nesting level of 100 is not enough
        if (\extension_loaded('xdebug')
            && false === strpos(ini_get('disable_functions'), 'ini_set')
        ) {
            $oldValue = ini_get('xdebug.max_nesting_level');
            if (false === $oldValue || $oldValue < 256) {
                ini_set('xdebug.max_nesting_level', 256);
            }
        }

        if ($input->hasParameterOption(['--config-reference'])) {
            $input = new ArrayInput(['--config-reference' => true]);
        }

        if ($path = $input->getParameterOption(['--config', '-c'])) {
            if (!is_file($path)) {
                throw new ConfigurationLoadingException('The requested config file does not exist');
            }

            $this->configurationLoader->setConfigurationFilePath($path);
        }

        $this->add($this->createCommand($input, $output));

        return parent::doRun($input, $output);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->createContainer($this->input, $this->output);
    }

    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();

        $commands[] = new DumpReferenceCommand($this->extensionManager);
        $commands[] = new DebugCommand($this, $this->configurationLoader, $this->extensionManager);

        return $commands;
    }

    /**
     * Gets the name of the command based on input.
     *
     * @param InputInterface $input The input interface
     *
     * @return string The command name
     */
    protected function getCommandName(InputInterface $input)
    {
        if ($input->hasParameterOption(['--config-reference'])) {
            return 'dump-reference';
        }

        if ($input->hasParameterOption(['--debug'])) {
            return 'debug';
        }

        return $this->getName();
    }

    protected function configureIO(InputInterface $input, OutputInterface $output)
    {
        if (true === $input->hasParameterOption(['--colors'])) {
            $output->setDecorated(true);
        } elseif (true === $input->hasParameterOption(['--no-colors'])) {
            $output->setDecorated(false);
        }

        parent::configureIO($input, $output);
    }

    /**
     * Configures container based on provided config file and profile.
     *
     * @return array
     */
    private function loadConfiguration(InputInterface $input)
    {
        $profile = $input->getParameterOption(['--profile', '-p']) ?: 'default';

        return $this->configurationLoader->loadConfiguration($profile);
    }

    /**
     * Creates main command for application.
     *
     * @return SymfonyCommand
     */
    private function createCommand(InputInterface $input, OutputInterface $output)
    {
        return $this->getContainer()->get('cli.command');
    }

    /**
     * Creates container instance, loads extensions and freezes it.
     *
     * @return ContainerInterface
     */
    private function createContainer(InputInterface $input, OutputInterface $output)
    {
        if (null === $this->container) {
            $basePath = rtrim($this->getBasePath(), \DIRECTORY_SEPARATOR);

            $container = new ContainerBuilder();

            $container->setParameter('cli.command.name', $this->getName());
            $container->setParameter('paths.base', $basePath);

            $container->set('cli.input', $input);
            $container->set('cli.output', $output);

            $extension = new ContainerLoader($this->extensionManager);
            $extension->load($container, $this->loadConfiguration($input));
            $container->addObjectResource($extension);
            $container->compile();

            $this->container = $container;
        }

        return $this->container;
    }

    /**
     * Returns base path.
     *
     * @return string
     */
    private function getBasePath()
    {
        if ($configPath = $this->configurationLoader->getConfigurationFilePath()) {
            return realpath(\dirname($configPath));
        }

        return realpath(getcwd());
    }
}
