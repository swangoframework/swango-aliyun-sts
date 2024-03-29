<?php
namespace Swango\Aliyun\Sts;
/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements. See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership. The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * 'License'); you may not use this file except in compliance
 * with the License. You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * 'AS IS' BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied. See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */
abstract class RpcAcsRequest extends AcsRequest {
    private $dateTimeFormat = 'Y-m-d\TH:i:s\Z';
    function __construct($product, $version, $actionName) {
        parent::__construct($product, $version, $actionName);
        $this->initialize();
    }
    private function initialize() {
        $this->setMethod('GET');
        $this->setAcceptFormat('JSON');
    }
    public function getFinalQuery(Auth\ISigner $iSigner, Auth\Credential $credential): array {
        $apiParams = parent::getQueryParameters();
        $apiParams['RegionId'] = $this->getRegionId();
        $apiParams['AccessKeyId'] = $credential->getAccessKeyId();
        $apiParams['Format'] = $this->getAcceptFormat();
        $apiParams['SignatureMethod'] = $iSigner->getSignatureMethod();
        $apiParams['SignatureVersion'] = $iSigner->getSignatureVersion();
        $apiParams['SignatureNonce'] = uniqid();
        // use GMT timezone
        $apiParams['Timestamp'] = date($this->dateTimeFormat, \Time\now() - 8 * 3600);
        $apiParams['Action'] = $this->getActionName();
        $apiParams['Version'] = $this->getVersion();
        $apiParams['Signature'] = $this->computeSignature($apiParams, $credential->getAccessSecret(), $iSigner);
        return $apiParams;
    }
    private function computeSignature(array $parameters, string $accessKeySecret, Auth\ISigner $iSigner): string {
        ksort($parameters);
        $canonicalizedQueryString = '';
        foreach ($parameters as $key=>$value) {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }
        $stringToSign = parent::getMethod() . '&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
        $signature = $iSigner->signString($stringToSign, $accessKeySecret . '&');

        return $signature;
    }
    protected function percentEncode(string $str): string {
        $res = preg_replace('/\+/', '%20', urlencode($str));
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }
}