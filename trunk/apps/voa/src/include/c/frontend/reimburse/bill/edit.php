<?php
/**
 * 编辑报销清单
 * $Author$
 * $Id$
 */

class voa_c_frontend_reimburse_bill_edit extends voa_c_frontend_reimburse_bill {

	public function execute() {
		$rbb_id = (int)$this->request->get('rbb_id');
		$serv_b = &service::factory('voa_s_oa_reimburse_bill', array('pluginid' => startup_env::get('pluginid')));
		$bill = $serv_b->fetch_by_id($rbb_id);
		if (empty($rbb_id) || empty($bill)) {
			$this->_error_message('reimburse_bill_is_not_exists');
			return false;
		}

		if ($bill['m_uid'] != $this->_user['m_uid']) {
			$this->_error_message('no_privilege');
		}

		/** 格式化清单数据 */
		$fmt = &uda::factory('voa_uda_frontend_reimburse_format');
		if (!$fmt->reimburse_bill($bill)) {
			$this->_error_message($fmt->error);
			return false;
		}

		if ($this->_is_post()) {
			/** 调用处理函数 */
			$this->_edit($bill);
			return false;
		}

		/** 获取索引值 */
		$type_index = 1;
		if (!$this->_get_type_index($bill['rbb_type'], $type_index)) {
			$this->_error_message('fatal_error');
			return false;
		}

		// 由于前端使用UTC时间, 这里要转换
		$bill['_time'] = rgmdate($bill['rbb_time'], 'Y-m-d\TH:i:s\Z', 0);

		$this->view->set('action', $this->action_name);
		$this->view->set('form_action', "/reimburse/bill/edit/{$bill['rbb_id']}");
		$this->view->set('bill', $bill);
		$this->view->set('type_index', $type_index);
		$this->view->set('current_ts', $bill['rbb_time']);
		$this->view->set('navtitle', '报销明细');
		$this->view->set('types', $this->_p_sets['types']);
		$this->view->set('p_sets', $this->_p_sets);
		$this->view->set('attachs', !empty($bill['_attachs']) ? $bill['_attachs'] : array());

		$this->_output('reimburse/bill/post');
	}

	/**
	 * 获取索引值
	 * @param unknown $type
	 */
	protected function _get_type_index($type, &$type_index) {
		foreach ($this->_p_sets['types'] as $k => $v) {
			if ($type == $k) {
				break;
			}

			$type_index ++;
		}

		return true;
	}

	/**
	 * 修改操作
	 * @param unknown $bill
	 * @return boolean
	 */
	protected function _edit($bill) {
		$uda = &uda::factory('voa_uda_frontend_reimburse_update');
		/** 报销清单信息 */
		if (!$uda->reimburse_bill_update($bill)) {
			$this->_error_message($uda->error);
			return false;
		}

		$this->_success_message('报销清单修改成功', "/reimburse/new/");
	}
}
