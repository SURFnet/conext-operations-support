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

namespace Surfnet\Conext\OperationsSupportBundle\Console\Command;

use Psr\Log\LoggerInterface;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationReporter;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationRunner;
use Surfnet\Conext\EntityVerificationFramework\SuiteWhitelist;
use Surfnet\Conext\OperationsSupportBundle\Exception\RuntimeException;
use Surfnet\Conext\OperationsSupportBundle\Reporter\CliReporter;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

final class RunSuitesCommand extends ContainerAwareCommand
{
    /**
     * @see http://www-numi.fnal.gov/offline_software/srt_public_context/WebDocs/Errors/unix_system_errors.html
     */
    const EXIT_CODE_INVALID_ARGUMENT = 22;

    protected function configure()
    {
        $this
            ->setName('operations-support:suites:run')
            ->setDescription('Run all configured suites and their tests, and report any issues')
            ->addOption(
                'reporter',
                null,
                InputOption::VALUE_OPTIONAL,
                'The reporter to report issues with (eg. jira)'
            )
            ->addOption(
                'suites',
                null,
                InputOption::VALUE_OPTIONAL,
                'A comma-separated list of suites that should run exclusively (by default all suites run)'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reporterName = $input->getOption('reporter');
        $suites       = $input->getOption('suites');

        try {
            $reporter  = $this->determineReporter($reporterName, $input, $output);
            $whitelist = $this->determineWhitelist($suites);
        } catch (RuntimeException $e) {
            return self::EXIT_CODE_INVALID_ARGUMENT;
        }

        /** @var VerificationRunner $runner */
        $runner = $this->getContainer()->get('surfnet_conext_operations_support.verification_runner');
        $runner->run($reporter, $whitelist);
    }

    /**
     * @param string|null     $reporterName
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return VerificationReporter
     */
    private function determineReporter($reporterName, InputInterface $input, OutputInterface $output)
    {
        if ($reporterName === null) {
            return new CliReporter($input, $output);
        }

        $reporterServiceId = 'surfnet_conext_operations_support.reporter.' . $reporterName;

        if (!$this->getContainer()->has($reporterServiceId)) {
            $output->writeln([
                '',
                sprintf('<error> No reporter called "%s" is registered </error>', $reporterName),
                '',
                sprintf('    I looked for a service named <info>%s</info>', $reporterServiceId),
                '',
            ]);

            throw new RuntimeException;
        }

        $this->getLogger()->debug(sprintf('Running with reporter: "%s"', $reporterServiceId));

        return $this->getContainer()->get($reporterServiceId);
    }

    /**
     * @param string|null $suites
     * @return SuiteWhitelist|null
     */
    private function determineWhitelist($suites)
    {
        if ($suites === null) {
            return null;
        }

        $suiteNames = explode(',', $suites);
        $this->getLogger()->debug(sprintf('Running with whitelisted suites: "%s"', $suites));

        return new SuiteWhitelist($suiteNames);
    }

    /**
     * @return LoggerInterface
     */
    private function getLogger()
    {
        return $this->getContainer()->get('logger');
    }
}
