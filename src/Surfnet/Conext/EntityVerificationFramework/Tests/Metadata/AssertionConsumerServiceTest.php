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
use Surfnet\Conext\EntityVerificationFramework\Metadata\AssertionConsumerService;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Binding;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Url;

final class AssertionConsumerServiceTest extends TestCase
{
    /**
     * @test
     * @group Metadata
     */
    public function it_can_be_deserialized()
    {
        $bindingLocation = 'samba://media.invalid';
        $acs = AssertionConsumerService::deserialize([
            'Binding' => Binding::BINDING_HTTP_POST,
            'Location' => $bindingLocation,
        ]);

        $this->assertTrue(
            $acs->equals(
                new AssertionConsumerService(
                    Binding::create(Binding::BINDING_HTTP_POST),
                    Url::fromString($bindingLocation),
                    null
                )
            )
        );
    }

    /**
     * @test
     * @group Metadata
     */
    public function the_binding_can_be_omitted()
    {
        $bindingLocation = 'samba://media.invalid';
        $acs = AssertionConsumerService::deserialize([
            'Location' => $bindingLocation
        ]);

        $this->assertTrue(
            $acs->equals(
                new AssertionConsumerService(
                    Binding::notSet(),
                    Url::fromString($bindingLocation),
                    null
                )
            )
        );
    }

    /**
     * @test
     * @group Metadata
     */
    public function the_location_can_be_omitted()
    {
        $acs = AssertionConsumerService::deserialize([
            'Binding' => Binding::BINDING_HTTP_POST,
        ]);

        $this->assertTrue(
            $acs->equals(
                new AssertionConsumerService(
                    Binding::create(Binding::BINDING_HTTP_POST),
                    Url::notSet(),
                    null
                )
            )
        );
    }

    /**
     * @test
     * @group Metadata
     */
    public function two_acss_can_be_equals()
    {
        $acs0 = AssertionConsumerService::deserialize([
            'Binding' => Binding::BINDING_HTTP_POST,
            'Location' => 'samba://media.invalid',
        ]);
        $acs1 = AssertionConsumerService::deserialize([
            'Location' => 'samba://media.invalid',
            'Binding' => Binding::BINDING_HTTP_POST,
        ]);

        $this->assertTrue($acs0->equals($acs1));
    }
}
