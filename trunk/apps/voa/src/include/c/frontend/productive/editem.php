<?php
/**
 * 活动/产品打分项信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_productive_editem extends voa_c_frontend_productive_base {

	public function execute() {

		$pt_id = (int)$this->request->get('pt_id');

		/** 读取活动/产品记录 */
		$serv_pt = &service::factory('voa_s_oa_productive', array('pluginid' => startup_env::get('pluginid')));
		$productive = $serv_pt->fetch_by_id($pt_id);

		/** 检查是否有编辑权限 */
		if (!$this->_chk_edit_permit($productive)) {
			$this->_error_message('no_privilege');
			return false;
		}

		/** 读取打分记录 */
		$serv_ptsr = &service::factory('voa_s_oa_productive_score', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv_ptsr->fetch_by_pt_id($pt_id);

		/** 计算主评分项分数 */
		$total = 0;
		$item2score = array();
		$uda_base = &uda::factory('voa_uda_frontend_productive_base');
		$uda_base->calc_score($total, $item2score, $list);

		$this->view->set('list', $list);
		$this->view->set('items', $this->_items);
		$this->view->set('productive', $productive);
		$this->view->set('item2score', $item2score);
		$this->view->set('shop', $this->_shops[$productive['csp_id']]);

		$this->_output('productive/editem');
	}

}
