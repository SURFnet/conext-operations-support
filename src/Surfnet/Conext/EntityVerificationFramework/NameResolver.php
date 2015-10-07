<?php

/**
 * Copyright 2015 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Surfnet\Conext\EntityVerificationFramework;

use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuite;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTest;
use Surfnet\Conext\EntityVerificationFramework\Exception\InvalidArgumentException;

final class NameResolver
{
    /**
     * @var array internal cache
     */
    private static $resolvedStrings = [];

    /**
     * @var array internal cache
     */
    private static $resolvedClasses = [];


    /**
     * @param $class
     * @return string
     */
    public static function resolveToString($class)
    {
        Assert::isObject($class);

        $className = get_class($class);

        if (array_key_exists($className, self::$resolvedStrings)) {
            return self::$resolvedStrings[$className];
        }

        if (!($class instanceof VerificationTest || $class instanceof VerificationSuite)) {
            throw new InvalidArgumentException(
                'NameResolver may only be used for instances of VerificationTest or VerificationSuite'
            );
        }

        return self::$resolvedStrings[$className] = strtolower(
            preg_replace(
                [
                    '~^Surfnet\\\\VerificationSuite\\\\([a-zA-Z]+)\\\\([a-zA-Z]+$)?~',
                    '~Test\\\\~',
                    '~\\\\~',
                    '~([a-z])([A-Z])~'
                ],
                [
                    '$1',
                    '.',
                    '_',
                    '$1_$2'
                ],
                $className
            )
        );
    }

    /**
     * @param $className
     * @return string
     */
    public static function resolveToClass($className)
    {
        Assert::string($className);
        Assert::notBlank($className);

        if (array_key_exists($className, self::$resolvedStrings)) {
            return self::$resolvedClasses[$className];
        }

        $resolvedClassName = self::convertToClassName($className);

        if (!class_exists($resolvedClassName)) {
            throw new InvalidArgumentException(sprintf('Resolved class "%s" does not exist', $resolvedClassName));
        }

        return self::$resolvedClasses[$className] = $resolvedClassName;
    }

    /**
     * @param $className
     * @return string
     */
    private static function convertToClassName($className)
    {
        $namespace = 'Surfnet\\VerificationSuite\\';

        if (strpos($className, '.') === false) {
            $camelCased = self::underscoreToCamelCase($className);

            return $namespace . $camelCased . '\\' . $camelCased;
        }

        $parts = explode('.', $className);

        $suiteNamespace = self::underscoreToCamelCase($parts[0]);
        $testName = self::underscoreToCamelCase($parts[1]);

        return $namespace . $suiteNamespace . '\\Test\\' . $testName;
    }

    /**
     * @param $string
     * @return mixed
     */
    private static function underscoreToCamelCase($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }
}
