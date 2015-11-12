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
use Surfnet\Conext\EntityVerificationFramework\Metadata\ContactSet;

final class ContactSetTest extends TestCase
{
    /**
     * @test
     * @group value
     */
    public function it_can_create_a_set_of_two_contacts()
    {
        $contact0 = Contact::deserialise(['givenName' => 'Robèrt'], '');
        $contact1 = Contact::deserialise(['givenName' => 'Jean-Claude'], '');
        new ContactSet([$contact0, $contact1]);
    }
    /**
     * @test
     * @group value
     */
    public function it_can_create_a_set_containing_no_contacts()
    {
        new ContactSet([]);
    }

    /**
     * @test
     * @group value
     */
    public function it_can_check_whether_it_contains_a_contact()
    {
        $set = new ContactSet([Contact::deserialise(['givenName' => 'Robèrt'], '')]);

        $this->assertTrue($set->contains(Contact::deserialise(['givenName' => 'Robèrt'], '')));
        $this->assertFalse($set->contains(Contact::deserialise(['givenName' => 'Jean-Claude'], '')));
    }

    /**
     * @test
     * @group value
     */
    public function it_is_countable()
    {
        $contact0 = Contact::deserialise(['givenName' => 'Robèrt'], '');
        $contact1 = Contact::deserialise(['givenName' => 'Jean-Claude'], '');
        $set = new ContactSet([$contact0, $contact1]);

        $this->assertCount(2, $set);
    }

    /**
     * @test
     * @group value
     */
    public function it_behaves_as_a_set()
    {
        $set = new ContactSet([
            Contact::deserialise(['contactType' => 'technical'], ''),
            Contact::deserialise(['contactType' => 'technical'], ''),
        ]);
        $this->assertCount(1, $set);

        $set = $set->add(Contact::deserialise(['givenName' => 'Renault', 'surName' => 'du Grande'], ''));
        $this->assertCount(2, $set);

        $set = $set->add(Contact::deserialise(['contactType' => 'technical'], ''));
        $this->assertCount(2, $set);
    }
}
