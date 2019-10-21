<?php
namespace Swango\Aliyun\Sts\Request;
class AssumeRole extends \Swango\Aliyun\Sts\RpcAcsRequest {
    public function __construct() {
        parent::__construct('Sts', '2015-04-01', 'AssumeRole');
        $this->setProtocol('https');
    }
    public function setRoleArn(string $role_arn): self {
        $this->queryParameters['RoleArn'] = $role_arn;
        return $this;
    }
    public function setRoleSessionName(string $role_session_name): self {
        $this->queryParameters['RoleSessionName'] = $role_session_name;
        return $this;
    }
}