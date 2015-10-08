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

namespace Surfnet\Conext\EntityVerificationFramework\SuiteWhitelist;

use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuite;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuiteWhitelist;
use Surfnet\Conext\EntityVerificationFramework\NameResolver;
use Surfnet\Conext\EntityVerificationFramework\SuiteWhitelist;

class Whitelist implements VerificationSuiteWhitelist
{
    private $suiteNames;

    public function __construct(array $suiteNames)
    {
        $this->suiteNames = $suiteNames;
    }

    public function contains(VerificationSuite $suite)
    {
        return in_array(NameResolver::resolveToString($suite), $this->suiteNames);
    }
}
