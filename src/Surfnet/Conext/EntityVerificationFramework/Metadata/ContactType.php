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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidatable;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ConfiguredMetadataValidator;

final class ContactType implements ConfiguredMetadataValidatable
{
    const TYPE_TECHNICAL = 'technical';
    const TYPE_ADMINISTRATIVE = 'administrative';
    const TYPE_SUPPORT = 'support';

    const VALID_TYPES = [self::TYPE_TECHNICAL, self::TYPE_ADMINISTRATIVE, self::TYPE_SUPPORT];

    /** @var string|null */
    private $type;

    /**
     * @return ContactType
     */
    public static function unknown()
    {
        return new ContactType();
    }

    /**
     * @param string $type
     * @return ContactType
     */
    public static function fromString($type)
    {
        Assert::string($type, 'Contact type must be string');

        $contactType = new ContactType();
        $contactType->type = $type;

        return $contactType;
    }

    private function __construct()
    {
    }

    public function validate(
        ConfiguredMetadataValidator $validator,
        ConfiguredMetadataValidationContext $context
    ) {
        if (!in_array($this->type, self::VALID_TYPES, true)) {
            $validator->addViolation('Contact type must be one of "support", "administrative", "technical"');
        }
    }

    /**
     * @param ContactType $other
     * @return bool
     */
    public function equals(ContactType $other)
    {
        return $this->type === $other->type;
    }

    public function __toString()
    {
        return $this->type;
    }
}
