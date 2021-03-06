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

namespace Surfnet\VerificationSuite\NameResolverTestSuite\Test;

use Surfnet\Conext\EntityVerificationFramework\Api\VerificationContext;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTest;

class SomeTest implements VerificationTest
{
    public function verify(VerificationContext $verificationContext) {}
    public function shouldBeSkipped(VerificationContext $verificationContext) {}
    public function getReasonToSkip() {}
}
