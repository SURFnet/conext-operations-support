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

use SimpleXMLElement;
use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;

final class AssertionConsumerService implements ConfiguredMetadataValidatable
{
    /** @var Binding */
    private $binding;

    /** @var BindingLocation */
    private $location;

    /** @var string|mixed */
    private $index;

    /**
     * @param array $data
     * @return AssertionConsumerService
     */
    public static function deserialize($data)
    {
        $binding = Binding::notSet();
        if (isset($data['Binding'])) {
            $binding = Binding::deserialize($data['Binding']);
        }

        $location = BindingLocation::notSet();
        if (isset($data['Location'])) {
            $location = BindingLocation::fromString($data['Location']);
        }

        $index = null;
        if (isset($data['index'])) {
            $index = $data['index'];
        }

        return new AssertionConsumerService($binding, $location, $index);
    }

    public static function fromXml(SimpleXMLElement $acsXml)
    {
        Assert::simpleXmlName($acsXml, 'AssertionConsumerService');

        $binding = Binding::notSet();
        if ($acsXml['Binding'] !== null) {
            $binding = Binding::deserialize((string) $acsXml['Binding']);
        }

        $location = BindingLocation::notSet();
        if ($acsXml['Location'] !== null) {
            $location = BindingLocation::fromString((string) $acsXml['Location']);
        }

        $index = null;
        if ($acsXml['index'] !== null) {
            $index = (string) $acsXml['index'];
        }

        return new AssertionConsumerService($binding, $location, $index);
    }

    /**
     * @param Binding         $binding
     * @param BindingLocation $location
     * @param string|mixed    $index
     */
    public function __construct(Binding $binding, BindingLocation $location, $index)
    {
        $this->binding  = $binding;
        $this->location = $location;
        $this->index    = $index;
    }

    public function validate(ConfiguredMetadataValidator $validator, ConfiguredMetadataValidationContext $context)
    {
        $validator->validate($this->binding, $context);
        $validator->validate($this->location, $context);

        if (!is_string($this->index)) {
            $validator->addViolation(
                sprintf(
                    'Binding index must be a string, got a "%s"',
                    is_object($this->index) ? get_class($this->index) : gettype($this->index)
                )
            );

            return;
        }

        if (!ctype_digit($this->index)) {
            $validator->addViolation(sprintf('Binding index must be a number, got "%s"', $this->index));
        }
    }

    /**
     * @param AssertionConsumerService $other
     * @return bool
     */
    public function equals(AssertionConsumerService $other)
    {
        return $this->binding->equals($other->binding) && $this->location->equals($other->location);
    }

    /**
     * @return mixed|string
     */
    public function getIndex()
    {
        return $this->index;
    }

    public function __toString()
    {
        return sprintf(
            'AssertionConsumerService(binding=%s, location=%s, index=%s)',
            $this->binding,
            $this->location,
            is_string($this->index) ? '"' . $this->index . '""' : '<invalid>'
        );
    }
}
