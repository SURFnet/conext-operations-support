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

use Surfnet\Conext\EntityVerificationFramework\Api\VerificationReporter;
use Surfnet\Conext\EntityVerificationFramework\Api\VerificationRunner;
use Surfnet\Conext\EntityVerificationFramework\SuiteWhitelist\SuiteWhitelist;
use Surfnet\Conext\OperationsSupportBundle\Exception\RuntimeException;
use Surfnet\Conext\OperationsSupportBundle\Reporter\CliReporter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class RunSuitesCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct();

        $this->container = $container;
    }

    protected function configure()
    {
        $this->setName('operations-support:suites:run');
        $this->setDescription('Run all configured suites and their tests, and report any issues');

        $this->addOption(
            'reporter',
            null,
            InputOption::VALUE_OPTIONAL,
            'The reporter to report issues with (eg. jira)'
        );
        $this->addOption(
            'suites',
            null,
            InputOption::VALUE_OPTIONAL,
            'A comma-separated list of suites that should run exclusively (default: all suites are run)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reporterName = $input->getOption('reporter');
        $reporter = $this->determineReporter($reporterName, $output);

        $suites = $input->getOption('suites');
        $whitelist = $this->determineWhitelist($suites);

        /** @var VerificationRunner $runner */
        $runner = $this->container->get('surfnet_conext_operations_support.verification_runner');
        $runner->run($reporter, $whitelist);
    }

    /**
     * @param string|null $reporterName
     * @param OutputInterface $output
     * @return VerificationReporter
     */
    private function determineReporter($reporterName, OutputInterface $output)
    {
        if ($reporterName === null) {
            return new CliReporter($output);
        }

        $reporterServiceId = 'surfnet_conext_operations_support.reporter.' . $reporterName;

        if (!$this->container->has($reporterServiceId)) {
            $output->writeln([
                '',
                sprintf('<error> No reporter called "%s" is registered </error>', $reporterName),
                '',
                sprintf('    I looked for a service named <info>%s</info>', $reporterServiceId),
                '',
            ]);

            throw new RuntimeException;
        }

        /** @var VerificationReporter $reporter */
        return $this->container->get($reporterServiceId);
    }

    /**
     * @param string|null $suites
     * @return SuiteWhitelist|void
     */
    private function determineWhitelist($suites)
    {
        if ($suites === null) {
            return;
        }

        $suiteNames = explode(',', $suites);
        $this->container->get('logger')->debug(sprintf('Whitelisted: %s', implode(', ', $suiteNames)));

        return new SuiteWhitelist($suiteNames);
    }
}
