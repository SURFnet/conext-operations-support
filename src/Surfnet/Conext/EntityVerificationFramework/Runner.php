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

use Psr\Log\LoggerInterface;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationBlacklist;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationReporter;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationRunner;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuite;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuiteResult;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuiteWhitelist;
use Surfnet\Conext\EntityVerificationFramework\Exception\LogicException;
use Surfnet\Conext\EntityVerificationFramework\Repository\ConfiguredMetadataRepository;

class Runner implements VerificationRunner
{
    /**
     * @var VerificationSuite[]
     */
    private $verificationSuites = [];

    /**
     * @var ConfiguredMetadataRepository
     */
    private $configuredMetadataRepository;

    /**
     * @var VerificationBlacklist
     */
    private $blacklist;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    public function __construct(
        ConfiguredMetadataRepository $configuredMetadataRepository,
        VerificationBlacklist $blacklist,
        ContextFactory $contextFactory,
        LoggerInterface $logger
    ) {
        $this->configuredMetadataRepository = $configuredMetadataRepository;
        $this->blacklist                    = $blacklist;
        $this->contextFactory               = $contextFactory;
        $this->logger                       = $logger;
    }

    public function addVerificationSuite(VerificationSuite $verificationSuite)
    {
        $this->verificationSuites[] = $verificationSuite;
    }

    public function run(VerificationReporter $reporter, VerificationSuiteWhitelist $suiteWhitelist = null)
    {
        $suitesToRun = $this->determineSuitesToRun($suiteWhitelist);
        if (count($suitesToRun) === 0) {
            $this->logger->warning('No suites available to run, aborting run...');

            return;
        }
        $this->logger->info(sprintf('Running Entity Verification Framework with "%d" suites', count($suitesToRun)));

        $entities = $this->configuredMetadataRepository->getConfiguredEntities();
        if (count($entities) === 0) {
            $this->logger->warning('No Entities available to verify, aborting run...');

            return;
        }
        $this->logger->info(sprintf('Retrieved "%d" entities from Configured Metadata Repository', count($entities)));

        $reportCount = 0;
        foreach ($entities as $entity) {
            $this->logger->debug(sprintf('Verifying Entity "%s"', $entity));

            $context = $this->contextFactory->create($entity, $this->logger);

            foreach ($suitesToRun as $verificationSuite) {
                $suiteName = NameResolver::resolveToString($verificationSuite);

                if ($this->blacklist->isBlacklisted($entity, $suiteName)) {
                    $this->logger->info(sprintf(
                        'Suite "%s" blacklisted for entity "%s"',
                        $suiteName,
                        $entity
                    ));

                    continue;
                }

                if ($verificationSuite->shouldBeSkipped($context)) {
                    $this->logger->info(sprintf(
                        'Skipping suite "%s" for entity "%s", reason: "%s"',
                        $suiteName,
                        $entity,
                        $verificationSuite->getReasonToSkip()
                    ));

                    continue;
                }

                $this->logger->debug(sprintf('Running suite "%s" for Entity "%s"', $suiteName, $entity));

                $suiteResult = $verificationSuite->verify($context, $this->blacklist);
                if (!$suiteResult instanceof VerificationSuiteResult) {
                    throw new LogicException(sprintf(
                        'VerificationSuite "%s" did not return a VerificationSuiteResult',
                        $suiteName
                    ));
                }

                if ($suiteResult->hasTestFailed()) {
                    $this->logger->debug(sprintf('Verification suite "%s" failed for "%s', $suiteName, $entity));

                    $reporter->reportFailedVerificationFor($entity, $suiteResult);
                    $reportCount++;
                    continue;
                }

                $this->logger->debug(sprintf('Verification suite "%s" ran successfully for "%s"', $suiteName, $entity));
            }

            $this->logger->debug(sprintf('Verification of Entity "%s" has been completed', $entity));
        }

        $this->logger->info(sprintf(
            'Completed Run of Entity Verification Framework, ' .
            'verification of "%d" Entities with "%d" suites resulted in "%d" reports',
            count($entities),
            count($this->verificationSuites),
            $reportCount
        ));
    }

    private function determineSuitesToRun(VerificationSuiteWhitelist $suiteWhitelist = null)
    {
        $this->logger->debug(sprintf('There are "%d" configured suites', count($this->verificationSuites)));

        if (count($this->verificationSuites) === 0) {
            return $this->verificationSuites;
        }

        if (!$suiteWhitelist) {
            $this->logger->debug('No suite whitelist given, running all suites');

            return $this->verificationSuites;
        }

        $suitesIndexedByName = array_combine(
            array_map(
                ['Surfnet\Conext\EntityVerificationFramework\NameResolver', 'resolveToString'],
                $this->verificationSuites
            ),
            $this->verificationSuites
        );

        $suitesToRun = array_filter($suitesIndexedByName, [$suiteWhitelist, 'contains'], ARRAY_FILTER_USE_KEY);

        $this->logger->debug(sprintf('"%d" of the configured suites are whitelisted', count($suitesToRun)));

        return $suitesToRun;
    }
}
