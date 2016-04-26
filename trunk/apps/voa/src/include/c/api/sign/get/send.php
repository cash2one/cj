<?php

/**
 * 升级考勤推送操作手册图文消息
 * $Author$
 * $Id$
 */
class voa_c_api_sign_get_send extends voa_c_api_sign_base {

	protected function _before_action($action = '') {
		$this->_require_login = false;

		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

		return true;
		$settings = voa_h_cache::get_instance()->get('plugin.sign.setting', 'oa');

		$msg_title = '【考勤】微信端操作指南';
		$msg_desc = '移动互联网时代的打卡利器，支持多部门分班次、多地点考勤设置，员工在微信端即可签到。';
		$msg_url = 'http://www.vchangyi.com/bbs/forum.php?mod=viewthread&tid=384&page=1&extra=#pid919';
		$toparty = '@all';
		$msg_picurl = 'http://st.vchangyi.com/plugins/sign/news_cover.jpg';
		$agentid = $settings['agentid'];
		$pluginid = $settings['pluginid'];

		voa_h_qymsg::push_news_send_queue(null, $msg_title, $msg_desc, $msg_url, null, $toparty, $msg_picurl, $agentid, $pluginid);

		return true;
	}

}
