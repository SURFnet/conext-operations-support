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

namespace Surfnet\Conext\EntityVerificationFramework;

use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTestResult;

final class TestResult implements VerificationTestResult
{
    /**
     * Severity Constants
     */
    const SEVERITY_CRITICAL = 5;
    const SEVERITY_HIGH     = 4;
    const SEVERITY_MEDIUM   = 3;
    const SEVERITY_LOW      = 2;
    const SEVERITY_TRIVIAL  = 1;

    /**
     * Status constants
     */
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED  = 'failed';

    /**
     * @var string
     */
    private $reason;

    /**
     * @var string
     */
    private $explanation;

    /**
     * @var int
     */
    private $severity;

    /**
     * @var string
     */
    private $status;

    /**
     * @return TestResult
     */
    public static function success()
    {
        return new self(self::STATUS_SUCCESS);
    }

    /**
     * @param string $reason
     * @param string $explanation detailed explanation that explains what is wrong.
     * @param int $severity one of the TestResult::SEVERITY_* constants
     * @return TestResult
     */
    public static function failed($reason, $explanation, $severity)
    {
        Assert::string($reason, 'Reason must be a string');
        Assert::string($explanation, 'Explanation must be a string');
        Assert::integer($severity, 'Severity must be an integer');
        Assert::range($severity, self::SEVERITY_TRIVIAL, self::SEVERITY_CRITICAL, 'Invalid Severity');

        $result = new self(self::STATUS_FAILED);

        $result->reason      = $reason;
        $result->explanation = $explanation;
        $result->severity    = $severity;

        return $result;
    }

    /**
     * @param int
     */
    private function __construct($status)
    {
        Assert::inArray($status, [self::STATUS_SUCCESS, self::STATUS_FAILED], 'Invalid TestResult Status');

        $this->status = $status;
    }

    public function hasTestFailed()
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function getSeverity()
    {
        return $this->severity;
    }

    public function getReason()
    {
        return $this->reason;
    }

    public function getExplanation()
    {
        return $this->explanation;
    }
}
