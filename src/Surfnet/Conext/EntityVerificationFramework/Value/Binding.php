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

final class Binding
{
    const BINDING_HTTP_REDIRECT = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Redirect';
    const BINDING_HTTP_POST = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';
    const BINDING_SOAP = 'urn:oasis:names:tc:SAML:2.0:bindings:SOAP';
    const BINDING_PAOS = 'urn:oasis:names:tc:SAML:2.0:bindings:PAOS';
    const BINDING_HTTP_ARTIFACT = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-Artifact';
    const BINDING_URI = 'urn:oasis:names:tc:SAML:2.0:bindings:URI';

    const VALID_BINDINGS = [
        self::BINDING_HTTP_REDIRECT,
        self::BINDING_HTTP_POST,
        self::BINDING_SOAP,
        self::BINDING_PAOS,
        self::BINDING_HTTP_ARTIFACT,
        self::BINDING_URI,
    ];

    /** @var mixed */
    private $binding;

    /**
     * @param mixed $data
     * @return Binding
     */
    public static function deserialise($data)
    {
        $binding = new self();
        $binding->binding = $data;

        return $binding;
    }

    private function __construct()
    {
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        return in_array($this->binding, self::VALID_BINDINGS, true);
    }

    /**
     * @param Binding $other
     * @return bool
     */
    public function equals(Binding $other)
    {
        return $this == $other;
    }

    public function __toString()
    {
        return $this->binding;
    }
}
