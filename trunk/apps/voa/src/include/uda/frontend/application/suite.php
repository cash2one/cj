<?php
/**
 * suite.php
 * 套件方式开启应用
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_application_suite extends voa_uda_frontend_application_base {

	/** 待操作的插件信息 */
	protected $_plugin = array();
	protected $_uda_open = null;

	public function __construct() {
		parent::__construct();

		if ($this->_uda_open === null) {
			$this->_uda_open = &uda::factory('voa_uda_frontend_application_open');
		}
	}

	/**
	 * 开启应用
	 * @param number $cp_pluginid
	 * @param string $qywx_application_agentid 该应用对应的企业微信应用agentid
	 * @param string $suiteid 套件ID
	 * @param string $logo_file 指定logo图片本地路径
	 * @return boolean
	 */
	public function open($cp_pluginid, $qywx_application_agentid, $suiteid, $appid = 0, $logo_file = '') {

		$qywx_application_agent = array(
			'agentid' => $qywx_application_agentid,// 套件ID
			'suiteid' => $suiteid,// agentid 微信绑定的应用ID
			'appid' => $appid
		);
		// 开通成功
		if ($this->_to_qywxsuite_open($cp_pluginid, $qywx_application_agent, $logo_file)) {
			return true;
		}
		// 使用套件通知微信企业平台开通失败
		if (empty($this->errcode)) {
			$this->errmsg(10011, '开通应用发生通讯错误');
		}

		// 写入日志
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$logs = array(
			'application open failed:',
			$sets['domain'],
			$cp_pluginid,
			$qywx_application_agentid,
			$suiteid,
			$logo_file,
			'errcode:'.$this->errcode,
			print_r($qywx_application_agent, true)
		);
		logger::error(implode('|', $logs));

		return false;
	}

	/**
	 * 使用套件方式开启
	 * @param number $cp_pluginid
	 * @param array $qywx_application_agent <strong>(应用结果)</strong> 应用型代理信息
	 * + suiteid 套件ID
	 * + agentid 微信绑定的应用ID
	 * @param number $suiteid 应用套件ID
	 * @return boolean
	 */
	protected function _to_qywxsuite_open($cp_pluginid, &$qywx_application_agent, $logo_file = '') {

		if (!$this->check_status_change_expire($cp_pluginid)) {
			// 检查该应用更改状态是否频繁
			return false;
		}

		// 系统设置变量
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		// 当前应用信息
		$plugin = $this->get_plugin($cp_pluginid);

		// logo media 信息
		/**$media = array();
		// logo 本地路径
		$local_file = '';
		// 如果指定了logo路径则使用指定路径
		if ($logo_file) {
			$local_file = $logo_file;
		}

		// 上传logo到微信企业媒体中心
		if ($local_file) {
			$serv = voa_wxqy_service::instance();
			if (!$serv->upload_media($media, 'image', $local_file, $this->errcode, $this->errmsg)) {
				return false;
			}
		} else {
			$media['media_id'] = 0;
		}*/

		// 判断应用是否开启了上报地理位置权限
		$report_location_flag = 0;

		// 找到对应的应用套件信息
		$s_suite = new voa_s_oa_suite();
		$suite = $s_suite->fetch_by_suiteid($qywx_application_agent['suiteid']);
		if (empty($suite)) {
			$this->errmsg('2001', '无法获取应用套件信息');
			return false;
		}
		// 授权的应用信息
		$agent_info = array();
		$authinfo = @unserialize($suite['authinfo']);

		// by zhuxun, 如果是消息服务, 则构造一个 agent 信息
		$noagent = config::get(startup_env::get('app_name').'.suite.noagent');
		if (in_array($qywx_application_agent['suiteid'], $noagent)) {
			$authinfo['auth_info']['agent'] = array(
				array('agentid' => 9999, 'appid' => 1)
			);
		}
		// end.

		if (empty($authinfo['auth_info']['agent'])) {
			$this->errmsg('2002', '无法获取授权应用列表信息');
			return false;
		}
		foreach ($authinfo['auth_info']['agent'] as $_agent) {
			if ($_agent['agentid'] == $qywx_application_agent['agentid']) {
				$agent_info = $_agent;
				break;
			}
		}
		if (empty($agent_info)) {
			$this->errmsg('2003', '无法获取授权应用信息');
			return false;
		}

		// 判断是否给予了上报地理位置权限
		if (isset($agent_info['api_group']) && is_array($agent_info['api_group']) && in_array('get_location', $agent_info['api_group'])) {
			$report_location_flag = 1;
		}

		// 应用信息
		$agent = array(
			'agentid' => $qywx_application_agent['agentid'],
			//'report_location_flag' => $report_location_flag,
			//'logo_mediaid' => $media['media_id'],
			'name' => $plugin['cp_name'],
			'description' => $plugin['cp_description'],
			'redirect_domain' => $sets['domain'],
		);
		// 地理位置标识
		if (0 != $report_location_flag && in_array($cp_pluginid, array(14, 23))) {
			$agent['report_location_flag'] = 1;
		}

		// 引入应用套件服务
		$wxserv = voa_wxqysuite_service::instance();
		// 设置微信应用
		// by zhuxun, 判断是否需要调用 set_agent 方法
		$noagent = config::get(startup_env::get('app_name').'.suite.noagent');
		$appagent = config::get(startup_env::get('app_name').'.suite.appagent');

		//if (!$wxserv->set_agent($agent, $qywx_application_agent['suiteid'])) {
		if (!in_array($qywx_application_agent['suiteid'], $noagent)
				&& !in_array("{$qywx_application_agent['suiteid']}-{$qywx_application_agent['appid']}", $appagent)
				&& !$wxserv->set_agent($agent, $qywx_application_agent['suiteid'])) {
		// end.
			$this->errmsg(10041, '使用授权信息关联应用发生错误');
			return false;
		}

		// 通知标记畅移后台该应用为待启用状态，但暂时不处理本地应用状态
		$cyea_id = 0;
		if ($this->_uda_open->_to_cy_open($cp_pluginid, $cyea_id, true) === false) {
			// 通知开通失败
			if (empty($this->_uda_open->error)) {
				$this->errmsg(10042, '启用应用发生通讯错误');
				return false;
			}
			$this->errmsg($this->_uda_open->errcode, $this->_uda_open->errmsg);
			return false;
		}

		// 正式开启本地应用
		$qywx_application_agent['cyea_id'] = $cyea_id;
		if ($this->_uda_open->open_confirm($cp_pluginid, $qywx_application_agent) === false) {
			// 开通本地失败
			if (empty($this->_uda_open->error)) {
				$this->errmsg(10043, '开启本地应用发生错误');
				return false;
			}
			$this->errmsg($this->_uda_open->errcode, $this->_uda_open->errmsg);
			return false;
		}

		// 通知畅移后台正式开启
		if (!$this->vchangyi_application_api($cp_pluginid, 'confirm_open')) {
			return false;
		}

		return true;
	}

}
