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

namespace Surfnet\Conext\EntityVerificationFramework\Tests\Metadata;

use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Contact;

final class ContactTest extends TestCase
{
    /**
     * @test
     * @group Metadata
     */
    public function it_can_be_deserialized()
    {
        $data = [
            'contactType' => 'technical',
            'emailAddress' => 'webmaster@example.invalid',
            'givenName' => 'Web',
            'surName' => 'Master',
        ];
        Contact::deserialize($data, '');
    }

    /**
     * @test
     * @group Metadata
     */
    public function two_contacts_can_equal_each_other()
    {
        $data = [
            'contactType' => 'technical',
            'emailAddress' => 'webmaster@example.invalid',
            'givenName' => 'Web',
            'surName' => 'Master',
        ];
        $contact0 = Contact::deserialize($data, '');
        $contact1 = Contact::deserialize($data, '');

        $this->assertTrue($contact0->equals($contact1));
    }

    /**
     * @test
     * @group Metadata
     */
    public function two_contacts_can_not_be_equal()
    {
        $data = [
            'contactType' => 'technical',
            'emailAddress' => 'webmaster@example.invalid',
            'givenName' => 'Web',
            'surName' => 'Master',
        ];
        $contact0 = Contact::deserialize($data, '');

        $data = [
            'emailAddress' => 'webmaster@example.invalid',
            'givenName' => 'Web',
            'surName' => 'Master',
        ];
        $contact1 = Contact::deserialize($data, '');

        $data = [
            'contactType' => 'technical',
            'givenName' => 'Web',
            'surName' => 'Master',
        ];
        $contact2 = Contact::deserialize($data, '');

        $data = [
            'contactType' => 'technical',
            'emailAddress' => 'webmaster@example.invalid',
            'givenName' => 'Web',
            'surName' => 'Server',
        ];
        $contact3 = Contact::deserialize($data, '');

        $this->assertFalse($contact0->equals($contact1));
        $this->assertFalse($contact0->equals($contact2));
        $this->assertFalse($contact0->equals($contact3));
    }
}
