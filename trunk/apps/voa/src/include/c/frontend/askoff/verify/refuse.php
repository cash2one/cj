<?php
/**
 * 拒绝申请
 * $Author$
 * $Id$
 */

class voa_c_frontend_askoff_verify_refuse extends voa_c_frontend_askoff_verify {

	public function execute() {
		/** 权限检查 */
		$this->_chk_permit();

		$uda = &uda::factory('voa_uda_frontend_askoff_update');
		if (!$uda->askoff_refuse($this->_askoff, $this->_proc)) {
			$this->_error_message($uda->error, get_referer());
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
				 . $this->_user['m_username']." 已拒绝\n"
				 . "------------------\n"
				 . "申请人：".$this->_askoff['m_username']."\n"
				 . "请假类别：".$this->_p_sets['types'][$this->_askoff['ao_type']]."\n"
				 . "请假时长：".$this->_askoff['_timespace']."\n"
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

		/** 写入 cookie, 刷新页面时发送 */
		$this->set_queue_session(array($data['mq_id']));

		$this->_success_message('操作成功', "/askoff/view/".$this->_askoff['ao_id']);
	}
}
