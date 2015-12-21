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

final class SingleSignOnService implements ConfiguredMetadataValidatable
{
    /** @var Binding */
    private $binding;

    /** @var Url */
    private $location;

    /**
     * @param array $data
     * @return SingleSignOnService
     */
    public static function deserialize($data)
    {
        $binding = Binding::notSet();
        if (isset($data['Binding'])) {
            $binding = Binding::deserialize($data['Binding']);
        }

        $location = Url::notSet();
        if (isset($data['Location'])) {
            $location = Url::fromString($data['Location']);
        }

        return new SingleSignOnService($binding, $location);
    }

    /**
     * @param SimpleXMLElement $ssoXml
     * @return SingleSignOnService
     */
    public static function fromXml(SimpleXMLElement $ssoXml)
    {
        Assert::simpleXmlName($ssoXml, 'SingleSignOnService');

        $binding = Binding::notSet();
        if ($ssoXml['Binding'] !== null) {
            $binding = Binding::deserialize((string) $ssoXml['Binding']);
        }

        $location = Url::notSet();
        if ($ssoXml['Location'] !== null) {
            $location = Url::fromString((string) $ssoXml['Location']);
        }

        return new SingleSignOnService($binding, $location);
    }

    /**
     * @param Binding $binding
     * @param Url     $location
     */
    public function __construct(Binding $binding, Url $location)
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
     * @param SingleSignOnService $other
     * @return bool
     */
    public function equals(SingleSignOnService $other)
    {
        return $this->binding->equals($other->binding) && $this->location->equals($other->location);
    }

    public function __toString()
    {
        return sprintf('SingleSignOnService(binding=%s, location=%s)', $this->binding, $this->location);
    }
}
