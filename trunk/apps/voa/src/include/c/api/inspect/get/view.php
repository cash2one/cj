<?php
/**
 * 巡店信息展示
 * voa_c_api_inspect_get_view
 * $Author$
 * $Id$
 */

class voa_c_api_inspect_get_view extends voa_c_api_inspect_base {

	public function execute() {
		// 请求参数
		$fields = array(
			// 巡店ID
			'id' => array('type' => 'int', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		$ins_id = $this->_params['id'];

		/** 读取巡店信息 */
		$serv_ins = &service::factory('voa_s_oa_inspect', array('pluginid' => startup_env::get('pluginid')));
		$inspect = $serv_ins->fetch_by_id($ins_id);
		/** 格式化 */
		$fmt = &uda::factory('voa_uda_frontend_inspect_format');
		if (!$fmt->inspect($inspect)) {
			//$this->_error_message($fmt->error);
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		if (empty($inspect)) {
			//$this->_error_message('inspect_is_not_exist');
			return $this->_set_errcode(voa_errcode_api_inspect::INSPECT_IS_NOT_EXIST);
		}

		/** 读取用户信息 */
		$serv_mem = &service::factory('voa_s_oa_inspect_mem', array('pluginid' => startup_env::get('pluginid')));
		$mlist = $serv_mem->fetch_by_ins_id($ins_id);
		/** 判断权限 */
		$is_permit = true;
		foreach ($mlist as $_m) {
			if ($_m['m_uid'] == startup_env::get('wbs_uid')) {
				$is_permit = true;
				break;
			}
		}

		if (false == $is_permit) {
			//$this->_error_message('no_privilege');
			return $this->_set_errcode(voa_errcode_api_inspect::NO_PRIVILEGE);
		}

		/** 读取打分项 */
		$serv_score = &service::factory('voa_s_oa_inspect_score', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv_score->fetch_by_ins_id($ins_id);
		/** 计算主评分项分数 */
		$total = 0;
		$item2score = array();
		$uda_base = &uda::factory('voa_uda_frontend_inspect_base');
		$uda_base->calc_score($total, $item2score, $list);

		/** 重组返回json数组 */
		$this->_result = array(
			'id' => $ins_id,
			'inspect' => array(
				'uid' => $inspect['m_uid'],// 创建者uid
				'username' => $inspect['m_username'],// 创建者名字
				'shop' => $this->_shops[$inspect['csp_id']],// 店铺名
			),
			'total' => $total,
			'item2score' =>$item2score,
			'item' => $this->_items

		);

		return true;
	}

}
