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

namespace Surfnet\Conext\EntityVerificationFramework\Metadata;

use Surfnet\Conext\EntityVerificationFramework\Assert;

final class NameIdFormat
{
    const URN_SAML_11_UNSPECIFIED = 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified';
    const URN_SAML_20_TRANSIENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';
    const URN_SAML_20_PERSISTENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';

    const VALID_URNS = [
        self::URN_SAML_11_UNSPECIFIED,
        self::URN_SAML_20_TRANSIENT,
        self::URN_SAML_20_PERSISTENT,
    ];

    /**
     * @var string
     */
    private $urn;

    /**
     * @return NameIdFormat
     */
    public static function unknown()
    {
        return new NameIdFormat();
    }

    /**
     * @param string $urn
     * @return NameIdFormat
     */
    public static function fromUrn($urn)
    {
        Assert::string($urn, 'NameIDFormat URN must be string');

        $format = new NameIdFormat();
        $format->urn = $urn;

        return $format;
    }

    private function __construct()
    {
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
        return $this->urn;
    }
}
