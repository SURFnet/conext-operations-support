<?php

/**
 * Copyright 2015 SURFnet bv
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
    private static $resolved = [];

    /**
     * @param $class
     * @return string
     */
    public static function resolveToString($class)
    {
        Assert::isObject($class);

        $className = get_class($class);

        if (array_key_exists($className, static::$resolved)) {
            return static::$resolved[$className];
        }

        if (!($class instanceof VerificationTest || $class instanceof VerificationSuite)) {
            throw new InvalidArgumentException(
                'NameResolver may only be used for instances of VerificationTest or VerificationSuite'
            );
        }

        return static::$resolved[$className] = strtolower(
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
}
