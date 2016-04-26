<?php
/**
 * 编辑备忘
 * $Author$
 * $Id$
 */

class voa_c_frontend_vnote_edit extends voa_c_frontend_vnote_base {

	public function execute() {
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
		if ($this->_is_post()) {
			$this->_edit($vnote, $ccusers);
			return false;
		}

		$uda = &uda::factory('voa_uda_frontend_vnote_format');
		if (!$uda->format($vnote)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 读取备忘详情以及回复 */
		$serv_p = &service::factory('voa_s_oa_vnote_post', array('pluginid' => startup_env::get('pluginid')));
		$posts = $serv_p->fetch_by_vn_id($vn_id);
		foreach ($posts as $k => &$v) {
			$uda->vnote_post($v);
			/** 如果是备忘内容, 则 */
			if (voa_d_oa_vnote_post::FIRST_YES == $v['vnp_first']) {
				$vnote['_message'] = rhtmlspecialchars($v['vnp_message']);
				$vnote['vnp_id'] = $v['vnp_id'];
				unset($posts[$k]);
				continue;
			}
		}

		unset($v);

		$this->view->set('action', $this->action_name);
		$this->view->set('form_action', "/vnote/edit/{$vn_id}?vnp_id={$vnote['vnp_id']}&handlekey=post");
		$this->view->set('vnote', $vnote);
		$this->view->set('ccusers', $ccusers);
		$this->view->set('posts', $posts);
		$this->view->set('weeknames', config::get('voa.misc.weeknames'));
		$this->view->set('vn_id', $vn_id);

		$this->_output('vnote/post');
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
		
		$this->_success_message('vnote_edit_succeed', '/vnote/view/'.$vnote['vn_id']);
		return true;
	}
}
