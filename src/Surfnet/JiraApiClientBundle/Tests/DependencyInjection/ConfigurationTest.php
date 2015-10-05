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

namespace Surfnet\JiraApiClientBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\JiraApiClientBundle\DependencyInjection\Configuration;
use Surfnet\JiraApiClientBundle\Tests\DataProvider\DataProvider;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;
    use DataProvider;

    private $validConfig = [
        "host" => "example-host.test",
        "username" => "test user",
        "password" => "test password",
        "project_key" => "TST_000",
        "default_assignee_name" => "default assignee",
    ];

    /**
     * @test
     * @group JiraApiClientBundle
     * @dataProvider notNonStringScalarProvider
     */
    public function host_cannot_be_other_than_string($value)
    {
        $config = $this->validConfig;
        $config["host"] = $value;

        $this->assertConfigurationIsInvalid([$config], "The JIRA API host URL should be a string");
    }

    /**
     * @test
     * @group JiraApiClientBundle
     * @dataProvider notNonStringScalarProvider
     */
    public function username_cannot_be_other_than_string($value)
    {
        $config = $this->validConfig;
        $config["username"] = $value;

        $this->assertConfigurationIsInvalid([$config], "The JIRA API username should be a string");
    }

    /**
     * @test
     * @group JiraApiClientBundle
     * @dataProvider notNonStringScalarProvider
     */
    public function password_cannot_be_other_than_string($value)
    {
        $config = $this->validConfig;
        $config["password"] = $value;

        $this->assertConfigurationIsInvalid([$config], "The JIRA API password should be a string");
    }

    /**
     * @test
     * @group JiraApiClientBundle
     * @dataProvider notNonStringScalarProvider
     */
    public function project_key_cannot_be_other_than_string($value)
    {
        $config = $this->validConfig;
        $config["project_key"] = $value;

        $this->assertConfigurationIsInvalid([$config], "The project key should be a string");
    }

    /**
     * @test
     * @group JiraApiClientBundle
     * @dataProvider notNonStringScalarProvider
     */
    public function default_assignee_name_cannot_be_other_than_string($value)
    {
        $config = $this->validConfig;
        $config["default_assignee_name"] = $value;

        $this->assertConfigurationIsInvalid([$config], "The default assignee name should be a string");
    }

    /**
     * Return the instance of ConfigurationInterface that should be used by the
     * Configuration-specific assertions in this test-case
     *
     * @return \Symfony\Component\Config\Definition\ConfigurationInterface
     */
    protected function getConfiguration()
    {
        return new Configuration();
    }
}
