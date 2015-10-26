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

namespace Surfnet\Conext\EntityVerificationFramework\Api;

interface VerificationTestResult
{
    /**
     * Severity Constants
     */
    const SEVERITY_CRITICAL = 5;
    const SEVERITY_HIGH     = 4;
    const SEVERITY_MEDIUM   = 3;
    const SEVERITY_LOW      = 2;
    const SEVERITY_TRIVIAL  = 1;

    const VALID_SEVERITIES = [
        VerificationTestResult::SEVERITY_CRITICAL,
        VerificationTestResult::SEVERITY_HIGH,
        VerificationTestResult::SEVERITY_MEDIUM,
        VerificationTestResult::SEVERITY_LOW,
        VerificationTestResult::SEVERITY_TRIVIAL,
    ];

    /**
     * @return bool
     */
    public function hasTestFailed();

    /**
     * @return int One of the VerificationTestResult::SEVERITY_* constants.
     */
    public function getSeverity();

    /**
     * @return string
     */
    public function getReason();

    /**
     * @return string
     */
    public function getExplanation();
}
