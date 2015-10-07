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
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationReporter;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationRunner;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuite;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationSuiteResult;
use Surfnet\Conext\EntityVerificationFramework\Exception\LogicException;
use Surfnet\Conext\EntityVerificationFramework\Repository\PublishedMetadataRepository;
use Surfnet\Conext\EntityVerificationFramework\Repository\ConfiguredMetadataRepository;
use Surfnet\Conext\EntityVerificationFramework\Value\Entity;

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
     * @var PublishedMetadataRepository
     */
    private $publishedMetadataRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ConfiguredMetadataRepository $configuredMetadataRepository,
        PublishedMetadataRepository $publishedMetadataRepository,
        LoggerInterface $logger
    ) {
        $this->configuredMetadataRepository = $configuredMetadataRepository;
        $this->publishedMetadataRepository  = $publishedMetadataRepository;
        $this->logger                       = $logger;
    }

    public function addVerificationSuite(VerificationSuite $verificationSuite)
    {
        $this->verificationSuites[] = $verificationSuite;
    }

    public function run(VerificationReporter $reporter)
    {
        $this->logger->debug(
            sprintf(
                'Running Entity Verification Framework with "%d" suites',
                count($this->verificationSuites)
            )
        );

        $entities = $this->configuredMetadataRepository->getConfiguredEntities();
        $this->logger->debug(sprintf('Retrieved %d configured entities from configured', count($entities)));

        $getRemoteMetadata = function (Entity $entity) {
            return $this->publishedMetadataRepository->getMetadataFor($entity);
        };

        foreach ($entities as $entity) {
            $this->logger->debug(sprintf('Verifying Entity "%s"', $entity));

            $context = new Context(
                $entity,
                $this->configuredMetadataRepository->getMetadataFor($entity),
                $getRemoteMetadata,
                $this->logger
            );

            foreach ($this->verificationSuites as $verificationSuite) {
                $suiteName = NameResolver::resolveToString($verificationSuite);

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

                $suiteResult = $verificationSuite->verify($context);
                if (!$suiteResult instanceof VerificationSuiteResult) {
                    throw new LogicException(sprintf(
                        'VerificationSuite "%s" did not return a VerificationSuiteResult',
                        $suiteName
                    ));
                }

                if ($suiteResult->hasTestFailed()) {
                    $this->logger->debug(sprintf('Verification suite "%s" failed for "%s', $suiteName, $entity));

                    $reporter->reportFailedVerificationFor($entity, $suiteResult);
                    continue;
                }

                $this->logger->debug(sprintf('Verification suite "%s" ran successfully for "%s"', $suiteName, $entity));
            }

            $this->logger->debug(sprintf('Verification of Entity "%s" has been completed', $entity));
        }

        $this->logger->debug(sprintf(
            'Completed Run of Entity Verification Framework, "%d" Entities Verified with "%d" suites.',
            count($entities),
            count($this->verificationSuites)
        ));
    }
}
