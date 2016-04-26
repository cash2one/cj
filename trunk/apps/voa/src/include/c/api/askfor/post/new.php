<?php
/**
 * voa_c_api_askfor_post_new
 * 新建审批
 * $Author$
 * $Id$
 */
class voa_c_api_askfor_post_new extends voa_c_api_askfor_base {

	public function execute() {

		/*需要的参数*/
		$fields = array(
			'subject' => array('type' => 'string_trim', 'required' => true),	//审批主题
			'message' => array('type' => 'string_trim', 'required' => true),	//审批内容
			'at_ids' => array('at_ids' => 'string_trim', 'required' => false),  //附件ID
			'aft_id' => array('aft_id' => 'int', 'required' => true),  //流程ID
			'cols' => array('cols' => 'array', 'required' => false),   //自定义字段
		);
		/*基本验证检查*/
		if (!$this->_check_params($fields)) {
			return false;
		}
		/*审批标题检查*/
		if (empty($this->_params['subject'])) {
			return $this->_set_errcode(voa_errcode_api_askfor::SUBJECT_NULL);
		}
		/*审批内容检查*/
		if (empty($this->_params['message'])) {
			return $this->_set_errcode(voa_errcode_api_askfor:: MESSAGE_NULL);
		}


		/*入库操作*/
		if (!$this->_add()) {
			return false;
		}

		$this->_result = array(
			'af_id' => $this->_return['af_id']
		);
		return true;
	}

	/*
	 * 入库
	 * @return boolen 新增成功
	*/
	protected function _add() {

		$uda = &uda::factory('voa_uda_frontend_askfor_insert');
		/** 审批信息 */
		$askfor = array();
		if (!$uda->askfor_new($askfor)) {
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		$this->_return = $askfor;

		/** 整理输出 */
		/*$uda_fmt = &uda::factory('voa_uda_frontend_askfor_format');
		if (!$uda_fmt->askfor($askfor)) {
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			//$this->_error_message($uda_fmt->error, get_referer());
			return false;
		}
*/

		/** 发送微信消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $askfor['af_id']);
		$content = "待 ".$mem['m_username']." 审核\n"
				 . "主题：".$askfor['af_subject']."\n"
				 . "申请人：".$this->_member['m_username']."\n"
				 . "申请时间：".date('Y-m-d H:i')."\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";


		/** 整理需要接收消息的用户 */
		$users = array($mem['m_uid'] => $mem['m_openid']);
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
