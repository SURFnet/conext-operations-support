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
use Surfnet\Conext\EntityVerificationFramework\Metadata\Binding;
use Surfnet\Conext\EntityVerificationFramework\Metadata\SingleSignOnService;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Url;

final class SingleSignOnServiceTest extends TestCase
{
    /**
     * @test
     * @group value
     */
    public function it_can_be_deserialized()
    {
        SingleSignOnService::deserialize([
            'Binding' => Binding::BINDING_HTTP_POST,
            'Location' => 'samba://media.invalid'
        ]);
    }

    /**
     * @test
     * @group value
     */
    public function it_has_a_binding()
    {
        $acs = SingleSignOnService::deserialize([
            'Binding' => Binding::BINDING_HTTP_POST,
            'Location' => 'samba://media.invalid'
        ]);

        $this->assertTrue($acs->hasBinding());
        $this->assertInstanceOf(Binding::class, $acs->getBinding());
    }

    /**
     * @test
     * @group value
     */
    public function it_has_a_location()
    {
        $acs = SingleSignOnService::deserialize([
            'Binding' => Binding::BINDING_HTTP_POST,
            'Location' => 'samba://media.invalid'
        ]);

        $this->assertTrue($acs->hasLocation());
        $this->assertInstanceOf(Url::class, $acs->getLocation());
    }

    /**
     * @test
     * @group value
     */
    public function it_is_valid()
    {
        $acs = SingleSignOnService::deserialize([
            'Binding' => Binding::BINDING_HTTP_POST,
            'Location' => 'samba://media.invalid'
        ]);

        $this->assertTrue($acs->isValid());
    }

    /**
     * @test
     * @group value
     */
    public function the_binding_can_be_omitted()
    {
        $acs = SingleSignOnService::deserialize([
            'Location' => 'samba://media.invalid'
        ]);

        $this->assertFalse($acs->hasBinding());
        $this->assertFalse($acs->isBindingValid());
        $this->assertFalse($acs->isValid());
    }

    /**
     * @test
     * @group value
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\LogicException
     */
    public function when_the_binding_is_omitted_the_binding_is_not_available()
    {
        $acs = SingleSignOnService::deserialize([
            'Location' => 'samba://media.invalid'
        ]);
        $acs->getBinding();
    }

    /**
     * @test
     * @group value
     */
    public function the_location_can_be_omitted()
    {
        $acs = SingleSignOnService::deserialize([
            'Binding' => Binding::BINDING_HTTP_POST,
        ]);

        $this->assertFalse($acs->hasLocation());
        $this->assertFalse($acs->isLocationValid());
        $this->assertFalse($acs->isValid());
    }

    /**
     * @test
     * @group value
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\LogicException
     */
    public function when_the_location_is_omitted_the_location_is_not_available()
    {
        $acs = SingleSignOnService::deserialize([
            'Binding' => Binding::BINDING_HTTP_POST,
        ]);
        $acs->getLocation();
    }

    /**
     * @test
     * @group value
     */
    public function two_acss_can_be_equals()
    {
        $acs0 = SingleSignOnService::deserialize([
            'Binding' => Binding::BINDING_HTTP_POST,
            'Location' => 'samba://media.invalid',
        ]);
        $acs1 = SingleSignOnService::deserialize([
            'Location' => 'samba://media.invalid',
            'Binding' => Binding::BINDING_HTTP_POST,
        ]);

        $this->assertTrue($acs0->equals($acs1));
    }
}
