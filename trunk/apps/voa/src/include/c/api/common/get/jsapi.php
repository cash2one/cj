<?php
/**
 * jsapi.php
 * 获取jsapi签名相关
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_common_get_jsapi extends voa_c_api_common_abstract {

	public function execute() {

		// 需要传入的参数
		$fields = array(
			// 使用jsapi的页面url
			'url' => array('type' => 'string_trim', 'required' => true),
			// 调用js的应用pluginid 来自common_plugin表cp_pluginid字段
			'pluginid' => array('type' => 'int', 'required' => false),
			'addr_signature' => array('type' => 'int', 'required' => false)
		);

		// 参数数值基本检查验证
		$this->_check_params($fields);

		// 当前api应用id与请求的应用id不同，则以请求的应用id为准
		if ($this->_params['pluginid'] && $this->_params['pluginid'] != startup_env::get('pluginid')) {
			startup_env::set('pluginid', $this->_params['pluginid']);
		}

		$url = $this->_params['url'] ? $this->_params['url'] : null;

		// 获取jsapi签名相关数据
		$wxqy_service = new voa_wxqy_service();
		$data = $wxqy_service->jsapi_signature($url);

		// 需要获取位置接口的签名
		$addr_signature = array();
		if ($this->_params['addr_signature']) {
			$ls_source = $wxqy_service->jsapi_addr_signature($url);
			$addr_signature = array(
				'timestamp' => (int)$ls_source['timestamp'],
				'nonce_str' => (string)$ls_source['nonce_str'],
				'signature' => (string)$ls_source['signature'],
				'corpid' => (string)$ls_source['corpid'],
				'url' => (string)$ls_source['url']
			);
		}

		// 输出结果
		$this->_result = array(
			'timestamp' => (int)$data['timestamp'],// 签名的时间戳
			'nonce_str' => (string)$data['nonce_str'],// 签名的随机字符串
			'signature' => (string)$data['signature'],// 签名字符串
			'corpid' => (string)$data['corpid'],// 企业号corpid
			'url' => (string)$data['url'],// 调用jsapi的url
			'addr_signature' => $addr_signature// 调用位置接口用的签名
		);

		return true;
	}

}

