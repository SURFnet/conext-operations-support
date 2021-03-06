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

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) -- Methods are relevant, not complex.
 */
final class Contact implements ConfiguredMetadataValidatable
{
    /** @var ContactType */
    private $type;
    /** @var EmailAddress */
    private $email;
    /** @var string|null */
    private $givenName;
    /** @var string|null */
    private $surName;

    /**
     * @param string[] $data
     * @param string   $propertyPath
     * @return Contact
     */
    public static function deserialize($data, $propertyPath)
    {
        Assert::isArray($data, 'Contact data must be array structure', $propertyPath);

        $contact = new Contact(ContactType::notSet(), EmailAddress::notSet());

        if (isset($data['contactType'])) {
            $contact->type = ContactType::fromString($data['contactType']);
        }

        if (isset($data['emailAddress'])) {
            $contact->email = EmailAddress::fromString($data['emailAddress']);
        }

        if (isset($data['givenName'])) {
            Assert::string(
                $data['givenName'],
                'Contact givenName must be a string',
                sprintf('%s.givenName', $propertyPath)
            );
            $contact->givenName = $data['givenName'];
        }

        if (isset($data['surName'])) {
            Assert::string($data['surName'], 'Contact surName must be a string', sprintf('%s.surName', $propertyPath));
            $contact->surName = $data['surName'];
        }

        return $contact;
    }

    /**
     * @param ContactType  $type
     * @param EmailAddress $email
     * @param string|null  $givenName
     * @param string|null  $surName
     */
    public function __construct(
        ContactType $type,
        EmailAddress $email,
        $givenName = null,
        $surName = null
    ) {
        Assert::nullOrString($givenName, 'Contact givenName must be a string or null');
        Assert::nullOrString($surName, 'Contact surName must be a string or null');

        $this->type      = $type;
        $this->email     = $email;
        $this->givenName = $givenName;
        $this->surName   = $surName;
    }

    public function validate(
        ConfiguredMetadataVisitor $visitor,
        ConfiguredMetadataConstraintViolationWriter $violations,
        ConfiguredMetadataValidationContext $context
    ) {
        $visitor->visit($this->type, $violations, $context);
        $visitor->visit($this->email, $violations, $context);

        if (trim($this->givenName) === '') {
            $violations->add('Contact given name is not configured or empty');
        }
        if (trim($this->surName) === '') {
            $violations->add('Contact surname is not configured or empty');
        }
    }

    /**
     * @param Contact $other
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function equals(Contact $other)
    {
        return $this->type->equals($other->type)
            && $this->email->equals($other->email)
            && $this->givenName === $other->givenName
            && $this->surName === $other->surName;
    }

    public function __toString()
    {
        return sprintf(
            'Contact(type=%s, email=%s, givenName=%s, surName=%s)',
            $this->type,
            $this->email,
            $this->givenName,
            $this->surName
        );
    }
}
