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

use Jira_Api as JiraApiClient;
use Surfnet\JiraApiClientBundle\ApiClient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\VarDumper\VarDumper;

final class JiraMyPermissionsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('operations-support:jira:my-permissions');
        $this->setDescription('Retrieve a listing of issue priorities so one can configure a status mapping');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var JiraApiClient $apiClient */
        $apiClient = $this->getContainer()->get('surfnet_jira_api_client.jira_client');

        VarDumper::dump($apiClient->api(ApiClient::REQUEST_GET, '/rest/api/2/mypermissions', [], true));
    }
}
