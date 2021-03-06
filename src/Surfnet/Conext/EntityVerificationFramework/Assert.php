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

use Assert\Assertion as BaseAssertion;
use SimpleXMLElement;

class Assert extends BaseAssertion
{
    protected static $exceptionClass = 'Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException';

    public static function simpleXmlName(
        SimpleXMLElement $element,
        $expectedName,
        $message = null,
        $propertyPath = null
    ) {
        $actualName = $element->getName();

        if (!$message) {
            $message = sprintf(
                'Failed to assert SimpleXMLElement\'s name "%s" equals "%s"',
                self::stringify($actualName),
                self::stringify($expectedName)
            );
        }

        static::eq($actualName, $expectedName, $message, $propertyPath);
    }
}
