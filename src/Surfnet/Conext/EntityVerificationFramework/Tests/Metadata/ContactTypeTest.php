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
use Mockery as m;
use Mockery\MockInterface;
use Surfnet\Conext\EntityVerificationFramework\Metadata\ContactType;

class ContactTypeTest extends TestCase
{
    /**
     * @test
     * @group value
     */
    public function contact_types_can_equal_each_other()
    {
        $this->assertTrue(
            ContactType::fromString(ContactType::TYPE_SUPPORT)
                ->equals(ContactType::fromString(ContactType::TYPE_SUPPORT)),
            'Two support contact types should equal each other'
        );
        $this->assertTrue(
            ContactType::unknown()->equals(ContactType::unknown()),
            'Two unknown contact types should equal each other'
        );
    }

    /**
     * @test
     * @group value
     */
    public function contact_types_can_not_equal_each_other()
    {
        $this->assertFalse(
            ContactType::fromString(ContactType::TYPE_ADMINISTRATIVE)
                ->equals(ContactType::fromString(ContactType::TYPE_SUPPORT)),
            'An administrative contact type should not equal a support contact type'
        );
        $this->assertFalse(
            ContactType::fromString(ContactType::TYPE_TECHNICAL)
                ->equals(ContactType::unknown()),
            'A technical contact type should not equal an unknown contact type'
        );
        $this->assertFalse(
            ContactType::fromString('')
                ->equals(ContactType::unknown()),
            'An empty contact type should not equal an unknown contact type'
        );
    }
}
