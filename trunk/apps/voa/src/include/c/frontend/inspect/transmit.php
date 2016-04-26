<?php
/**
 * 巡店信息转发
* $Author$
* $Id$
*/

class voa_c_frontend_inspect_transmit extends voa_c_frontend_inspect_base {
	// 巡店信息
	protected $_inspect = array();

	public function execute() {

		// 如果非 post 提交
		if (!$this->_is_post()) {
			$this->_error_message('非法提交');
			return true;
		}

		// 判断是否有查看权限
		if (!$this->__chk_view_permit()) {
			$this->_error_message('no_privilege');
			return true;
		}

		$uda_trans = new voa_uda_frontend_inspect_transmit();
		$params = $this->request->getx();
		$params['_inspect'] = $this->_inspect;
		$res = array();
		if (!$uda_trans->execute($params, $res)) {
			$this->_error_message('请选项抄送人');
			return true;
		}

		$this->_success_message('转发成功');

		return true;
	}

	private function __chk_view_permit() {

		$ins_id = (int)$this->request->get('ins_id');

		// 读取巡店信息
		$uda_inspect = new voa_uda_frontend_inspect_get();
		$this->_inspect = array();
		$uda_inspect->execute(array('ins_id' => $ins_id), $this->_inspect);

		if (empty($this->_inspect)) {
			$this->_error_message('inspect_is_not_exist');
			return false;
		}

		// 读取用户信息
		$uda_mem = new voa_uda_frontend_inspect_mem_list();
		$uda_mem->set_limit(false);
		$mlist = array();
		$uda_mem->execute($this->request->getx(), $mlist);

		// 判断权限
		$is_permit = false;
		foreach ($mlist as $_m) {
			if ($_m['m_uid'] == startup_env::get('wbs_uid')) {
				$is_permit = true;
				break;
			}
		}

		if (false == $is_permit) {
			$this->_error_message('no_privilege');
			return false;
		}

		return true;
	}

}
