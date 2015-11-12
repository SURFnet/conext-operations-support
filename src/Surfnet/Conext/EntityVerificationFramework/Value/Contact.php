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

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) -- Methods are relevant, not complex.
 */
final class Contact
{
    /** @var ContactType|null */
    private $type;
    /** @var EmailAddress|null */
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
    public static function deserialise($data, $propertyPath)
    {
        Assert::isArray($data, 'Contact data must be array structure', $propertyPath);

        $contact = new Contact();

        if (isset($data['contactType'])) {
            $contact->type = new ContactType($data['contactType']);
        }

        if (isset($data['emailAddress'])) {
            $contact->email = new EmailAddress($data['emailAddress']);
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
     * @param ContactType|null  $type
     * @param EmailAddress|null $email
     * @param string|null       $givenName
     * @param string|null       $surName
     */
    public function __construct(
        ContactType $type = null,
        EmailAddress $email = null,
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

    /**
     * @return bool
     */
    public function hasContactType()
    {
        return $this->type !== null;
    }

    /**
     * @return bool
     */
    public function hasValidContactType()
    {
        return $this->type && $this->type->isValid();
    }

    /**
     * @return bool
     */
    public function hasSurName()
    {
        return $this->surName !== null;
    }

    /**
     * @return bool
     */
    public function hasFilledSurName()
    {
        return $this->surName !== '';
    }

    /**
     * @return bool
     */
    public function hasGivenName()
    {
        return $this->givenName !== null;
    }

    /**
     * @return bool
     */
    public function hasFilledGivenName()
    {
        return $this->givenName !== '';
    }

    /**
     * @return bool
     */
    public function hasEmailAddress()
    {
        return $this->email !== null;
    }

    /**
     * @return bool
     */
    public function hasValidEmailAddress()
    {
        return $this->email && $this->email->isValid();
    }

    /**
     * @param Contact $other
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function equals(Contact $other)
    {
        return ($this->type === $other->type
                || $this->type && $other->type && $this->type->equals($other->type))
            && ($this->email === $other->email
                || $this->email && $other->email && $this->email->equals($other->email))
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
