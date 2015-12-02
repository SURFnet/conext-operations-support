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

class ShibbolethMetadataScopeTest extends TestCase
{
    /**
     * @test
     * @group Metadata
     * @dataProvider validArrayStructuresToDeserialise
     *
     * @param array                   $arrayStructure
     * @param ShibbolethMetadataScope $expected
     */
    public function shibmd_scope_can_be_deserialised(array $arrayStructure, ShibbolethMetadataScope $expected)
    {
        $actual = ShibbolethMetadataScope::deserialise($arrayStructure, 'prop.path');

        $this->assertTrue(
            $expected->equals($actual),
            sprintf(
                'Expected "%s" to be equal to deserialised "%s", "%s"',
                $expected,
                json_encode($arrayStructure),
                $actual
            )
        );
    }

    public function validArrayStructuresToDeserialise()
    {
        return [
            'Literal'                 => [
                ['allowed' => 'schacRemoteClub', 'regexp' => false],
                ShibbolethMetadataScope::literal('schacRemoteClub'),
            ],
            'Regexp'                  => [
                ['allowed' => 'schacRemoteClub', 'regexp' => true],
                ShibbolethMetadataScope::regexp(new RegularExpression('~schacRemoteClub~')),
            ],
            'No explicit regexp flag' => [
                ['allowed' => 'schacRemoteClub'],
                ShibbolethMetadataScope::literal('schacRemoteClub'),
            ],
        ];
    }
}
