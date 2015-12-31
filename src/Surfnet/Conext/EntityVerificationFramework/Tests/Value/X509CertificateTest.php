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

use DateInterval;
use DateTimeImmutable;
use Mockery as m;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\Conext\EntityVerificationFramework\Tests\DateTimeHelper;
use Surfnet\Conext\EntityVerificationFramework\Value\X509Certificate;

class X509CertificateTest extends TestCase
{
    protected function tearDown()
    {
        DateTimeHelper::stopMockingNow();
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Value
     */
    public function certificates_can_be_checked_to_be_valid_for_a_specified_interval()
    {
        $certificate = new X509Certificate('aed5', new DateTimeImmutable('2017-01-01 00:00:00'));

        DateTimeHelper::mockNow(new DateTimeImmutable('2016-01-01 00:00:00'));
        $this->assertTrue($certificate->isStillValidFor(new DateInterval('P364D')));
        $this->assertFalse($certificate->isStillValidFor(new DateInterval('P1Y')));
        $this->assertFalse($certificate->isStillValidFor(new DateInterval('P1Y1D')));
    }

    /**
     * @test
     * @group EntityVerificationFramework
     * @group Value
     */
    public function certificates_can_be_checked_to_be_no_longer_valid()
    {
        $certificate = new X509Certificate('aed5', new DateTimeImmutable('2015-01-01 00:00:00'));

        DateTimeHelper::mockNow(new DateTimeImmutable('2016-01-01 00:00:00'));
        $this->assertFalse($certificate->isStillValid());
    }
}
