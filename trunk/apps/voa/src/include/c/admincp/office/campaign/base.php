<?php
/**
 * voa_c_admincp_office_showroom_base
 * 企业后台/微办公管理/活动推广/基本控制器
 * Create By linshiling
 * $Author$
 * $Id$
 */

class voa_c_admincp_office_campaign_base extends voa_c_admincp_office_base {
	protected $_p_sets = array();
	protected $_uda_base = null;
	protected $url;

	// 设置模板使用的链接
	protected function seturl() {

		$this->view->set('deleteUrlBase', $this->url('delete'));
		$this->view->set('editUrlBase', $this->url('edit'));
		$this->view->set('viewUrl', $this->url('view'));
		$this->view->set('listAllUrl', $this->url('list'));
	}

	protected function url($act) {

		return parent::cpurl($this->_module, $this->_operation, $act, $this->_module_plugin_id);
	}

	protected function ajax($state, $info = '') {

		$return = array('state' => $state, 'info' => $info);
		echo json_encode($return);
		exit();
	}

	// 输出编辑器代码
	protected function ueditor($value = '') {
		// 初始化编辑器
		$ueditor = new ueditor();
		// 编辑器资源路径
		$ueditor->ueditor_home_url = config::get(startup_env::get('app_name') . '.ueditor.ueditor_home_url');
		// 处理上传文件路径
		$ueditor->server_url = '/admincp/ueditor/';

		$ueditor->ueditor_config = array('toolbars' => '_mobile', 'textarea' => 'content', 'initialFrameHeight' => 300, 'initialContent' => $value, 'elementPathEnabled' => false);
		if (! $ueditor->create_editor('content', '')) {
			$ueditor_output = $ueditor->ueditor_error;
		} else {
			$ueditor_output = $ueditor->ueditor_html;
		}

		// 编辑器
		$this->view->set('ueditor_output', $ueditor_output);
	}

	// 默认选中部门
	protected function deps($ids) {

		if (! $ids) {
			return;
		}

		$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
		$deps = $serv_d->fetch_all_by_key($ids);
		$temp = array();
		foreach ($deps as $dep) {
			$temp[] = array('id' => $dep['cd_id'], 'name' => $deps[$dep['cd_id']]['cd_name'], 'input_name_department' => 'deps[]');
		}

		$this->view->set('deps', $temp);
	}

	// 时间下拉框数据
	protected function times() {

		// 时间列表(半小时为一段)
		$today = rstrtotime('today');
		for ($i = 1; $i < 48; $i ++) {
			$t = rgmdate($today + 1800 * $i, 'H:i');
			$times[] = rgmdate($today + 1800 * $i, 'H:i');
		}

		$this->view->set('times', $times);
	}

	protected function _before_action($action) {

		if (! parent::_before_action($action)) {
			return false;
		}

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.campaign.setting', 'oa');
		return true;
	}

	protected function _after_action($action) {

		if (! parent::_after_action($action)) {
			return false;
		}

		return true;
	}

	/**
	 * 发送图文提醒消息
	 *
	 * @param array $act
	 * @return boolean
	 */
	protected function _to_queue($act, $deps) {

		// 获取agentid
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		startup_env::set('agentid', $plugins[$this->_p_sets['pluginid']]['cp_agentid']);

		if (! $deps) { // 如果是发给全公司
			$touser = '@all';
			$toparty = '';
		} else {
			$serv_d = &service::factory('voa_s_oa_common_department', array('pluginid' => 0));
			$depms = $serv_d->fetch_all_by_key($deps);
			$toparty = implode('|', array_column($depms, 'cd_qywxid'));
			$touser = '';
		}

		// 组织查看链接
		$viewurl = '';
		$this->get_view_url($viewurl, $act['id']);

		$msg_title = '您收到一条活动信息';
		$msg_desc = '主题：' . rhtmlspecialchars($act['subject']);
		$msg_picurl = voa_h_attach::attachment_url($act['cover'], 0);

		// 发送消息
		voa_h_qymsg::push_news_send_queue($this->session, $msg_title, $msg_desc, $viewurl, $touser, $toparty, $msg_picurl);

		return true;
	}

	/**
	 * 获取查看详情的url
	 *
	 * @param string $url url地址
	 * @param int $af_id 审批信息id
	 * @return boolean
	 */
	protected function get_view_url(&$url, $id) {

		// 组织查看链接
		$scheme = config::get('voa.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($scheme.$this->_setting['domain'].'/frontend/campaign/view/?id='.$id.'&pluginid='.startup_env::get('pluginid'));

		return true;
	}
}
