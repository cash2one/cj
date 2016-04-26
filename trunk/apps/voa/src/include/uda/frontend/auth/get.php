<?php
/**
 * voa_uda_frontend_auth_get
 * auth认证  获取
 * Created by zhoutao.
 * Created Time: 2015/7/5  9:45
 */

class voa_uda_frontend_auth_get extends voa_uda_frontend_auth_base {

	/**
	 * 查询auth登录状态
	 * @param $data
	 * @param $out
	 * @return bool
	 */
	public function get_state ($data, &$out) {

		$get = $this->auth_insert->get_by_conds(
			array(
				'authcode' => $data['authcode']
			)
		);

		if (!empty($get) && is_array($get)) {
			$out = array(
				'state' => $get['state'],
				'errmsg' => $get['errmsg']
			);
		}

		return true;
	}

	/**
	 * 根据authcode获取m_uid
	 * @param $authcode
	 * @param $m_uid
	 * @return bool
	 */
	public function get_m_uid ($authcode, &$m_uid) {

		$get = $this->auth_insert->get_by_conds(
			array(
				'authcode' => $authcode
			)
		);

		if (!empty($get) && is_array($get)) {
			$m_uid = $get['m_uid'];
		}
		return true;
	}
}
