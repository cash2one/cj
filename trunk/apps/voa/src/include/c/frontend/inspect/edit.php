<?php
/**
 * 巡店信息提交(编辑)
 * $Author$
 * $Id$
 */

class voa_c_frontend_inspect_edit extends voa_c_frontend_inspect_base {
	// 打分项和详情对应信息
	protected $_score_list = array();
	// 总分
	protected $_total = 0;
	// 巡店信息
	protected $_inspect = array();

	public function execute() {

		$ins_id = (int)$this->request->get('ins_id');

		// 读取巡店信息
		$uda_inspect = new voa_uda_frontend_inspect_get();
		$uda_inspect->execute(array('ins_id' => $ins_id), $this->_inspect);

		// 检查是否有编辑权限
		if (empty($this->_inspect) || !$this->_chk_edit_permit($this->_inspect)) {
			$this->_error_message('no_privilege');
			return false;
		}

		// 读取打分项
		$uda_score = new voa_uda_frontend_inspect_score_list();
		$uda_score->execute(array('ins_id' => $ins_id), $this->_score_list);

		// 计算总分
		$this->_total = 0;
		$item2score = array();
		$uda_score->calc_score($this->_total, $item2score, $this->_score_list);
		if (0 >= $this->_total) {
			$this->_error_message('all_item_score_is_require');
			return false;
		}

		if ($this->_is_post()) {
			$this->_edit();
			return true;
		}

		// 取草稿信息
		$data = array();
		$this->_get_draft($data);

		$this->view->set('cculist', isset($data['ccusers']) ? $data['ccusers'] : array());
		$this->view->set('accepters', isset($data['accepters']) ? $data['accepters'] : array());
		$this->view->set('inspect', $this->_inspect);
		$this->view->set('total', $this->_total);
		$this->view->set('form_action', '/frontend/inspect/edit/ins_id/'.$ins_id.'/?handlekey=post');
		$this->view->set('shop', $this->_shops[$this->_inspect['csp_id']]);

		$this->_output('inspect/post');
	}

	// 提交编辑
	protected function _edit() {

		$params = $this->request->getx();
		$params['_total'] = $this->_total;
		$params['_wbs_user'] = $this->_user;
		$params['_inspect'] = $this->_inspect;

		$uda = &uda::factory('voa_uda_frontend_inspect_update');
		$result = array();
		try {
			voa_uda_frontend_transaction_abstract::s_begin();

			if (!$uda->execute($params, $result)) {
				return false;
			}

			voa_uda_frontend_transaction_abstract::s_commit();
		} catch (help_exception $e) {
			voa_uda_frontend_transaction_abstract::s_rollback();
			$this->_error_message($e->getMessage());
		}

		$memlist = $result['to'];
		$cculist = $result['cc'];

		// 更新草稿信息
		$this->_update_draft(array_keys($memlist), array_keys($cculist));

		// 发送微信消息
		$viewurl = '';
		$this->get_view_url($viewurl, $this->_inspect['ins_id']);
		$content = $this->_shops[$this->_inspect['csp_id']]['csp_name']."已完成\n"
				 . "巡视人：".$this->_user['m_username']."\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";

		// 整理需要接收消息的用户
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

		// 写入 cookie, 刷新页面时发送
		$this->set_queue_session(array($data['mq_id']));

		// 给目标人发送微信消息
		$this->_success_message('巡店信息发送成功', "/frontend/inspect/view/ins_id/".$this->_inspect['ins_id']);
	}
}
