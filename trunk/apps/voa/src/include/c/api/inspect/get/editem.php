<?php
/**
 * 巡店打分项信息
 * voa_c_api_inspect_get_editem
 * $Author$
 * $Id$
 */

class voa_c_api_inspect_get_editem extends voa_c_api_inspect_base {

	public function execute() {

		// 请求参数
		$fields = array(
			// 巡店　ID
			'id' => array('type' => 'int', 'required' => true),
		);

		if (!$this->_check_params($fields)) {
			return false;
		}
		$ins_id = $this->_params['id'];
		/** 读取巡店记录 */
		$serv_ins = &service::factory('voa_s_oa_inspect', array('pluginid' => startup_env::get('pluginid')));
		$inspect = $serv_ins->fetch_by_id($ins_id);

		/** 检查是否有编辑权限 */
		if (!$this->_chk_edit_permit($inspect)) {
			//$this->_error_message('no_privilege');
			//return $this->_set_errcode(voa_errcode_api_inspect::NO_PRIVILEGE);
		}

		/** 读取打分记录 */
		$serv_isr = &service::factory('voa_s_oa_inspect_score', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv_isr->fetch_by_ins_id($ins_id);

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
			'items' => $this->_items,

		);

		return true;
	}

}
