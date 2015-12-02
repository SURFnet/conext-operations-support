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

    /**
     * @param array $data
     * @return AssertionConsumerService
     */
    public static function deserialize($data)
    {
        $binding = Binding::unknown();
        if (isset($data['Binding'])) {
            $binding = Binding::deserialize($data['Binding']);
        }

        $location = BindingLocation::unknown();
        if (isset($data['Location'])) {
            $location = BindingLocation::fromString($data['Location']);
        }

        return new AssertionConsumerService($binding, $location);
    }

    public static function fromXml(SimpleXMLElement $acsXml)
    {
        Assert::simpleXmlName($acsXml, 'AssertionConsumerService');

        $binding = Binding::unknown();
        if ($acsXml['Binding'] !== null) {
            $binding = Binding::deserialize((string) $acsXml['Binding']);
        }

        $location = BindingLocation::unknown();
        if ($acsXml['Location'] !== null) {
            $location = BindingLocation::fromString((string) $acsXml['Location']);
        }

        return new AssertionConsumerService($binding, $location);
    }

    /**
     * @param Binding         $binding
     * @param BindingLocation $location
     */
    public function __construct(Binding $binding, BindingLocation $location)
    {
        $this->binding  = $binding;
        $this->location = $location;
    }

    public function validate(ConfiguredMetadataValidator $validator, ConfiguredMetadataValidationContext $context)
    {
        $validator->validate($this->binding, $context);
        $validator->validate($this->location, $context);
    }

    /**
     * @param AssertionConsumerService $other
     * @return bool
     */
    public function equals(AssertionConsumerService $other)
    {
        return $this->binding->equals($other->binding) && $this->location->equals($other->location);
    }

    public function __toString()
    {
        return sprintf('AssertionConsumerService(binding=%s, location=%s)', $this->binding, $this->location);
    }
}
