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

namespace Surfnet\Conext\EntityVerificationFramework\Tests;

use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Blacklist;
use Surfnet\Conext\EntityVerificationFramework\BlacklistFactory;

final class BlacklistFactoryTest extends TestCase
{
    /**
     * @test
     * @group Blacklist
     */
    public function it_can_create_a_blacklist()
    {
        // A wee smoke test.
        $blacklist = BlacklistFactory::fromDescriptors([
            '*'         => [['https://sp.invalid', 'sp']],
            'one_suite' => [['https://sp.invalid', 'sp']],
        ]);

        $this->assertInstanceOf(Blacklist::class, $blacklist);
    }
}
