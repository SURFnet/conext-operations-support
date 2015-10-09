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

use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuiteWhitelist;
use Surfnet\Conext\EntityVerificationFramework\Assert;

class SuiteWhitelist implements VerificationSuiteWhitelist
{
    /**
     * @var string[]
     */
    private $suiteNames;

    /**
     * @param array $suiteNames
     */
    public function __construct(array $suiteNames)
    {
        Assert::allString($suiteNames);
        $this->suiteNames = $suiteNames;
    }

    /**
     * @param string $suite
     * @return bool
     */
    public function contains($suite)
    {
        return in_array($suite, $this->suiteNames);
    }
}
