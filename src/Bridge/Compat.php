<?php

/*
 * This file is part of the DoyoUserBundle project.
 *
 * (c) Anthonius Munthi <me@itstoni.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Doyo\Behat\Coverage\Bridge;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Version;

/**
 * Utility class to load driver by php-code-coverage version.
 */
class Compat
{
    public static function getDriverClass($name)
    {
        $namespace = 'Doyo\\Behat\\Coverage\\Bridge\\Driver\\';
        if (version_compare(static::getVersion(), '6.0', '<')) {
            $namespace = $namespace.'Compat\\';
        }
        $class = $namespace.$name;

        return $class;
    }

    public static function createDriver($name)
    {
        $class = static::getDriverClass($name);

        return new $class();
    }

    public static function getVersion()
    {
        static $version;
        if (null === $version) {
            $version = Version::id();
        }

        return $version;
    }

    public static function getCoverageValidConfigs()
    {
        $r       = new \ReflectionClass(CodeCoverage::class);
        $configs = [];
        $ignored = [
            'data',
            'tests',
            'cachetokens',
            'maptestclassnametocoveredclassname',
        ];
        foreach ($r->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $methodName = $method->getName();
            if (false === strpos($methodName, 'set')) {
                continue;
            }
            $configName = substr($methodName, 3);
            $lower      = strtolower($configName);
            if (\in_array($lower, $ignored, true)) {
                continue;
            }
            $defaults             = $r->getDefaultProperties();
            $configName           = lcfirst($configName);
            $configs[$configName] = $defaults[$configName];
        }

        return $configs;
    }
}
