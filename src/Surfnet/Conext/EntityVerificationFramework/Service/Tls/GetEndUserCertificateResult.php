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

namespace Surfnet\Conext\EntityVerificationFramework\Service\Tls;

use Surfnet\Conext\EntityVerificationFramework\Assert;
use Surfnet\Conext\EntityVerificationFramework\Exception\LogicException;
use Surfnet\Conext\EntityVerificationFramework\Value\X509Certificate;

final class GetEndUserCertificateResult
{
    const STATUS_SUCCESS = 'SUCCESS';
    const STATUS_CONNECTION_FAILED = 'CONNECTION_FAILED';
    const STATUS_CERTIFICATE_EXTRACTION_FAILED = 'CERTIFICATE_EXTRACTION_FAILED';
    const STATUS_CERTIFICATE_PARSING_FAILED = 'CERTIFICATE_PARSING_FAILED';

    /**
     * @var string
     */
    private $status;

    /**
     * @var X509Certificate
     */
    private $certificate;

    /**
     * @var string
     */
    private $errorMessage;

    /**
     * @param X509Certificate $certificate
     * @return GetEndUserCertificateResult
     */
    public static function success(X509Certificate $certificate)
    {
        $result = new GetEndUserCertificateResult(self::STATUS_SUCCESS);
        $result->certificate = $certificate;

        return $result;
    }

    /**
     * @param string $errorMessage
     * @return GetEndUserCertificateResult
     */
    public static function connectionFailed($errorMessage)
    {
        Assert::string($errorMessage, 'Error message must be a string');

        $result = new GetEndUserCertificateResult(self::STATUS_CONNECTION_FAILED);
        $result->errorMessage = $errorMessage;

        return $result;
    }

    /**
     * @param string $errorMessage
     * @return GetEndUserCertificateResult
     */
    public static function certificateExtractionFailed($errorMessage)
    {
        Assert::string($errorMessage, 'Error message must be a string');

        $result = new GetEndUserCertificateResult(self::STATUS_CERTIFICATE_EXTRACTION_FAILED);
        $result->errorMessage = $errorMessage;

        return $result;
    }

    /**
     * @param string $errorMessage
     * @return GetEndUserCertificateResult
     */
    public static function certificateParsingFailed($errorMessage)
    {
        Assert::string($errorMessage, 'Error message must be a string');

        $result = new GetEndUserCertificateResult(self::STATUS_CERTIFICATE_PARSING_FAILED);
        $result->errorMessage = $errorMessage;

        return $result;
    }

    /**
     * @param string $status
     */
    final private function __construct($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function wasSuccessful()
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    /**
     * @return X509Certificate
     */
    public function getCertificate()
    {
        if (!$this->wasSuccessful()) {
            throw new LogicException('X509 certificate is not available');
        }

        return $this->certificate;
    }

    /**
     * @return bool
     */
    public function didConnectionFail()
    {
        return $this->status === self::STATUS_CONNECTION_FAILED;
    }

    /**
     * @return bool
     */
    public function didCertificateExtractionFail()
    {
        return $this->status === self::STATUS_CERTIFICATE_EXTRACTION_FAILED;
    }

    /**
     * @return bool
     */
    public function didCertificateParsingFail()
    {
        return $this->status === self::STATUS_CERTIFICATE_PARSING_FAILED;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }
}
