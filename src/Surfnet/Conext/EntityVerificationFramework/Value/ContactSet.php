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

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Surfnet\Conext\EntityVerificationFramework\Assert;

final class ContactSet implements IteratorAggregate, Countable
{
    /**
     * @var Contact[]
     */
    private $contacts;

    /**
     * @param array[]  $data
     * @param string $propertyPath
     * @return ContactSet
     */
    public static function deserialise($data, $propertyPath)
    {
        Assert::isArray($data, 'Data to deserialise must be an array', $propertyPath);

        return new ContactSet(
            array_map(
                function ($data) use ($propertyPath) {
                    return Contact::deserialise($data, $propertyPath . '[]');
                },
                $data
            )
        );
    }

    /**
     * @param Contact[] $contacts
     */
    public function __construct(array $contacts = [])
    {
        foreach ($contacts as $contact) {
            $this->add($contact);
        }
    }

    /**
     * @param Contact $contact
     * @return bool FALSE when Contact is already present (by value) in the set.
     */
    public function add(Contact $contact)
    {
        if ($this->contains($contact)) {
            return false;
        }

        $this->contacts[] = $contact;

        return true;
    }

    /**
     * @param Contact $contact The contact to search for.
     *
     * @return boolean TRUE if the collection contains the element, FALSE otherwise.
     */
    public function contains(Contact $contact)
    {
        foreach ($this->contacts as $existingContact) {
            if ($contact->equals($existingContact)) {
                return true;
            }
        }

        return false;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->contacts);
    }

    public function count()
    {
        return count($this->contacts);
    }
}
