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
class Client extends \BaseClient {
    protected const SCHEME = 'https';
    public $iClientProfile;
    public function __construct(Profile $iClientProfile) {
        $this->iClientProfile = $iClientProfile;
    }
    public function getAcsResponse(\Swango\Aliyun\Sts\RpcAcsRequest $request, ?\Swango\Aliyun\Sts\Auth\ISigner $iSigner = null,
        ?\Swango\Aliyun\Sts\Auth\Credential $credential = null) {
        $httpResponse = $this->doAction($request, $iSigner, $credential);
        return $this->parseAcsResponse($httpResponse->getBody(), $request->getAcceptFormat());
    }
    public function doAction(\Swango\Aliyun\Sts\RpcAcsRequest $request, ?\Swango\Aliyun\Sts\Auth\ISigner $iSigner = null,
        ?\Swango\Aliyun\Sts\Auth\Credential $credential = null) {
        if (null == $this->iClientProfile && (null == $iSigner || null == $credential || null == $request->getRegionId() ||
             null == $request->getAcceptFormat())) {
            throw new Exception\ClientException('No active profile found.', 'SDK.InvalidProfile');
        }
        if (null == $iSigner) {
            $iSigner = $this->iClientProfile->getSigner();
        }
        if (null == $credential) {
            $credential = $this->iClientProfile->getCredential();
        }
        $request = $this->prepareRequest($request);
        $domain = Regions\EndpointProvider::findProductDomain($request->getRegionId(), $request->getProduct());
        if (null == $domain) {
            throw new Exception\ClientException('Can not find endpoint to access.', 'SDK.InvalidRegionId');
        }

        $this->makeClient();
        $this->client->withMethod($request->getMethod());
        $this->client->getUri()->withHost($domain);
        $this->client->getUri()->withPath('/');
        $this->client->getUri()->withQuery($request->getFinalQuery($iSigner, $credential));

        $this->client->withHeaders($request->getHeaders() + [
            'Host' => $domain
        ]);
        /**
         *
         * @var \Swlib\Saber\Response $response
         */
        $response = $this->sendHttpRequest()->recv();
        return $this->parseAcsResponse($response->getBody()->__toString(), $request->getAcceptFormat());
    }
    private function prepareRequest(\Swango\Aliyun\Sts\RpcAcsRequest $request) {
        if (null == $request->getRegionId()) {
            $request->setRegionId($this->iClientProfile->getRegionId());
        }
        if (null == $request->getAcceptFormat()) {
            $request->setAcceptFormat($this->iClientProfile->getFormat());
        }
        if (null == $request->getMethod()) {
            $request->setMethod('GET');
        }
        return $request;
    }
    private function parseAcsResponse(string $body, string $format) {
        if ('JSON' == $format) {
            return \Json::decodeAsObject($body);
        } elseif ('XML' == $format) {
            return self::xmlDecode($body);
        } elseif ('RAW' == $format) {
            return $body;
        }
        return null;
    }
    private static function xmlIntoObject(\SimpleXMLElement $data, array &$need_array, string $key) {
        $s = $data->__toString();
        if (str_replace([
            ' ',
            "\n",
            "\r",
            "\t"
        ], '', $s) != '')
            return $s;
        $result = new \stdClass();
        $flag = 0; // 0表示没有儿子 1表示有多个同键名儿子 2表示有儿子
        $map = [];
        foreach ($data as $k=>$v) {
            // 有两项相同健值，说明这里需要转换为数组
            if ($flag !== 1 && array_key_exists($k, $map)) {
                $flag = 1;
                $ob = $result->{$k};
                $result = new \stdClass();
                $result->{$k} = [
                    $ob
                ];
            }
            if ($flag === 0)
                $flag = 2;
            $map[$k] = null;
            if ($flag === 1)
                $result->{$k}[] = self::xmlIntoObject($v, $need_array, "$key.$k");
            else
                $result->{$k} = self::xmlIntoObject($v, $need_array, "$key.$k");
        }
        // 一个儿子都没有，说明本项为空
        if ($flag === 0)
            return null;
        unset($v);
        if ($flag === 2 && in_array($key, $need_array))
            return [
                $result
            ];

        return $result;
    }
    private static function xmlDecode(string &$data, array $need_array = []): \stdClass {
        $xml = simplexml_load_string($data);
        if ($xml === false)
            throw new \Exception('Aliyun xml decode fail ' . $data);
        $name = $xml->getName();
        $ret = new \stdClass();
        $ret->{$name} = self::xmlIntoObject($xml, $need_array, $name);
        return $ret;
    }
}