<?php

/**
 * voa_c_api_interface_get_list
 * 活动列表
 * $Author$
 * $Id$
 */
class voa_c_api_interface_get_list extends voa_c_api_interface_base {

	protected function _before_action($action) {

		$this->_require_login = false;
		return parent::_before_action($action);
	}

	public function execute() {

		//整理数据
		$data = array();

		$uda_list = &uda::factory('voa_uda_frontend_interface_list');
		// 读取列表
		$list = array();
		// 搜索条件
		$conds = $this->request->getx();

		$conds['page'] = 1;
		$conds['perpage'] = 100;

		$uda_list->execute($conds, $list);
		$total = $uda_list->get_total();

		$uda_list = &uda::factory('voa_uda_frontend_interface_steplist');
		// 读取流程步骤列表
		$steplist = array();
		$f_conds = array();

		// 判断当前的动作是新增还是编辑
		if (isset($conds['f_id']) && !empty($conds['f_id'])) {
			$f_conds['f_id'] = $conds['f_id'];
		} else {
			$f_conds['f_id'] = $conds['cp_pluginid'];
		}

		$uda_list->list_by_conds($f_conds,$steplist);
		// 判断接口是否在流程里
		if (!empty($steplist)) {
			foreach ($steplist as $_k => $_val) {
				if ($list[$_val['n_id']]) {
					$list[$_val['n_id']]['checked'] = 'checked';
					$list[$_val['n_id']]['sort'] = $_val['s_order'];
					$list[$_val['n_id']]['s_id'] = $_val['s_id'];
					$list[$_val['n_id']]['login_uid'] = $_val['login_uid'];
				}
			}
		}

		// 输出结果
		$this->_result = array(
			'list'  => $list,
			'$steplist' => $steplist,
			'count' => count($list)
		);

		return true;
	}
}
