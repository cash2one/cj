<?php
/**
 * 拒绝申请
 * voa_c_api_askfor_post_refuse
 * $Author$
 * $Id$
 */

class voa_c_api_askfor_post_refuse extends voa_c_api_askfor_verify {

	public function execute() {

		/*请求参数*/
		$fields = array(
			/*审批ID*/
			'af_id' => array('type' => 'int', 'required' => true),
		);
		if (!$this->_check_params($fields)) {
			return false;
		}

		/*审批内容检查*/
		if (empty($this->_params['af_id'])) {
			return $this->_set_errcode(voa_errcode_api_askfor::ASKFOR_NOT_EXIST);
		}

		/** 权限检查 */
		$rs = $this->_chk_permit();
		if(!$rs) return false;

		$uda = &uda::factory('voa_uda_frontend_askfor_update');
		if (!$uda->askfor_refuse($this->_askfor, $this->_proc)) {
			$this->_errcode = $uda->errcode;
			$this->_errmsg = $uda->errmsg;
			return false;
		}

		/** 格式化审批信息 */
		$uda_fmt = &uda::factory('voa_uda_frontend_askfor_format');
		$uda_fmt->askfor($this->_askfor);

		/** 取审批详情 */
		$serv_pt = &service::factory('voa_s_oa_askfor_comment');
		$post = $serv_pt->fetch_by_id($this->_askfor['af_id']);

		/** 取应用插件信息 */
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		$cur_plugin = $plugins[startup_env::get('pluginid')];

		/** 取用户信息 */
		$mem = voa_h_user::get($this->_askfor['m_uid']);

		/** 发送微信模板消息 */
		$viewurl = '';
		$this->get_view_url($viewurl, $this->_askfor['af_id']);
		$content = "审批状态: {$this->_member['m_username']} 已拒绝\n"
				 . "------------------\n"
				 . "主题：".$this->_askfor['af_subject']."\n"
				 . "申请人：".$this->_askfor['m_username']."\n"
				 . "审批时间：".date('Y-m-d H:i')."\n"
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
