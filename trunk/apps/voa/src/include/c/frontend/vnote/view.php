<?php
/**
 * 查看备忘
 * $Author$
 * $Id$
 */

class voa_c_frontend_vnote_view extends voa_c_frontend_vnote_base {
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 抄送人 */
	const STATUS_CC = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;

	public function execute() {
		/** 备忘ID */
		$vn_id = rintval($this->request->get('vn_id'));

		$uda = &uda::factory('voa_uda_frontend_vnote_format');

		/** 读取备忘信息 */
		$serv = &service::factory('voa_s_oa_vnote', array('pluginid' => startup_env::get('pluginid')));
		$vnote = $serv->fetch_by_id($vn_id);
		if (empty($vn_id) || empty($vnote)) {
			$this->_error_message('vnote_is_not_exists');
		}

		if (!$uda->format($vnote)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 读取报名目标人和抄送人 */
		$serv_m = &service::factory('voa_s_oa_vnote_mem', array('pluginid' => startup_env::get('pluginid')));
		$mems = $serv_m->fetch_by_vn_id($vn_id);
		/** 取出 uid */
		$uids = array();
		/** 判断用户权限 */
		$is_permit = false;
		/** 抄送人信息 */
		$ccusers = array();
		foreach ($mems as $v) {
			$uids[$v['m_uid']] = $v['m_uid'];
			if (startup_env::get('wbs_uid') == $v['m_uid'] || 0 == $v['m_uid']) {
				$is_permit = true;
			}

			if ($v['m_uid'] == $vnote['m_uid']) {
				continue;
			}

			$ccusers[] = $v;
		}

		/** 判断当前用户是否有权限查看 */
		if (!$is_permit && startup_env::get('wbs_uid') != $vnote['m_uid']) {
			$this->_error_message('no_privilege');
		}

		/** 读取备忘详情以及回复 */
		$serv_p = &service::factory('voa_s_oa_vnote_post', array('pluginid' => startup_env::get('pluginid')));
		$posts = $serv_p->fetch_by_vn_id($vn_id);
		foreach ($posts as $k => &$v) {
			$uda->vnote_post($v);
			/** 如果是备忘内容, 则 */
			if (voa_d_oa_vnote_post::FIRST_YES == $v['vnp_first']) {
				$vnote['_message'] = rhtmlspecialchars($v['vnp_message']);
				unset($posts[$k]);
				continue;
			}
		}

		unset($v);

		$this->view->set('action', $this->action_name);
		$this->view->set('vnote', $vnote);
		$this->view->set('ccusers', $ccusers);
		$this->view->set('posts', $posts);
		$this->view->set('weeknames', config::get('voa.misc.weeknames'));
		$this->view->set('vn_id', $vn_id);

		$this->_output('vnote/view');
	}

}
