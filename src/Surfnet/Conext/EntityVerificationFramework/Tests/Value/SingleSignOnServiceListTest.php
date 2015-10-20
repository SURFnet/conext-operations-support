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
use Surfnet\Conext\EntityVerificationFramework\Value\Binding;
use Surfnet\Conext\EntityVerificationFramework\Value\SingleSignOnServiceList;

final class SingleSignOnServiceListTest extends TestCase
{
    /**
     * @test
     * @group value
     */
    public function it_can_deserialise_one_binding()
    {
        $bindings = [
            [
                'Binding' => Binding::BINDING_HTTP_POST,
                'Location' => 'samba://media.invalid'
            ]
        ];
        $list = SingleSignOnServiceList::deserialise($bindings, '');

        $this->assertCount(1, $list);
    }

    /**
     * @test
     * @group value
     */
    public function it_can_deserialise_two_equal_bindings()
    {
        $bindings = [
            [
                'Binding' => Binding::BINDING_HTTP_POST,
                'Location' => 'samba://media.invalid'
            ],
            [
                'Binding' => Binding::BINDING_HTTP_POST,
                'Location' => 'samba://media.invalid'
            ]
        ];
        $list = SingleSignOnServiceList::deserialise($bindings, '');

        $this->assertCount(2, $list);
    }

    /**
     * @test
     * @group value
     */
    public function it_can_deserialise_zero_bindings()
    {
        $bindings = [];
        $list = SingleSignOnServiceList::deserialise($bindings, '');

        $this->assertCount(0, $list);
    }
}
