<?php
/**
 * 新的巡店信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_inspect_tasknew extends voa_c_frontend_inspect_base {

	public function execute() {

		if ($this->_is_post()) {
			// 调用处理函数
			$this->_add();
			return false;
		}

		$region2shop = array();
		$this->get_shop_json($region2shop);

		$this->view->set('action', $this->action_name);
		$this->view->set('form_action', '/frontend/inspect/tasknew?handlekey=post');
		$this->view->set('region2shop', rjson_encode($region2shop));

		$this->_output('inspect/tasknew');
	}

	public function _add() {

		$uda = &uda::factory('voa_uda_frontend_inspect_add');
		// 抄送人信息
		$params = $this->request->getx();
		$params['_user'] = $this->_user;
		$inspect = array();

		try {
			if (!$uda->execute($params, $inspect)) {
				$this->_error_message($uda->error);
				return false;
			}
		} catch (help_exception $e) {
			$this->_error_message($e->getMessage());
			return true;
		}

		// 给目标人发送微信消息
		$this->_success_message('巡店新增成功', "/frontend/inspect/editem/ins_id/".$inspect['ins_id']);
	}
}
