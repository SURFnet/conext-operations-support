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

namespace Surfnet\Conext\OperationsSupportBundle\Value;

use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\OperationsSupportBundle\Exception\LogicException;

final class JiraIssueStatus
{
    /**
     * @var JiraIssueStatus
     */
    private static $openStatus;

    /**
     * @var JiraIssueStatus
     */
    private static $mutedStatus;

    /**
     * @var string
     */
    private $statusId;

    /**
     * @param JiraIssueStatus $openStatus
     * @param JiraIssueStatus $mutedStatus
     */
    public static function configure(JiraIssueStatus $openStatus, JiraIssueStatus $mutedStatus)
    {
        if (self::$openStatus !== null) {
            throw new LogicException('JiraIssueStatus has already been set');
        }

        self::$openStatus = $openStatus;
        self::$mutedStatus = $mutedStatus;
    }

    /**
     * @return JiraIssueStatus
     */
    public static function open()
    {
        if (self::$openStatus === null) {
            throw new LogicException('Open status ID has not been set');
        }

        return self::$openStatus;
    }

    /**
     * @return JiraIssueStatus
     */
    public static function muted()
    {
        if (self::$mutedStatus === null) {
            throw new LogicException('Muted status ID has not been set');
        }

        return self::$mutedStatus;
    }

    /**
     * @param string $statusId
     */
    public function __construct($statusId)
    {
        Assert::regex($statusId, '~^\d+$~', 'JIRA issue status ID must consist of one or more digits');

        $this->statusId = $statusId;
    }

    /**
     * @return bool
     */
    public function isOpen()
    {
        if (self::$openStatus === null) {
            throw new LogicException('Open status ID has not been set');
        }

        return $this->equals(self::$openStatus);
    }

    /**
     * @return bool
     */
    public function isMuted()
    {
        if (self::$mutedStatus === null) {
            throw new LogicException('Muted status ID has not been set');
        }

        return $this->equals(self::$mutedStatus);
    }

    /**
     * @return string
     */
    public function getStatusId()
    {
        return $this->statusId;
    }

    /**
     * @param JiraIssueStatus $other
     * @return bool
     */
    public function equals(JiraIssueStatus $other)
    {
        return $this == $other;
    }
}
