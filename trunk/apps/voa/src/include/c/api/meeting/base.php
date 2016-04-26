<?php
/**
 * voa_c_api_meeting_base
 * 会议基础控制器
 * $Author$
 * $Id$
 */
class voa_c_api_meeting_base extends voa_c_api_base {

	/** 会议状态 */
	const ST_NORMAL = 0;
	const ST_FIN = 1;
	const ST_CANCEL = 2;
	/** 星期信息数组 */
	protected $_weeknames = array('星期天', '星期一', '星期二', '星期三', '星期四', '星期五', '星期六');
	/** 房间信息 */
	protected $_rooms = array();
	/** 状态信息数组 */
	protected $_st_tips = array(
		0 => '正常',
		1 => '已结束',
		2 => '已取消'
	);

	public function __construct() {
		parent::__construct();
	}

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$this->_rooms = voa_h_cache::get_instance()->get('plugin.meeting.room', 'oa');

		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.meeting.setting', 'oa');

		/** 取应用插件信息 */
		$pluginid = $this->_p_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		if (array_key_exists($pluginid, $plugins)) {
			$this->_plugin = $plugins[$pluginid];
			startup_env::set('agentid', $this->_plugin['cp_agentid']);
			/** 加载提示语言 */
			language::load_lang($this->_plugin['cp_identifier']);
		}

		return true;
	}


	/**
	 * 获取会议室配置
	 */
	public static function fetch_cache_meeting_room() {
		$serv = &service::factory('voa_s_oa_meeting_room', array('pluginid' => startup_env::get('pluginid')));
		return $serv->fetch_all();
	}

	/**
	 * 获取会议配置信息
	 */
	public static function fetch_cache_meeting_setting() {
		$serv = &service::factory('voa_s_oa_meeting_setting', array('pluginid' => startup_env::get('pluginid')));
		$data = $serv->fetch_all();
		$arr = array();
		foreach ($data as $v) {
			if (voa_d_oa_common_setting::TYPE_ARRAY == $v['ms_type']) {
				$arr[$v['ms_key']] = unserialize($v['ms_value']);
			} else {
				$arr[$v['ms_key']] = $v['ms_value'];
			}
		}

		return $arr;
	}

	/** 判断当前值是否为空, 或者状态是否正常 */
	protected function _meeting_is_valid($meeting) {
		/** 正常状态值 */
		$sts = array(voa_d_oa_meeting::STATUS_NORMAL, voa_d_oa_meeting::STATUS_UPDATE);
		if (empty($meeting) || !in_array($meeting['mt_status'], $sts) || $meeting['mt_endtime'] < startup_env::get('timestamp') / 1000) {
			return false;
		}

		return true;
	}


	/**
	 * 获取查看详情的url
	 * @param string $url url地址
	 * @param int $mt_id 微信墙信息id
	 * @return boolean
	 */
	public function get_view_url(&$url, $mt_id) {
		/** 组织查看链接 */
		$scheme = config::get('voa.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($scheme.$this->_setting['domain'].'/meeting/view/'.$mt_id.'?pluginid='.startup_env::get('pluginid'));

		return true;
	}

	/**
	 * 更新草稿
	 * @param array $cc_uids 抄送人uids
	 */
	protected function _update_draft($cc_uids = array()) {
		$serv = &service::factory('voa_s_oa_meeting_draft', array('pluginid' => startup_env::get('pluginid')));
		$mtd_id = (int)$this->request->get('mtd_id');
		if (0 < $mtd_id) {
			$serv->update(array(
				'mtd_message' => '',
				'mtd_cc_uid' => implode(',', array_diff($cc_uids, array($this->_user['m_uid'])))
			), array('mtd_id' => $mtd_id, 'm_openid' => $this->_user['m_openid']));
		} else {
			$serv->insert(array(
				'm_openid' => $this->_user['m_openid'],
				'mtd_cc_uid' => implode(',', array_diff($cc_uids, array($this->_user['m_uid'])))
			));
		}

		return true;
	}

	/**
	 * 获取草稿信息
	 * @param array &$ret 草稿内容
	 */
	protected function _get_draft(&$ret) {
		$serv_dr = &service::factory('voa_s_oa_meeting_draft', array('pluginid' => startup_env::get('pluginid')));
		$this->_draft = $serv_dr->get_by_openid($this->_user['m_openid']);
		if (empty($this->_draft)) {
			return true;
		}

		$this->view->set('mtd_id', $this->_draft['mtd_id']);
		/** 整理段落序号 */
		$msg = '';
		if (!empty($this->_draft['mtd_message'])) {
			$arr = explode(config::get(startup_env::get('app_name').'.page_break'), $this->_draft['mtd_message']);
			foreach ($arr as $k => $v) {
				$msg .= ($k + 1).' '.$v."\n";
			}
		}

		/** 取最近一次操作相关人员 */
		$uids = array();
		if (!empty($this->_draft['mtd_cc_uid'])) {
			$uids = explode(',', $this->_draft['mtd_cc_uid']);
		}

		/** 取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));
		$users = $serv_m->fetch_all_by_ids($uids);

		$ret = array('ccusers' => $users, 'message' => $msg);
		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}


}
