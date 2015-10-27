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

namespace Surfnet\JiraApiClientBundle\Result;

use Surfnet\JiraApiClientBundle\Assert;
use Surfnet\JiraApiClientBundle\Exception\LogicException;

final class CreateIssueResult
{
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_CLIENT_ERROR = 'CLIENT_ERROR';

    /**
     * @var string
     */
    private $issueKey;

    /**
     * @var string[]
     */
    private $errorMessages;

    /**
     * @param string $issueKey
     * @return CreateIssueResult
     */
    public static function success($issueKey)
    {
        Assert::string($issueKey, 'Issue key must be a string');
        Assert::notBlank($issueKey, 'Issue key may not be blank');

        $result = new CreateIssueResult(self::STATUS_SUCCESS);
        $result->issueKey = $issueKey;

        return $result;
    }

    /**
     * @param string[] $errorMessages
     * @return CreateIssueResult
     */
    public static function clientError(array $errorMessages)
    {
        Assert::allString($errorMessages, 'Client error messages must be strings');

        $result = new CreateIssueResult(self::STATUS_CLIENT_ERROR);
        $result->errorMessages = $errorMessages;

        return $result;
    }

    /**
     * @param string $status
     */
    private function __construct($status)
    {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function wasSuccessful()
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * @return bool
     */
    public function wasClientErrorReported()
    {
        return $this->status === self::STATUS_CLIENT_ERROR;
    }

    /**
     * @return string
     */
    public function getIssueKey()
    {
        if (!$this->wasSuccessful()) {
            throw new LogicException('Issue key is not available when issue creation not successful');
        }

        return $this->issueKey;
    }

    /**
     * @return string[]
     */
    public function getErrorMessages()
    {
        return $this->errorMessages;
    }
}
