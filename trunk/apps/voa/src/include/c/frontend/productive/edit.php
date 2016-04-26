<?php
/**
 * 活动/产品信息提交(编辑)
 * $Author$
 * $Id$
 */

class voa_c_frontend_productive_edit extends voa_c_frontend_productive_base {
	/** 打分项和详情对应信息 */
	protected $_score_list = array();
	/** 总分 */
	protected $_total = 0;
	/** 活动/产品信息 */
	protected $_productive;

	public function execute() {

		$pt_id = (int)$this->request->get('pt_id');

		/** 读取活动/产品信息 */
		$serv_pt = &service::factory('voa_s_oa_productive', array('pluginid' => startup_env::get('pluginid')));
		$this->_productive = $serv_pt->fetch_by_id($pt_id);

		/** 检查是否有编辑权限 */
		if (empty($this->_productive) || !$this->_chk_edit_permit($this->_productive)) {
			$this->_error_message('no_privilege');
			return false;
		}

		/** 格式化 */
		$fmt = &uda::factory('voa_uda_frontend_productive_format');
		if (!$fmt->productive($this->_productive)) {
			$this->_error_message($fmt->error);
			return false;
		}

		/** 读取打分项 */
		$serv_score = &service::factory('voa_s_oa_productive_score', array('pluginid' => startup_env::get('pluginid')));
		$this->_score_list = $serv_score->fetch_by_pt_id($pt_id);

		/** 计算总分 */
		$this->_total = 0;
		$item2score = array();
		$uda_base = &uda::factory('voa_uda_frontend_productive_base');
		$uda_base->calc_score($this->_total, $item2score, $this->_score_list);
		if (0 > $this->_total) {
			//$this->_error_message('all_item_score_is_require');
			//return false;
		}

		if ($this->_is_post()) {
			$this->_edit();
		}

		/** 取草稿信息 */
		$data = array();
		$this->_get_draft($data);

		$this->view->set('cculist', isset($data['ccusers']) ? $data['ccusers'] : array());
		$this->view->set('accepters', isset($data['accepters']) ? $data['accepters'] : array());
		$this->view->set('productive', $this->_productive);
		$this->view->set('total', $this->_total);
		$this->view->set('form_action', '/frontend/productive/edit/pt_id/'.$pt_id.'/?handlekey=post');
		$this->view->set('shop', $this->_shops[$this->_productive['csp_id']]);

		$this->_output('productive/post');
	}

	/** 提交编辑 */
	protected function _edit() {

		$params = $this->request->getx();
		$params['total'] = $this->_total;
		$params['wbs_user'] = $this->_user;

		$uda = &uda::factory('voa_uda_frontend_productive_update');
		/** 接收人 */
		$memlist = array();
		/** 抄送人信息 */
		$cculist = array();
		if (!$uda->productive_edit($params, $this->_productive, $memlist, $cculist)) {
			$this->_error_message($uda->error);
			return false;
		}

		/** 更新草稿信息 */
		$this->_update_draft(array_keys($memlist), array_keys($cculist));

		/** 整理输出 */
		$uda_fmt = &uda::factory('voa_uda_frontend_productive_format');
		if (!$uda_fmt->productive($this->_productive)) {
			$this->_error_message($uda_fmt->error, get_referer());
			return false;
		}

		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $this->_productive['pt_id']);
		$content = $this->_shops[$this->_productive['csp_id']]['csp_name']."已完成\n"
				 . "巡视人：".$this->_user['m_username']."\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";

		/** 整理需要接收消息的用户 */
		$users = array();
		foreach ($memlist as $_u) {
			if (startup_env::get('wbs_uid') == $_u['m_uid']) {
				continue;
			}

			$users[$_u['m_uid']] = $_u['m_openid'];
		}

		foreach ($cculist as $u) {
			if (startup_env::get('wbs_uid') == $u['m_uid']) {
				continue;
			}

			$users[$u['m_uid']] = $u['m_openid'];
		}

		$data = array(
			'mq_touser' => implode('|', $users),
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => (int)$this->_plugin['cp_agentid'],
			'mq_message' => $content
		);
		voa_h_qymsg::push_send_queue($data);

		/** 写入 cookie, 刷新页面时发送 */
		$this->set_queue_session(array($data['mq_id']));

		/** 给目标人发送微信消息 */
		$this->_success_message('信息发送成功', "/frontend/productive/view/pt_id/".$this->_productive['pt_id']);
	}
}
