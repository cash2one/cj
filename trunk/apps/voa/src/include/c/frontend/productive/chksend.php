<?php
/**
 * 活动/产品信息发送
 * $Author$
 * $Id$
 */

class voa_c_frontend_productive_chksend extends voa_c_frontend_productive_base {

	public function execute() {

		$pt_id = (int)$this->request->get('pt_id');

		/** 读取活动/产品信息 */
		$serv_pt = &service::factory('voa_s_oa_productive', array('pluginid' => startup_env::get('pluginid')));
		$this->_productive = $serv_pt->fetch_by_id($pt_id);

		/** 检查是否有编辑权限 */
		if (empty($this->_productive) || !$this->_chk_edit_permit($this->_productive)) {
			$this->_error_message('no_privilege');
			return false;
		}

		/** 读取打分项 */
		$serv_score = &service::factory('voa_s_oa_productive_score', array('pluginid' => startup_env::get('pluginid')));
		$this->_score_list = $serv_score->fetch_by_pt_id($pt_id);

		/** 计算总分 */
		$this->_total = 0;
		$item2score = array();
		$uda_base = &uda::factory('voa_uda_frontend_productive_base');
		$uda_base->calc_score($this->_total, $item2score, $this->_score_list);
		if (0 >= $this->_total) {
			//$this->_error_message('all_item_score_is_require');
			//return false;
		}

		$this->_success_message('check_success', "/frontend/productive/edit/pt_id/".$pt_id);
	}
}
