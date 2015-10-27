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

use Jira_Api_Result as ApiResult;
use Surfnet\JiraApiClientBundle\ApiClient;
use Surfnet\JiraApiClientBundle\Assert;
use Surfnet\JiraApiClientBundle\Command\CommentOnIssueCommand;
use Surfnet\JiraApiClientBundle\Command\CreateIssueCommand;
use Surfnet\JiraApiClientBundle\Command\ReprioritiseIssueCommand;
use Surfnet\JiraApiClientBundle\Dto\Comment;
use Surfnet\JiraApiClientBundle\Dto\Issue;
use Surfnet\JiraApiClientBundle\Exception\RuntimeException;

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
            throw new RuntimeException('Unknown error while creating JIRA issue: API result object false');
        }

        $resource = $result->getResult();

        Assert::keyExists(
            $resource,
            'key',
            'API responded in an unexpected manner: returned issue structure misses "key" key'
        );

        return $resource['key'];
    }

    public function reprioritiseIssue(ReprioritiseIssueCommand $command)
    {
        /** @var ApiResult|false $result */
        $result = $this->apiClient->editIssue($command->issueKey, [
            'update' => [
                'priority' => [
                    ['set' => $command->priorityId]
                ]
            ]
        ]);

        if (!$result) {
            throw new RuntimeException('Unknown error while reprioritising JIRA issue: API result object false');
        }
    }

    public function commentOnIssue(CommentOnIssueCommand $command)
    {
        /** @var ApiResult|false $result */
        $result = $this->apiClient->addComment($command->issueKey, ['body' => $command->body]);

        if (!$result) {
            throw new RuntimeException('Unknown error while adding comment to JIRA issue: API result object false');
        }

        $resource = $result->getResult();

        Assert::keyExists(
            $resource,
            'id',
            'API responded in an unexpected manner: returned comment structure misses "id" key'
        );

        return $resource['id'];
    }

    public function getIssue($issueKey)
    {
        /** @var ApiResult|false $result */
        $result = $this->apiClient->getIssue($issueKey);

        if (!$result) {
            throw new RuntimeException('Unknown error while fetching JIRA issue: API result object false');
        }

        $resource = $result->getResult();

        Assert::keyExists($resource, 'fields', 'Issue resource must contain "fields"');
        Assert::keyExists($resource['fields'], 'priority', 'Issue resource must contain "fields.priority"');
        Assert::keyExists($resource['fields'], 'priority', 'Issue resource must contain "fields.priority"');
        Assert::keyExists($resource['fields']['priority'], 'id', 'Issue resource must contain "fields.priority.id"');
        Assert::keyExists($resource['fields']['status'], 'id', 'Issue resource must contain "fields.status.id"');
        Assert::keyExists($resource['fields'], 'summary', 'Issue resource must contain "fields.summary"');
        Assert::keyExists($resource['fields'], 'description', 'Issue resource must contain "fields.description"');
        Assert::string($resource['fields']['priority']['id'], 'Issue resource "fields.priority.id" must be a string, got "%s"');
        Assert::string($resource['fields']['status']['id'], 'Issue resource "fields.status.id" must be a string, got "%s"');
        Assert::string($resource['fields']['summary'], 'Issue resource "fields.summary" must be a string, got "%s"');
        Assert::nullOrString($resource['fields']['description'], 'Issue resource "fields.description" must be a string or NULL, got "%s"');
        Assert::notBlank($resource['fields']['priority']['id'], 'Issue resource "fields.priority.id" may not be blank');
        Assert::notBlank($resource['fields']['status']['id'], 'Issue resource "fields.status.id" may not be blank');

        $issue = new Issue();
        $issue->priorityId = $resource['fields']['priority']['id'];
        $issue->statusId = $resource['fields']['status']['id'];
        $issue->summary = $resource['fields']['summary'];
        $issue->description = $resource['fields']['description'];

        return $issue;
    }

    public function getComment($issueKey, $commentId)
    {
        $result = $this->apiClient->getComment($issueKey, $commentId);

        if (!$result) {
            throw new RuntimeException('Unknown error while fetching JIRA issue comment: API result object false');
        }

        $resource = $result->getResult();

        Assert::keyExists($resource, 'body', 'Comment resource must contain "body"');
        Assert::string($resource['body'], 'Comment resource "body" must be a string');

        $comment = new Comment();
        $comment->body = $resource['body'];

        return $comment;
    }
}
