<?php
/**
 * voa_c_api_askoff_post_approve
 * 通过请假
 * $Author$
 * $Id$
 */

class voa_c_api_askoff_post_approve extends voa_c_api_askoff_verify {

	public function execute() {


		/*请求参数*/
		$fields = array(
			/*请假ID*/
			'ao_id' => array('type' => 'int', 'required' => true),
			/*请假进度信息*/
			'message' => array('type' => 'string_trim', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		/*请假ID检查*/
		if (empty($this->_params['ao_id'])) {
			return $this->_set_errcode(voa_errcode_api_askoff::ASKOFF_NOT_EXIST);
		}
		/*请假进度检查*/
		if (empty($this->_params['message'])) {
			return $this->_set_errcode(voa_errcode_api_askoff::NEW_APPROVE_MESSAGE_NULL);
		}

		/** 权限检查 */
		$rs = $this->_chk_permit();
		if(!$rs) return false;
		
		
		$uda = &uda::factory('voa_uda_frontend_askoff_update');
	
		if (!$uda->askoff_approve($this->_askoff, $this->_proc)) {
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		/** 格式化请假信息 */
		$uda_fmt = &uda::factory('voa_uda_frontend_askoff_format');
		$uda_fmt->askoff($this->_askoff);

		/** 取请假详情 */
		$serv_pt = &service::factory('voa_s_oa_askoff_post');
		$post = $serv_pt->fetch_first_by_ao_id($this->_askoff['ao_id']);

		/** 取应用插件信息 */
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		$cur_plugin = $plugins[startup_env::get('pluginid')];

		/** 取用户信息 */
		$mem = voa_h_user::get($this->_askoff['m_uid']);

		/** 发送微信模板消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $this->_askoff['ao_id']);
		$content = "审批状态\n"
				 . $this->_member['m_username']." 已同意\n"
				 . "------------------\n"
				 . "申请人：".$this->_askoff['m_username']."\n"
				 . "请假类别：".$this->_p_sets['types'][$this->_askoff['ao_type']]."\n"
				 . "请假天数：".$this->_askoff['_days']." 天"."\n"
				 . "开始日期：".$this->_askoff['_begintime_md']."\n"
				 . "结束日期：".$this->_askoff['_endtime_md']."\n"
				 . " <a href='".$viewurl."'>点击查看详情</a>";
		$data = array(
			'mq_touser' => $mem['m_openid'],
			'mq_toparty' => '',
			'mq_msgtype' => voa_h_qymsg::MSGTYPE_TEXT,
			'mq_agentid' => $this->_plugin['cp_agentid'],
			'mq_message' => $content
		);
		voa_h_qymsg::push_send_queue($data);
		

		return true;
	}
}
