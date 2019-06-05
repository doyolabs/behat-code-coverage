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

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Behat\Definition\ServiceContainer\DefinitionExtension;
use Behat\Behat\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Behat\Gherkin\ServiceContainer\GherkinExtension;
use Behat\Behat\HelperContainer\ServiceContainer\HelperContainerExtension;
use Behat\Behat\Hook\ServiceContainer\HookExtension;
use Behat\Behat\Output\ServiceContainer\Formatter\JUnitFormatterFactory;
use Behat\Behat\Output\ServiceContainer\Formatter\PrettyFormatterFactory;
use Behat\Behat\Output\ServiceContainer\Formatter\ProgressFormatterFactory;
use Behat\Behat\Snippet\ServiceContainer\SnippetExtension;
use Behat\Behat\Tester\ServiceContainer\TesterExtension;
use Behat\Behat\Transformation\ServiceContainer\TransformationExtension;
use Behat\Behat\Translator\ServiceContainer\GherkinTranslationsExtension;
use Behat\Testwork\ApplicationFactory as BaseApplicationFactory;
use Behat\Testwork\Argument\ServiceContainer\ArgumentExtension;
use Behat\Testwork\Autoloader\ServiceContainer\AutoloaderExtension;
use Behat\Testwork\Call\ServiceContainer\CallExtension;
use Behat\Testwork\Cli\ServiceContainer\CliExtension;
use Behat\Testwork\Environment\ServiceContainer\EnvironmentExtension;
use Behat\Testwork\Exception\ServiceContainer\ExceptionExtension;
use Behat\Testwork\Filesystem\ServiceContainer\FilesystemExtension;
use Behat\Testwork\Ordering\ServiceContainer\OrderingExtension;
use Behat\Testwork\Output\ServiceContainer\Formatter\FormatterFactory;
use Behat\Testwork\Output\ServiceContainer\OutputExtension;
use Behat\Testwork\ServiceContainer\ServiceProcessor;
use Behat\Testwork\Specification\ServiceContainer\SpecificationExtension;
use Behat\Testwork\Suite\ServiceContainer\SuiteExtension;
use Behat\Testwork\Translator\ServiceContainer\TranslatorExtension;

class ApplicationFactory extends BaseApplicationFactory
{
    const VERSION = '3.5.0';

    /**
     * @var string
     */
    private $configPath;

    /**
     * ApplicationFactory constructor.
     */
    public function __construct()
    {
        $this->configPath = __DIR__.'/behat.yaml';
    }

    public function setConfigPath(string $configPath): self
    {
        if (!is_file($configPath)) {
            throw new \InvalidArgumentException('File: '.$configPath.' not exists');
        }
        $this->configPath = $configPath;

        return $this;
    }

    /**
     * Creates application instance.
     *
     * @return Application
     */
    public function createApplication()
    {
        $configurationLoader = $this->createConfigurationLoader();
        $extensionManager    = $this->createExtensionManager();

        return new Application($this->getName(), $this->getVersion(), $configurationLoader, $extensionManager);
    }

    /**
     * {@inheritdoc}
     */
    protected function getName()
    {
        return 'doyo-test';
    }

    /**
     * {@inheritdoc}
     */
    protected function getVersion()
    {
        return self::VERSION;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultExtensions()
    {
        $processor = new ServiceProcessor();

        return [
            new ArgumentExtension(),
            new AutoloaderExtension(['' => '%paths.base%/features/bootstrap']),
            new SuiteExtension($processor),
            new OutputExtension('pretty', $this->getDefaultFormatterFactories($processor), $processor),
            new ExceptionExtension($processor),
            new GherkinExtension($processor),
            new CallExtension($processor),
            new TranslatorExtension(),
            new GherkinTranslationsExtension(),
            new TesterExtension($processor),
            new CliExtension($processor),
            new EnvironmentExtension($processor),
            new SpecificationExtension($processor),
            new FilesystemExtension(),
            new ContextExtension($processor),
            new SnippetExtension($processor),
            new DefinitionExtension($processor),
            new EventDispatcherExtension($processor),
            new HookExtension(),
            new TransformationExtension($processor),
            new OrderingExtension($processor),
            new HelperContainerExtension($processor),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnvironmentVariableName()
    {
        return 'BEHAT_PARAMS';
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfigPath()
    {
        return $this->configPath;
    }

    /**
     * Returns default formatter factories.
     *
     * @return FormatterFactory[]
     */
    private function getDefaultFormatterFactories(ServiceProcessor $processor)
    {
        return [
            new PrettyFormatterFactory($processor),
            new ProgressFormatterFactory($processor),
            new JUnitFormatterFactory(),
        ];
    }
}
