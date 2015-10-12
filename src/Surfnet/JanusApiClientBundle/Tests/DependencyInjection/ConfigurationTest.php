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

namespace Surfnet\JanusApiClientBundle\Tests\DependencyInjection;

use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use PHPUnit_Framework_TestCase as TestCase;
use Surfnet\JanusApiClientBundle\DependencyInjection\Configuration;
use Surfnet\JanusApiClientBundle\Tests\DataProvider\DataProvider;

final class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;
    use DataProvider;

    private $validConfig = [
        "base_url" => "http://api.invalid/",
        "username" => "test user",
        "password" => "test password",
    ];

    /**
     * @test
     * @group JanusApiClientBundle
     * @dataProvider nonStringScalarProvider
     */
    public function base_url_cannot_be_other_than_string($value)
    {
        $config = $this->validConfig;
        $config["base_url"] = $value;

        $this->assertConfigurationIsInvalid([$config], "The Janus API base URL should be a string");
    }

    /**
     * @test
     * @group JanusApiClientBundle
     */
    public function base_url_has_to_be_valid_url()
    {
        $config = $this->validConfig;
        $config["base_url"] = "not a valid URL";

        $this->assertConfigurationIsInvalid([$config], "The Janus API base URL should be a valid URL");
    }

    /**
     * @test
     * @group JanusApiClientBundle
     */
    public function base_url_must_end_in_forward_slash()
    {
        $config = $this->validConfig;
        $config["base_url"] = "https://sr.invalid/janus/api";

        $this->assertConfigurationIsInvalid([$config], "The Janus API base URL must end in a forward slash");
    }

    /**
     * @test
     * @group JanusApiClientBundle
     * @dataProvider nonStringScalarProvider
     */
    public function username_cannot_be_other_than_string($value)
    {
        $config = $this->validConfig;
        $config["username"] = $value;

        $this->assertConfigurationIsInvalid([$config], "The Janus API username should be a string");
    }

    /**
     * @test
     * @group JanusApiClientBundle
     * @dataProvider nonStringScalarProvider
     */
    public function password_cannot_be_other_than_string($value)
    {
        $config = $this->validConfig;
        $config["password"] = $value;

        $this->assertConfigurationIsInvalid([$config], "The Janus API password should be a string");
    }

    protected function getConfiguration()
    {
        return new Configuration();
    }
}
