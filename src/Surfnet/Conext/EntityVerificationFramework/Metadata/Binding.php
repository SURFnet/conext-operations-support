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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataConstraintViolationWriter;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataVisitor;

final class Binding implements ConfiguredMetadataValidatable
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

    /**
     * @var mixed
     */
    private $binding;

    /**
     * @var bool
     */
    private $set = true;

    /**
     * @param mixed $data
     * @return Binding
     */
    public static function deserialize($data)
    {
        $binding = new self();
        $binding->binding = $data;

        return $binding;
    }

    /**
     * @param string $constant
     * @return Binding
     */
    public static function create($constant)
    {
        Assert::choice($constant, self::VALID_BINDINGS, 'Binding "%s" is not one of the valid bindings');

        $binding = new self();
        $binding->binding = $constant;

        return $binding;
    }

    /**
     * @return Binding
     */
    public static function notSet()
    {
        $binding = new self();
        $binding->set = false;

        return $binding;
    }

    private function __construct()
    {
    }

    public function validate(
        ConfiguredMetadataVisitor $visitor,
        ConfiguredMetadataConstraintViolationWriter $violations,
        ConfiguredMetadataValidationContext $context
    ) {
        if (in_array($this->binding, self::VALID_BINDINGS, true)) {
            return;
        }

        if (!is_string($this->binding)) {
            $type = is_object($this->binding) ? get_class($this->binding) : gettype($this->binding);
            $violations->add(
                sprintf('Binding must be one of "%s", got type "%s"', join('", "', self::VALID_BINDINGS), $type)
            );

            return;
        }

        $violations->add(
            sprintf('Binding must be one of "%s", got "%s"', join('", "', self::VALID_BINDINGS), $this->binding)
        );
    }

    /**
     * @param Binding $other
     * @return bool
     */
    public function equals(Binding $other)
    {
        return $this->binding === $other->binding;
    }

    public function __toString()
    {
        return $this->set === false ? 'Binding<not-set>' : 'Binding(' . $this->binding . ')';
    }
}
