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

final class Contact
{
    /** @var ContactType */
    private $type;
    /** @var EmailAddress */
    private $email;
    /** @var string */
    private $givenName;
    /** @var string  */
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
            $contact->email = EmailAddress::deserialise(
                $data['emailAddress'],
                sprintf('%s.emailAddress', $propertyPath)
            );
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
     * @param Contact $other
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function equals(Contact $other)
    {
        if ($this->type === null || $other->type === null) {
            $valid = $this->type === $other->type;
        } else {
            $valid = $this->type->equals($other->type);
        }

        if ($this->email === null || $other->email === null) {
            $valid = $valid && $this->email === $other->email;
        } else {
            $valid = $valid && $this->email->equals($other->email);
        }

        return $valid && $this->givenName === $other->givenName && $this->surName === $other->surName;
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
