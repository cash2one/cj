<?php
/**
 * 新的活动/产品信息
 * $Author$
 * $Id$
 */

class voa_c_frontend_productive_tasknew extends voa_c_frontend_productive_base {

	public function execute() {
		if ($this->_is_post()) {
			/** 调用处理函数 */
			$this->_add();
			return false;
		}

		$region2shop = array();
		$this->get_shop_json($region2shop);

		$this->view->set('action', $this->action_name);
		$this->view->set('form_action', '/frontend/productive/tasknew?handlekey=post');
		$this->view->set('region2shop', rjson_encode($region2shop));

		$this->_output('productive/tasknew');
	}

	public function _add() {
		$uda = &uda::factory('voa_uda_frontend_productive_insert');
		/** 抄送人信息 */
		$params = $this->request->getx();
		$params['_user'] = $this->_user;
		if (!$uda->productive_new($params, $productive)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 给目标人发送微信消息 */
		$this->_success_message('新增成功', "/frontend/productive/editem/pt_id/".$productive['pt_id']);
	}
}
