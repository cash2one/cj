<?php
/**
 * 编辑备忘
 * $Author$
 * $Id$
 */

class voa_c_api_vnote_post_edit extends voa_c_api_vnote_base {

	public function execute() {
		/*需要的参数*/
		$fields = array(
			'vn_id' => array('type' => 'int', 'required' => true),
			'message' => array('type' => 'string_trim', 'required' => true),
		);
		/*基本验证检查*/
		if (!$this->_check_params($fields)) {
			return false;
		}
		/*审批标题检查*/
		if (empty($this->_params['message'])) {
			return $this->_set_errcode('message not null');
		}
		
		/** 备忘ID */
		$vn_id = rintval($this->request->get('vn_id'));

	
		
		/** 读取备忘信息 */
		$serv = &service::factory('voa_s_oa_vnote', array('pluginid' => startup_env::get('pluginid')));
		$vnote = $serv->fetch_by_id($vn_id);
		if (empty($vn_id) || empty($vnote)) {
			$this->_error_message('vnote_is_not_exists');
		}

		/** 读取报名目标人和抄送人 */
		$serv_m = &service::factory('voa_s_oa_vnote_mem', array('pluginid' => startup_env::get('pluginid')));
		$mems = $serv_m->fetch_by_vn_id($vn_id);
		/** 取出 uid */
		$uids = array();
		/** 抄送人信息 */
		$ccusers = array();
		foreach ($mems as $v) {
			$uids[$v['m_uid']] = $v['m_uid'];
			if ($v['m_uid'] == $vnote['m_uid']) {
				continue;
			}

			$ccusers[$v['m_uid']] = $v;
		}

		/** 判断当前用户是否有权限查看 */
		if (startup_env::get('wbs_uid') != $vnote['m_uid']) {
			$this->_error_message('no_privilege');
		}

		/** 如果是提交编辑操作, 则 */
		$this->_edit($vnote, $ccusers);
	}

	/**
	 * 编辑备忘
	 * @return boolean
	 */
	protected function _edit($vnote, $ccusers) {
		$uda = uda::factory('voa_uda_frontend_vnote_update');
		if (!$uda->vnote_edit($vnote, $ccusers)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 把消息推入队列 */
		$this->_to_queue($vnote, $ccusers);
		
		return true;
	}
}
