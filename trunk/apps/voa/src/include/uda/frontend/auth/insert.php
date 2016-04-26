<?php
/**
 * voa_uda_frontend_auth_insert
 * 统一数据访问/PCauth登录/入库
 * Created by zhoutao.
 * Created Time: 2015/7/3  17:45
 */

class voa_uda_frontend_auth_insert extends voa_uda_frontend_auth_base {

	/**
	 * authcode 入库
	 * @param $data
	 * @param $out
	 * @return bool
	 */
	public function insert_authcode ($data, $out) {
		if (!empty($data)) {
			$this->auth_insert->insert(
				array(
					'authcode' => (string)$data['authcode'],
					'ip' => (string)$data['ip']
				)
			);
		}
		return true;
	}


}

