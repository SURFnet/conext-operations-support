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

namespace Surfnet\JiraApiClientBundle\Service;

use Jira_Api as ApiClient;
use Jira_Api_Result as ApiResult;
use Surfnet\JiraApiClientBundle\Assert;
use Surfnet\JiraApiClientBundle\Command\CreateIssueCommand;
use Surfnet\JiraApiClientBundle\Command\UpdateIssueCommand;
use Surfnet\JiraApiClientBundle\Exception\LogicException;
use Surfnet\JiraApiClientBundle\Exception\RuntimeException;
use Surfnet\JiraApiClientBundle\Result\CreateIssueResult;

final class RestIssueService implements IssueService
{
    /**
     * @var ApiClient
     */
    private $apiClient;

    /**
     * @var string
     */
    private $projectKey;

    /**
     * @var string
     */
    private $issueTypeId;

    /**
     * @param ApiClient $apiClient
     * @param string    $projectKey
     * @param string    $issueTypeId
     */
    public function __construct(ApiClient $apiClient, $projectKey, $issueTypeId)
    {
        Assert::string($projectKey, 'Project key name must be a string');
        Assert::notBlank($projectKey, 'Project key name may not be blank');
        Assert::regex($issueTypeId, '~^\d+$~', 'Issue type ID must be a string of digits');

        $this->apiClient = $apiClient;
        $this->projectKey = $projectKey;
        $this->issueTypeId = $issueTypeId;
    }

    public function createIssue(CreateIssueCommand $command)
    {
        /** @var ApiResult|false $result */
        $result = $this->apiClient->createIssue($this->projectKey, $command->summary, $this->issueTypeId, [
            'priority' => ['id' => $command->priorityId],
        ]);

        if (!$result) {
            throw new RuntimeException('Unknown error while creating JIRA issue');
        }

        $resource = $result->getResult();

        if (!is_array($resource)) {
            throw new RuntimeException(
                'API responded in an unexpected manner: returned issue resource is not an issue structure'
            );
        }

        if (!array_key_exists('key', $resource)) {
            throw new RuntimeException(
                'API responded in an unexpected manner: returned issue structure misses "key" key'
            );
        }

        return CreateIssueResult::success($resource['key']);
    }

    public function updateIssue(UpdateIssueCommand $command)
    {
        throw new LogicException('Issue updating is not yet implemented');
    }
}
