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

namespace Surfnet\Conext\EntityVerificationFramework\Value;

use Surfnet\Conext\EntityVerificationFramework\Assert;

final class NameIdFormat
{
    const FORMAT_SAML_11_UNSPECIFIED = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';
    const FORMAT_SAML_20_TRANSIENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';
    const FORMAT_SAML_20_PERSISTENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';

    const VALID_FORMATS = [
        self::FORMAT_SAML_11_UNSPECIFIED,
        self::FORMAT_SAML_20_TRANSIENT,
        self::FORMAT_SAML_20_PERSISTENT,
    ];

    /**
     * @var string
     */
    private $format;

    /**
     * @param string $format
     */
    public function __construct($format)
    {
        Assert::string($format, 'NameIDFormat must be string');

        $this->format = $format;
    }

    /**
     * @return bool
     */
    public function isValidFormat()
    {
        return in_array($this->format, self::VALID_FORMATS, true);
    }

    /**
     * @param NameIdFormat $other
     * @return bool
     */
    public function equals(NameIdFormat $other)
    {
        return $this == $other;
    }

    public function __toString()
    {
        return $this->format;
    }
}
