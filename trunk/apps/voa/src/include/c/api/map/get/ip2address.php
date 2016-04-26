<?php
/**
 * ip2address.php
 * 通过 IP 获取地址信息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_map_get_ip2address extends voa_c_api_map_base {

	public function execute() {

		// 需要传入的参数
		$fields = array(
			// IP 地址
			'ip' => array('type' => 'string_trim', 'required' => true),
		);

		// 参数数值基本检查验证
		$this->_check_params($fields);

		// 加载ip转换地址类
		$ip2address = new ip2address();
		if (!$ip2address->get($this->_params['ip'])) {
			return $this->_set_errcode($ip2address->errcode.':'.$ip2address->errmsg);
		}

		// 返回结果
		$this->_result = array(
			'ip' => $this->_params['ip'],
			'address' => $ip2address->result['address']
		);

		return true;
	}

}
