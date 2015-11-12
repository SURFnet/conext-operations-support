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

namespace Surfnet\Conext\OperationsSupportBundle\Reporter;

use Surfnet\Conext\EntityVerificationFramework\Api\VerificationReporter;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuiteResult;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationTestResult;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;
use Surfnet\Conext\OperationsSupportBundle\Exception\LogicException;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

final class CliReporter implements VerificationReporter
{
    const REPORT = <<<REPORT
<comment>%8s</comment> <info>%s</info> failed for entity <info>%s</info>
    Reason: %s

%s

REPORT;

    /**
     * @var InputInterface
     */
    private $input;

    /**
     * @var OutputInterface
     */
    private $output;

    public function __construct(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
    }

    public function reportFailedVerificationFor(Entity $entity, VerificationSuiteResult $result)
    {
        if (!$result->hasTestFailed()) {
            throw new LogicException('Cannot report test that has not failed');
        }

        $severityName   = $this->getSeverityName($result->getSeverity());
        $failedTestName = $result->getFailedTestName();
        $reason         = $result->getReason();

        // Indent explanation
        $explanation    = preg_replace('~^~m', '    ', $result->getExplanation());

        $this->output->writeln(sprintf(self::REPORT, $severityName, $failedTestName, $entity, $reason, $explanation));

        if ($this->output instanceof Output && $this->output->isDebug()) {
            $questionHelper = new QuestionHelper();
            $questionHelper->ask($this->input, $this->output, new Question('Press RETURN to continue...'));
        }
    }

    /**
     * @param int $severity
     * @return string
     */
    private function getSeverityName($severity)
    {
        switch ($severity) {
            case VerificationTestResult::SEVERITY_CRITICAL:
                return 'CRITICAL';
            case VerificationTestResult::SEVERITY_HIGH:
                return 'HIGH';
            case VerificationTestResult::SEVERITY_MEDIUM:
                return 'MEDIUM';
            case VerificationTestResult::SEVERITY_LOW:
                return 'LOW';
            case VerificationTestResult::SEVERITY_TRIVIAL:
                return 'TRIVIAL';
            default:
                throw new LogicException(
                    sprintf('Cannot determine string representation of unknown severity %d', $severity)
                );
        }
    }
}
