<?php
/**
 * voa_uda_frontend_dailyreport_base
 * 统一数据访问/日报应用/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_dailyreport_base extends voa_uda_frontend_base {

	/**
	 * 参与人员状态映射
	 * @var array
	 */
	public $mem_status = array(
		'normal' => voa_d_oa_dailyreport_mem::STATUS_NORMAL,
		'update' => voa_d_oa_dailyreport_mem::STATUS_UPDATE,
		'carbon_copy' => voa_d_oa_dailyreport_mem::STATUS_CARBON_COPY,
		'remove' => voa_d_oa_dailyreport_mem::STATUS_REMOVE
	);

	/**
	 * 应用信息
	 */
	protected $_plugin = array();

	/**
	 * 配置信息
	 */
	protected $_sets = array();

	public function __construct() {
		parent::__construct();
		$this->_sets = voa_h_cache::get_instance()->get('plugin.dailyreport.setting', 'oa');

		// 取应用插件信息
		$pluginid = $this->_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		// 如果应用信息不存在
		if (! array_key_exists($pluginid, $plugins)) {
			$this->errcode = 1001;
			$this->errmsg = '应用信息丢失，请重新开启';
			return false;
		}
		// 获取应用信息
		$this->_plugin = $plugins[$pluginid];
		startup_env::set('agentid', $this->_plugin['cp_agentid']);
		// 判断应用是否关闭
		if ($this->_plugin['cp_available'] != voa_d_oa_common_plugin::AVAILABLE_OPEN) {
			$this->errcode = 1002;
			$this->errmsg = '本应用尚未开启 或 已关闭，请联系管理员启用后使用';
			return false;
		}
	}

	/**
	 * 验证报告时间是否正确
	 * @param int $reporttime
	 * @return boolean
	 */
	public function val_reporttime(&$reporttime) {
		$reporttime = rstrtotime($reporttime);
		if (0 >= $reporttime) {
			$this->errmsg(110, 'reporttime_invalid');
			return false;
		}

		return true;
	}

	/**
	 * 验证标题
	 *
	 * @param string $str
	 * @return boolean
	 */
	public function val_subject(&$str) {
		$str = trim($str);
		if (empty($str)) {
			$this->errmsg(100, 'subject_short');
			return false;
		}

		return true;
	}

	/**
	 * 验证内容
	 *
	 * @param string $str
	 * @return boolean
	 */
	public function val_message(&$str) {
		$str = trim($str);
		if (empty($str)) {
			$this->errmsg(101, 'message_too_short');
			return false;
		}

		return true;
	}

	/**
	 * 验证审批人
	 *
	 * @param string $uid
	 * @return boolean
	 */
	public function val_approveuid(&$uid) {
		if (strpos($uid, ',') !== false) {
			$uids = array();
			foreach (explode(',', $uid) as $_uid) {
				$_uid = (int)$_uid;
				if ($_uid > 0 && !isset($uids[$_uid])) {
					$uids[$_uid] = $_uid;
				}
			}
			if (empty($uids)) {
				$this->errmsg(1021, 'approveuid_error');
				return false;
			}
			$uid = $uids;
			return true;
		}
		$uid = (int) $uid;
		if (0 >= $uid) {
			$this->errmsg(102, 'approveuid_error');
			return false;
		}

		return true;
	}

	/**
	 * 验证抄送人
	 *
	 * @param string $uidstr
	 * @param array $uids
	 * @return boolean
	 */
	public function val_carboncopyuids($uidstr, &$uids) {
		$uidstr = (string) $uidstr;
		$uidstr = trim($uidstr);
		$tmps = empty($uidstr) ? array() : explode(',', $uidstr);
		$uids = array();
		foreach ($tmps as $uid) {
			$uid = (int) $uid;
			if (0 < $uid) {
				$uids[$uid] = $uid;
			}
		}

		return true;
	}

	/**
	 * 发送工作报告微信图文消息
	 *
	 * @param array $dailyreport 日报详情数据
	 * @param string $type 消息类型: new=新报告、reply=评论
	 * @param number $senderid 发送人ID
	 * @param bool $auth_url url是否加入认证编码，默认: 否
	 */
	public function viewurl(&$url, $dr_id, $auth_url = false) {
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$pluginid = $this->_sets['pluginid'];
		$http = config::get(startup_env::get('app_name') . '.oa_http_scheme');
		$url = $http . $sets['domain'] . '/dailyreport/view/' . $dr_id . '?pluginid=' . $pluginid;

		if ($auth_url) {
			$url = voa_wxqy_service::instance()->oauth_url($url);
		}

		return true;
	}

	/**
	 * 发送图文格式的微信消息
	 *
	 * @author Deepseath @ 20150326
	 * @param array $session_object seesion对象
	 * @param array $dailyreport 日报详情数据
	 * @param string $type 消息类型: new=新日报,reply=评论,forward=转发
	 * @param number $senderid 消息发送者的uid 接收人列表
	 * @return true;
	 */
	public function send_wxqymsg_news($session_obj, $dailyreport, $type, $senderid, $userlist = array()) {

		// 构造日报查看链接/
		$viewurl = '';
		$this->viewurl($viewurl, $dailyreport['dr_id']);

		// 整理需要接收消息的用户
		$serv_drm = &service::factory('voa_s_oa_dailyreport_mem', array(
			'pluginid' => $this->_sets['pluginid']
		));

		// 待接收人uid列表
		$uids = array();
		if (empty($userlist)) {
			foreach ($serv_drm->fetch_by_dr_id($dailyreport['dr_id']) as $_drm) {
				// 不发消息给当前发送者
				if ($senderid == $_drm['m_uid']) {
					continue;
				}
				$uids[] = $_drm['m_uid'];
			}
		} else {
			foreach ($userlist as $_u) {
				// 不发消息给当前发送者
				if ($senderid == $_u['m_uid']) {
					continue;
				}
				$uids[] = $_u['m_uid'];
			}
		}
		// 不存在接收人
		if (empty($uids)) {
			return true;
		}

		// 取出报告的发送者和接受者列表
		// 报告发布人列表
		$reporter = array();
		// 报告接收人列表
		$cc_list = array();
		foreach ($uids as $_uid) {
			if ($dailyreport['m_uid'] == $_uid) {
				$reporter[] = $_uid;
			} else {
				$cc_list[] = $_uid;
			}
		}

		// 消息标题
		$msg_title = '';
		// 消息内容描述
		$msg_desc = '';
		// 消息阅读URL
		$msg_url = '';

		// 当前日报类型
		$dr_type = isset($this->_sets['daily_type'][$dailyreport['dr_type']])
			? $this->_sets['daily_type'][$dailyreport['dr_type']][0] : '报告';

		// by zhuxun, 获取发送者
		$sender = voa_h_user::get($senderid);
		// 配置日期显示
		$weeks = config::get('voa.misc.weeknames');
		list($m, $d, $w) = explode('-', rgmdate($dailyreport['dr_reporttime'], "n-j-w"));
		$pm_params = serialize(array(
			'identifier' => $this->_plugin['cp_identifier'],
			'pluginid' => $this->_plugin['cp_pluginid'],
			'id' => $dailyreport['dr_id']
		));
		// end

		// 确定消息正文内容
		$content = array();
		if ($type == 'new') {
			// by zhuxun, 此操作移到前面操作
			// 配置日期显示
			//$weeks = config::get('voa.misc.weeknames');
			//list($m, $d, $w) = explode('-', rgmdate($dailyreport['dr_reporttime'], "n-j-w"));
			// end.
			$msg_title = '收到一份' . $dr_type;
			$msg_desc = "{$m}月{$d}日{$weeks[$w]}提交的{$dr_type}\n";
			$msg_desc .= "来自: {$dailyreport['m_username']}\n";
			$msg_desc .= "时间: " . rgmdate(startup_env::get('timestamp'), 'Y-m-d H:i');
			$msg_url = $viewurl;

			// by zhuxun, 发站内消息
			$pm_title = $msg_title;
			$pm_message = "收到一份报告【{$sender['m_username']} {$m}月{$d}日{$weeks[$w]}提交的{$dr_type}】";
			// end
		} elseif ($type == 'reply') {

			$msg_title = "你的{$dr_type}收到一条评论";
			$msg_desc = "来自: " . startup_env::get('wbs_username') . "\n";
			$msg_desc .= "时间: " . rgmdate(startup_env::get('timestamp'), 'Y-m-d H:i');
			$msg_url = $viewurl;
			voa_h_qymsg::push_news_send_queue($session_obj, $msg_title, $msg_desc, $msg_url, $reporter);
			// by zhuxun, 消息推入站内消息
			$pm_title = $msg_title;
			$pm_message = startup_env::get('wbs_username') . "评论了报告【我的 " . rgmdate(startup_env::get('timestamp'), 'Y-m-d H:i') . " 日报】";
			voa_h_qymsg::add_to_pm($sender, $reporter, $pm_title, $pm_message, $pm_params, $this->_plugin['cp_pluginid']);
			// end

			$msg_title = "与你有关的{$dr_type}收到一条评论";
			$msg_desc = "来自: " . startup_env::get('wbs_username') . "\n";
			$msg_desc .= "时间: " . rgmdate(startup_env::get('timestamp'), 'Y-m-d H:i');
			$msg_url = $viewurl;
			voa_h_qymsg::push_news_send_queue($session_obj, $msg_title, $msg_desc, $msg_url, $cc_list);
			// by zhuxun, 消息推入站内消息
			$pm_title = $msg_title;
			$pm_message = "【{$dailyreport['m_username']} " . rgmdate(startup_env::get('timestamp'), 'Y-m-d H:i') . " 日报】有一条新的评论";
			voa_h_qymsg::add_to_pm($sender, $cc_list, $pm_title, $pm_message, $pm_params, $this->_plugin['cp_pluginid']);
			// end

			return true;

		} elseif ($type == 'forward') {

			$msg_title = "收到" . startup_env::get('wbs_username') . '转发的' . $dr_type;
			$msg_desc = "来自: " . startup_env::get('wbs_username') . "\n";
			$msg_desc .= "时间: " . rgmdate(startup_env::get('timestamp'), 'Y-m-d H:i');
			if (! empty($dailyreport['remark'])) {
				$msg_desc .= "\n备注: " . $dailyreport['remark'];
			}

			$msg_url = $viewurl;
			// by zhuxun, 发站内消息
			$pm_title = $msg_title;
			$pm_message = "报告【{$dailyreport['m_username']} {$m}月{$d}日{$weeks[$w]}提交的{$dr_type}】被".startup_env::get('wbs_username')."转发";
			// end
		}

		if (empty($msg_desc)) {
			logger::error('未知类型的消息|' . $dailyreport['dr_id']);
			return false;
		}

		voa_h_qymsg::push_news_send_queue($session_obj, $msg_title, $msg_desc, $msg_url, $uids);
		// by zhuxun, 消息推入站内消息
		voa_h_qymsg::add_to_pm($sender, $uids, $pm_title, $pm_message, $pm_params, $this->_plugin['cp_pluginid']);
		// end

		return true;
	}

}
