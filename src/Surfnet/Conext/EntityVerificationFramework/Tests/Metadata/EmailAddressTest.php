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
use Mockery\MockInterface;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Metadata\EmailAddress;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\ValidationContext;
use Surfnet\Conext\EntityVerificationFramework\Metadata\Validator\ConfiguredMetadata\Validator;
use Surfnet\Conext\EntityVerificationFramework\Tests\DataProvider\DataProvider;

final class EmailAddressTest extends TestCase
{
    use DataProvider;

    /**
     * @test
     * @group value
     */
    public function it_deserialises_emails()
    {
        EmailAddress::fromString('juliette.dupree+spam@that.invalid');
    }

    /**
     * @test
     * @group value
     * @dataProvider nonStringProvider
     * @expectedException \Surfnet\Conext\EntityVerificationFramework\Exception\AssertionFailedException
     *
     * @param mixed $nonString
     */
    public function it_doesnt_accept_non_strings_as_email($nonString)
    {
        EmailAddress::fromString($nonString);
    }
    
    /**
     * @test
     * @group value
     */
    public function it_can_be_invalid()
    {
        /** @var ValidationContext|MockInterface $context */
        $context = m::mock(ValidationContext::class);

        /** @var Validator|MockInterface $validator */
        $validator = m::mock(Validator::class);
        $validator
            ->shouldReceive('addViolation')
            ->with('Contact e-mail address is not valid')
            ->once();

        $emailAddress = EmailAddress::fromString('invalid');
        $emailAddress->validate($validator, $context);
    }

    /**
     * @test
     * @group value
     */
    public function it_can_be_valid()
    {
        /** @var ValidationContext|MockInterface $context */
        $context = m::mock(ValidationContext::class);

        /** @var Validator|MockInterface $validator */
        $validator = m::mock(Validator::class);
        $validator->shouldReceive('addViolation')->never();

        $emailAddress = EmailAddress::fromString('valid@valid.invalid');
        $emailAddress->validate($validator, $context);
    }

    /**
     * @test
     * @group value
     */
    public function two_emails_can_equal_each_other()
    {
        $url0 = EmailAddress::fromString('renee.dupree@datrijmtook.invalid');
        $url1 = EmailAddress::fromString('renee.dupree@datrijmtook.invalid');

        $this->assertTrue($url0->equals($url1));
    }

    /**
     * @test
     * @group value
     */
    public function two_emails_can_differ()
    {
        $url0 = EmailAddress::fromString('renee.boulanger@vara.invalid');
        $url1 = EmailAddress::fromString('francois.boulanger@vara.invalid');

        $this->assertFalse($url0->equals($url1));
    }
}
