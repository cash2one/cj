<?php
/**
 * 新增报销清单
 * $Author$
 * $Id$
 */

class voa_c_frontend_reimburse_bill_new extends voa_c_frontend_reimburse_bill {

	public function execute() {

		if ($this->_is_post()) {
			/** 调用处理函数 */
			$this->_add();
			return false;
		}

		/** 初始数据 */
		$bill = array(
			'rbb_time' => startup_env::get('timestamp'),
			'_time' => rgmdate(startup_env::get('timestamp'), 'Y-m-d\TH:i:s\Z', 0)
		);

		$attach = array();

		$this->view->set('action', $this->action_name);
		$this->view->set('form_action', '/reimburse/bill/new');
		$this->view->set('bill', $bill);
		$this->view->set('type_index', 0);
		$this->view->set('current_ts', startup_env::get('timestamp'));
		$this->view->set('navtitle', '报销明细');
		$this->view->set('attach', $attach);
		$this->view->set('types', $this->_p_sets['types']);
		$this->view->set('p_sets', $this->_p_sets);
		// 载入jsapi
		$this->_get_jsapi("['chooseImage', 'previewImage', 'uploadImage']");

		$this->_output('reimburse/bill/post');
	}

	/**
	 * 新增账单
	 * @return boolean
	 */
	protected function _add() {
		$uda = &uda::factory('voa_uda_frontend_reimburse_insert');
		/** 报销清单信息 */
		$bill = array();
		if (!$uda->reimburse_bill_new($bill)) {
			$this->_error_message($uda->error);
			return false;
		}

		$this->_success_message('报销单据新增成功', "/reimburse/new");
	}
}
