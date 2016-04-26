<?php
/**
 * 巡店信息提交(编辑)
 *　voa_c_api_inspect_post_edit
 * $Author$
 * $Id$
 */

class 　voa_c_api_inspect_post_edit extends voa_c_api_inspect_base {
	/** 打分项和详情对应信息 */
	protected $_score_list = array();
	/** 总分 */
	protected $_total = 0;
	/** 巡店信息 */
	protected $_inspect;

	public function execute() {
		// 请求参数
		$fields = array(
			// 巡店ID
			'ins_id' => array('type' => 'int', 'required' => true),
			// 店铺ID
			'csp_id' => array('type' => 'int', 'required' => true),
			//目标人
			'mem_uids' => array('type' => 'string_trim', 'required' => true),
			//抄送人
			'cc_uids' => array('type' => 'string_trim', 'required' => false),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}
		// 验证抄送人
		if (empty($this->_params['mem_uids'])) {
			return $this->_set_errcode(voa_errcode_api_inspect::NEW_MEM_UIDS_NULL);
		}

		$ins_id = $this->_params['ins_id'];

		/** 读取巡店信息 */
		$serv_ins = &service::factory('voa_s_oa_inspect', array('pluginid' => startup_env::get('pluginid')));
		$this->_inspect = $serv_ins->fetch_by_id($ins_id);

		/** 检查是否有编辑权限 */
		if (empty($this->_inspect) || !$this->_chk_edit_permit($this->_inspect)) {
			//$this->_error_message('no_privilege');
			return $this->_set_errcode(voa_errcode_api_inspect::NO_PRIVILEGE);
		}

		/** 格式化 */
		$fmt = &uda::factory('voa_uda_frontend_inspect_format');
		if (!$fmt->inspect($this->_inspect)) {
			//$this->_error_message($fmt->error);
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		/** 读取打分项 */
		$serv_score = &service::factory('voa_s_oa_inspect_score', array('pluginid' => startup_env::get('pluginid')));
		$this->_score_list = $serv_score->fetch_by_ins_id($ins_id);

		/** 计算总分 */
		$this->_total = 0;
		$item2score = array();
		$uda_base = &uda::factory('voa_uda_frontend_inspect_base');
		$uda_base->calc_score($this->_total, $item2score, $this->_score_list);
		if (0 > $this->_total) {
			//$this->_error_message('all_item_score_is_require');
			return $this->_set_errcode(voa_errcode_api_inspect::ALL_ITEM_SCORE_IS_REQUIRE);
		}

		//入库操作
		if (!$this->_edit()) {
			return false;
		}

		$this->_result = array(
			'id' => $this->_inspect['ins_id']
		);

		return true;
	}

	/** 提交编辑 */
	protected function _edit() {

		$params = $this->request->getx();
		$params['total'] = $this->_total;
		$params['wbs_user'] = $this->_user;

		$uda = &uda::factory('voa_uda_frontend_inspect_update');
		/** 接收人 */
		$memlist = array();
		/** 抄送人信息 */
		$cculist = array();
		if (!$uda->inspect_edit($params, $this->_inspect, $memlist, $cculist)) {
			//$this->_error_message($uda->error);
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		/** 更新草稿信息 */
		$this->_update_draft(array_keys($memlist), array_keys($cculist));

		/** 整理输出 */
		$uda_fmt = &uda::factory('voa_uda_frontend_inspect_format');
		if (!$uda_fmt->inspect($this->_inspect)) {
			//$this->_error_message($uda_fmt->error, get_referer());
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $this->_inspect['ins_id']);
		$content = $this->_shops[$this->_inspect['csp_id']]['csp_name']."已完成\n"
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
		
		return true;
	}
}
