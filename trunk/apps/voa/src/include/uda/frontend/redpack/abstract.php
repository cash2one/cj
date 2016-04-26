<?php
/**
 * voa_uda_frontend_redpack_abstract
 * 统一数据访问/红包/基类
 * $Author$
 * $Id$
 */

class voa_uda_frontend_redpack_abstract extends voa_uda_frontend_base {
	// 全局配置信息
	protected $_sets = array();
	// 插件配置
	protected $_p_sets = array();
	// 红包主表
	protected $_serv_rp = null;
	// 红包领取日志表
	protected $_serv_rplog = null;

	public function __construct() {

		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.redpack.setting', 'oa');
		if ($this->_serv === null) {
			$this->_serv_rp = new voa_s_oa_redpack();
			$this->_serv_rplog = new voa_s_oa_redpack_log();
		}
	}

	/**
	 * 判断用户是否有权限领取红包
	 * @param int $redpack_id 红包id
	 * @param int $uid 用户uid
	 */
	public function has_privilege($redpack_id, $uid) {

		// 判断用户是否有权限
		$serv_rpm = &service::factory('voa_s_oa_redpack_mem');
		if ($ct = $serv_rpm->count_by_redpackid_uid($redpack_id, $uid)) {
			return true;
		}

		// 读取用户的所有部门
		$serv_dp = &service::factory('voa_s_oa_member_department');
		$dpids = $serv_dp->fetch_all_by_uid($uid);

		// 判断用户所在部门是否有权限
		$serv_rpd = &service::factory('voa_s_oa_redpack_department');
		if ($ct = $serv_rpd->count_by_redpackid_cdid($redpack_id, $dpids)) {
			return true;
		}

		return false;
	}

	/**
	 * 推送消息通知用户
	 *
	 * @param array $redpack
	 * @return void
	 */
	public function push_wx_msg($redpack, $m_uids, $cd_ids, $session) {

		// demo体验号不发送提醒
		if (strpos(strtolower(controller_request::get_instance()->server('SERVER_NAME')), 'demo') !== false) {
			return;
		}

		// 获取 agent_id
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		$agent_id = $plugins[$this->_p_sets['pluginid']]['cp_agentid'];
		$scheme = config::get(startup_env::get('app_name') . '.oa_http_scheme');
		$viewurl = $scheme . $this->_sets['domain'] . '/frontend/redpack/view?redpack_id=' . $redpack['id'] . '&pluginid=' . $this->_p_sets['pluginid'];
		if (empty($m_uids) && empty($cd_ids)) {
			$touser = '@all';
			$toparty = '';
		} else {
			$touser = $m_uids;
			$toparty = $cd_ids;
		}

		$type2tip = array();
		$type2tip[voa_d_oa_redpack::TYPE_APPOINT] = '定点红包';
		$type2tip[voa_d_oa_redpack::TYPE_RAND] = '随机红包';
		$type2tip[voa_d_oa_redpack::TYPE_AVERAGE] = '均分红包';
		$type = ! isset($type2tip[$redpack['_type']]) ? voa_d_oa_redpack::TYPE_RAND : $redpack['_type'];
		$msg_title = $type2tip[$type];
		// 根据类型, 组成不同的语言
		if (voa_d_oa_redpack::TYPE_RAND == $type) {
			$msg_desc = '总金额: ' . number_format($redpack['total'] / 100, 2) . "元\n\n" . "对　象: 全体人员";
		} else {
			$msg_desc = '单个金额: ' . number_format($redpack['total'] / ($redpack['redpacks'] * 100), 2) . "元\n\n" . "对　　象: " . (voa_d_oa_redpack::TYPE_APPOINT == $type ? '指定人员' : '全体人员');;
		}

		$msg_desc .= "\n\n" . $redpack['wishing'];
		$msg_url = $viewurl;
		$msg_picurl = '';
		// 发送消息
		voa_h_qymsg::push_news_send_queue($session, $msg_title, $msg_desc, $msg_url, $touser, $toparty, $msg_picurl, $agent_id);
	}

}
