<?php
/**
 * voa_uda_frontend_auth_update
 *
 * Created by zhoutao.
 * Created Time: 2015/7/5  9:10
 */

class voa_uda_frontend_auth_update extends voa_uda_frontend_auth_base {

	/**
	 * 更新auth认证状态
	 * @param $data
	 * @param $out
	 * @return bool
	 */
	public function check_update ($data, $out) {

		if (!empty($data) && is_array($data)) {

			$state = $this->auth_insert->get_by_conds(
				array(
					'authcode' => $data['authcode']
				)
			);
			if ($state['state'] == 2) {
				return true;
			}
			$this->auth_insert->update_by_conds(
				array(
					'authcode' => $data['authcode']
				),
				array(
					'm_uid' => $data['m_uid'],
					'state' => $data['state']
				)
			);

		}

		return true;
	}

	/**
	 * 更新登录状态
	 * @param $data
	 * @param $out
	 * @return bool
	 */
	public function login_update ($data, $out) {

		if (!empty($data) && is_array($data)) {

			$this->auth_insert->update_by_conds(
				array(
					'authcode' => $data['authcode']
				),
				array(
					'state' => $data['state']
				)
			);

		}

		return true;
	}

}
