<?php
/**
 * 服务测试接口
 * $Author$
 * $Id$
 */

class voa_server_test extends voa_server_forward {

    /**
     * __construct
     * 构造函数
     *
     * @return void
     */
    public function __construct($server_name) {

    	parent::__construct($server_name);
    }

    /**
     * 获取用户信息
     * @param array $args 参数
     *  + uid 用户uid
     *  + openid 用户openid
     */
    public function get($args) {

    	if ($args) {
    		$args[] = 'get succeed';
    		return $args;
    	}

    	throw new rpc_exception('args is empty.');
    }
}
