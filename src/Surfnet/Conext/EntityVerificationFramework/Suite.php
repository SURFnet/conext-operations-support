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

use Surfnet\Conext\EntityVerificationFramework\Api\VerificationContext;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuite;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTest;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTestResult;
use Surfnet\Conext\EntityVerificationFramework\Exception\LogicException;

abstract class Suite implements VerificationSuite
{
    /**
     * @var VerificationTest[]
     */
    private $verificationTests = [];

    public function addVerificationTest(VerificationTest $verificationTest)
    {
        $this->verificationTests[] = $verificationTest;
    }

    public function verify(VerificationContext $verificationContext)
    {
        $logger = $verificationContext->getLogger();
        $entity = $verificationContext->getEntity();

        foreach ($this->verificationTests as $test) {
            $testName = NameResolver::resolveToString($test);

            if ($test->shouldBeSkipped($verificationContext)) {
                $logger->info(sprintf(
                    'Skipping test "%s" for entity "%s", reason: "%s"',
                    $testName,
                    $entity,
                    $test->getReasonToSkip()
                ));

                continue;
            }

            $logger->debug(sprintf('Running Test "%s" for Entity "%s"', $testName, $entity));

            $testResult = $test->verify($verificationContext);

            if (!$testResult instanceof VerificationTestResult) {
                throw new LogicException(sprintf(
                    'VerificationTest "%s" did not return a VerificationTestResult',
                    $testName
                ));
            }

            if ($testResult->hasTestFailed()) {
                $logger->debug('Test "%s" failed for Entity "%s" ("%s")');

                return SuiteResult::failedTest($testResult, $test);
            }

            $logger->debug(sprintf('Test "%s" successful for Entity "%s"', $testName, $entity));
        }

        return SuiteResult::success();
    }
}
