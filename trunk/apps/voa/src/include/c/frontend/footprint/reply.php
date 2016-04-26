<?php
/**
 * 销售轨迹回复操作
 * $Author$
 * $Id$
 */

class voa_c_frontend_footprint_reply extends voa_c_frontend_footprint_base {

	public function execute() {
		if (!$this->_is_post()) {
			$this->_error_message('submit_invalid');
			return false;
		}

		/** 销售轨迹ID */
		$fp_id = rintval($this->request->get('fp_id'));
		/** 读取对应的轨迹信息 */
		$serv = &service::factory('voa_s_oa_footprint', array('pluginid' => startup_env::get('pluginid')));
		$footprint = $serv->fetch_by_id($fp_id);
		if (empty($footprint)) {
			$this->_error_message('footprint_is_not_exist');
			return false;
		}

		/** 读取权限用户 */
		$serv_m = &service::factory('voa_s_oa_footprint_mem', array('pluginid' => startup_env::get('pluginid')));
		$mems = $serv_m->fetch_by_fp_id($fp_id);

		/** 检查权限 */
		$is_permit = false;
		foreach ($mems as $m) {
			if ($m['m_uid'] == startup_env::get('wbs_uid')) {
				$is_permit = true;
				break;
			}
		}

		if (false == $is_permit) {
			$this->_error_message('no_privilege');
			return false;
		}

		/** 回复信息入库 */
		$uda = &uda::factory('voa_uda_frontend_footprint_insert');
		$post = array();
		if (!$uda->footprint_reply($footprint, $post)) {
			$this->_error_message($uda->error);
			return false;
		}

		$this->_success_message('reply_succeed');
	}

}
