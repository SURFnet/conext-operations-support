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

use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Metadata\RegularExpression;
use Surfnet\Conext\EntityVerificationFramework\Metadata\ShibbolethMetadataScope;
use Surfnet\Conext\EntityVerificationFramework\Metadata\ShibbolethMetadataScopeList;

class ShibbolethMetadataScopeListTest extends TestCase
{
    /**
     * @test
     * @group Value
     * @dataProvider scopeLists
     *
     * @param array                       $scopeListData
     * @param ShibbolethMetadataScopeList $expectedScopeList
     */
    public function scope_lists_can_be_deserialized(
        array $scopeListData,
        ShibbolethMetadataScopeList $expectedScopeList
    ) {
        $this->assertEquals(
            $expectedScopeList,
            ShibbolethMetadataScopeList::deserialize($scopeListData, 'shibmd.scope')
        );
    }

    public function scopeLists()
    {
        $schacHomeLiteral = 'schacHome';
        $endsInGroningen  = 'Groningen$';

        return [
            'No scopes'  => [[], new ShibbolethMetadataScopeList()],
            'One scope'  => [
                [['allowed' => $schacHomeLiteral, 'regexp' => false]],
                new ShibbolethMetadataScopeList([ShibbolethMetadataScope::literal($schacHomeLiteral)]),
            ],
            'Two scopes' => [
                [
                    ['allowed' => $schacHomeLiteral, 'regexp' => false],
                    ['allowed' => $endsInGroningen, 'regexp' => true],
                ],
                new ShibbolethMetadataScopeList(
                    [
                        ShibbolethMetadataScope::literal($schacHomeLiteral),
                        ShibbolethMetadataScope::regexp(new RegularExpression('~' . $endsInGroningen . '~')),
                    ]
                ),
            ],
        ];
    }
}
