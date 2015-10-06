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

use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuiteResult;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTestResult;

/**
 * @SuppressWarnings(PHPMD.UnusedPrivateField) Failed Test is not used - yet
 * @SuppressWarnings(PHPMD.UnusedPrivateField)
 */
final class SuiteResult implements VerificationSuiteResult
{
    /**
     * Status constants
     */
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED  = 'failed';

    /**
     * @var VerificationTestResult
     */
    private $testResult;

    /**
     * @var string
     */
    private $failedTestName;

    /**
     * @var string
     */
    private $status;

    public static function success()
    {
        return new self(self::STATUS_SUCCESS);
    }

    public static function failedTest(VerificationTestResult $verificationTestResult, $failedTestName)
    {
        $result = new self(self::STATUS_FAILED);

        $result->testResult = $verificationTestResult;
        $result->failedTestName = $failedTestName;

        return $result;
    }

    private function __construct($status)
    {
        $this->status = $status;
    }

    public function hasTestFailed()
    {
        return $this->testResult && $this->testResult->hasTestFailed();
    }

    public function getFailedTestName()
    {
        return $this->failedTestName;
    }

    public function getSeverity()
    {
        return $this->testResult->getSeverity();
    }

    public function getReason()
    {
        return $this->testResult->getReason();
    }

    public function getExplanation()
    {
        return $this->testResult->getExplanation();
    }
}
