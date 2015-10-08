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

namespace Surfnet\Conext\EntityVerificationFramework\Tests\Value;

use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Tests\DataProvider\DataProvider;
use Surfnet\Conext\EntityVerificationFramework\Value\ApplicationUrl;
use Surfnet\Conext\EntityVerificationFramework\Value\Contact;

final class ContactTest extends TestCase
{
    /**
     * @test
     * @group value
     */
    public function it_can_be_deserialised()
    {
        $data = [
            'contactType' => 'technical',
            'emailAddress' => 'webmaster@example.invalid',
            'givenName' => 'Web',
            'surName' => 'Master',
        ];
        Contact::deserialise($data, '');
    }

    /**
     * @test
     * @group value
     */
    public function two_contacts_can_equal_each_other()
    {
        $data = [
            'contactType' => 'technical',
            'emailAddress' => 'webmaster@example.invalid',
            'givenName' => 'Web',
            'surName' => 'Master',
        ];
        $contact0 = Contact::deserialise($data, '');
        $contact1 = Contact::deserialise($data, '');

        $this->assertTrue($contact0->equals($contact1));
    }

    /**
     * @test
     * @group value
     */
    public function two_contacts_can_not_be_equal()
    {
        $data = [
            'contactType' => 'technical',
            'emailAddress' => 'webmaster@example.invalid',
            'givenName' => 'Web',
            'surName' => 'Master',
        ];
        $contact0 = Contact::deserialise($data, '');

        $data = [
            'emailAddress' => 'webmaster@example.invalid',
            'givenName' => 'Web',
            'surName' => 'Master',
        ];
        $contact1 = Contact::deserialise($data, '');

        $data = [
            'contactType' => 'technical',
            'givenName' => 'Web',
            'surName' => 'Master',
        ];
        $contact2 = Contact::deserialise($data, '');

        $data = [
            'contactType' => 'technical',
            'emailAddress' => 'webmaster@example.invalid',
            'givenName' => 'Web',
            'surName' => 'Server',
        ];
        $contact3 = Contact::deserialise($data, '');

        $this->assertFalse($contact0->equals($contact1));
        $this->assertFalse($contact0->equals($contact2));
        $this->assertFalse($contact0->equals($contact3));
    }
}
