<?php
/**
 * WxqyMsg.class.php
 * 微信消息发送
 * $Author$
 */

namespace Common\Common;
use Think\Log;
use Common\Common\Cache;
use Common\Common\User;
use Common\Common\Wxqy\Service;

class WxqyMsg {
	/** 文本 消息类型定义名 */
	const MSGTYPE_TEXT = 'text';
	/** 图片 消息类型定义名 */
	const MSGTYPE_IMAGE = 'image';
	/** 音频 消息类型定义名 */
	const MSGTYPE_VOICE = 'voice';
	/** 视频 消息类型定义名 */
	const MSGTYPE_VIDEO = 'video';
	/** 图文 消息类型定义名 */
	const MSGTYPE_NEWS = 'news';
	// 文件
	const MSGTYPE_FILE = 'file';
	// 站点配置
	protected $_setting = array();
	// 新闻公告的插件名称
	protected $_news_name = 'News';
	// 插件列表
	protected $_pluginid2plugin = array();
	protected $_agentid2plugin = array();
	// 默认数据
	protected $_def_pluginid = 0;
	protected $_def_agentid = 0;

	// 实例化
	public static function &instance() {

		static $instance;
		if (empty($instance)) {
			$instance = new self();
		}

		return $instance;
	}

	// 初始化
	public function __construct() {

		// 获取配置
		$cache = &Cache::instance();
		$this->_setting = $cache->get('Common.setting');
		// 插件列表
		$cache = &\Common\Common\Cache::instance();
		$plugins = $cache->get('Common.plugin');
		// 整理数据
		foreach ($plugins as $_plugin) {
			$this->_pluginid2plugin[$_plugin['cp_pluginid']] = $_plugin;
			if (!empty($_plugin['cp_agentid'])) {
				$this->_agentid2plugin[$_plugin['cp_agentid']] = $_plugin;
			}
		}
	}

	/**
	 * 发送文本消息
	 * @param string $msg 消息内容
	 * @param array $uids 用户uid
	 * @param string $cdids 部门id
	 * @param number $agentid 企业号应用ID
	 * @param number $pluginid 插件ID
	 */
	public function send_text($msg, $uids, $cdids = '', $agentid = 0, $pluginid = 0) {

		$this->_send(self::MSGTYPE_TEXT, $msg, $uids, $cdids, $agentid, $pluginid);
		return true;
	}

	/**
	 * 发送图片消息
	 * @param string $mediaid 图片media id
	 * @param array $uids 用户uid
	 * @param string $cdids 部门id
	 * @param number $agentid 企业号应用ID
	 * @param number $pluginid 插件ID
	 */
	public function send_image($mediaid, $uids, $cdids = '', $agentid = 0, $pluginid = 0) {

		$message = array('media_id' => $mediaid);
		$this->_send(self::MSGTYPE_IMAGE, $message, $uids, $cdids, $agentid, $pluginid);
		return true;
	}

	/**
	 * 发送文件消息
	 * @param string $mediaid 文件media id
	 * @param array $uids 用户uid
	 * @param string $cdids 部门id
	 * @param number $agentid 企业号应用ID
	 * @param number $pluginid 插件ID
	 */
	public function send_file($mediaid, $uids, $cdids = '', $agentid = 0, $pluginid = 0) {

		$message = array('media_id' => $mediaid);
		$this->_send(self::MSGTYPE_FILE, $message, $uids, $cdids, $agentid, $pluginid);
		return true;
	}

	/**
	 * 发送声音消息
	 * @param string $mediaid 声音media id
	 * @param array $uids 用户uid
	 * @param string $cdids 部门id
	 * @param number $agentid 企业号应用ID
	 * @param number $pluginid 插件ID
	 */
	public function send_voice($mediaid, $uids, $cdids = '', $agentid = 0, $pluginid = 0) {

		$message = array('media_id' => $mediaid);
		$this->_send(self::MSGTYPE_VOICE, $message, $uids, $cdids, $agentid, $pluginid);
		return true;
	}

	/**
	 * 发送视频消息
	 * @param string $mediaid 视频media id
	 * @param array $uids 用户uid
	 * @param string $cdids 部门id
	 * @param number $agentid 企业号应用ID
	 * @param number $pluginid 插件ID
	 */
	public function send_video($mediaid, $title, $description, $uids, $cdids = '', $agentid = 0, $pluginid = 0) {

		$message = array(
			'media_id' => $mediaid,
			'title' => $title,
			'description' => $description
		);
		$this->_send(self::MSGTYPE_VIDEO, $message, $uids, $cdids, $agentid, $pluginid);
		return true;
	}

	/**
	 * 发送企业消息
	 * @param string $title 消息标题
	 * @param string $desc 消息内容
	 * @param string $url 消息的链接地址
	 * @param mixed $uids 目标用户
	 * @param mixed $cdids 目标部门
	 * @param string $picurl 企业消息的图片
	 * @param int $agentid 应用ID
	 * @param int $pluginid 插件id
	 */
	public function send_news($title, $desc, $url, $uids, $cdids = '', $picurl = '', $agentid = 0, $pluginid = 0) {

		// 重组消息体
		$message = $this->__format_news($title, $desc, $url, $picurl);
		$this->_send(self::MSGTYPE_NEWS, $message, $uids, $cdids, $agentid, $pluginid);
		return true;
	}

	/**
	 * 发送企业消息
	 * @param string $type 消息类型
	 * @param mixed $message 消息内容
	 * @param mixed $uids 目标用户
	 * @param mixed $cdids 目标部门
	 * @param string $picurl 企业消息的图片
	 * @param int $agentid 应用ID
	 * @param int $pluginid 插件id
	 */
	public function _send($type, $message, $uids, $cdids = '', $agentid = 0, $pluginid = 0) {

		$openids = array();
		$departmentids = array();
		// 检查发送目标和应用
		if (!$this->_check_send($uids, $cdids, $openids, $departmentids)) {
			return false;
		}

		try {
			// 临时切换插件信息
			$this->chang_plugin($agentid, $pluginid);

			// 如果是demo站，发送给全体人员且不是新闻公告应用，则不发消息
			if ($this->_is_demo() && $this->_news_name != cfg('PLUGIN_IDENTIFIER')
					&& ('@all' == $openids || '@all' == $departmentids)) {
				return true;
			}

			// 推送消息到微信
			$this->_send_to_wx($type, $message, cfg('AGENT_ID'), $openids, $departmentids);
			// 记录到队列表
			$data = array(
				'cp_pluginid' => cfg('PLUGIN_ID'),
				'mq_touser' => $openids,
				'mq_toparty' => $departmentids,
				'mq_msgtype' => $type,
				'mq_agentid' => cfg('AGENT_ID'),
				'mq_message' => is_array($message) ? serialize($message) : $message,
				'mq_status' => \Common\Model\MsgQueueModel::ST_UPDATE
			);
			$serv_mq = D('Common/MsgQueue', 'Service');
			$serv_mq->insert($data);

			// 重置 pluginid, agentid 常量
			$this->reset_plugin();
		} catch (\Think\Exception $e) {
			$this->reset_plugin();
			return false;
		} catch (\Exception $e) {
			$this->reset_plugin();
			return false;
		}

		return true;
	}

	/**
	 * 发送消息
	 * @param mixed $data 消息内容
	 */
	protected function _send_to_wx($type, $message, $agentid, $userids, $partys) {

		\Think\Log::record('message: ' . var_export($message, true));
		if (empty($userids) && empty($partys)) {
			\Think\Log::record('userids or partys is empty.');
			return false;
		}

		// 如果是debug域名, 则略过
		$debug_domain = cfg('DEBUG_DOMAIN');
		if (!empty($debug_domain) && preg_match("/" . preg_quote($debug_domain) . "$/i", I('server.HTTP_HOST'))) {
			return true;
		}

		// 根据不同的类型, 发送不同的消息
		switch ($type) {
			case self::MSGTYPE_IMAGE:
				Service::instance()->post_image($message, $agentid, $userids, $partys);
				break;
			case self::MSGTYPE_FILE:
				Service::instance()->post_file($message, $agentid, $userids, $partys);
				break;
			case self::MSGTYPE_NEWS:
				Service::instance()->post_news($message, $agentid, $userids, $partys);
				break;
			case self::MSGTYPE_TEXT:
				Service::instance()->post_text($message, $agentid, $userids, $partys);
				break;
			case self::MSGTYPE_VIDEO:
				Service::instance()->post_video($message, $agentid, $userids, $partys);
				break;
			case self::MSGTYPE_VOICE:
				Service::instance()->post_voice($message, $agentid, $userids, $partys);
				break;
			default: // 默认
				\Think\Log::record('type error: ' . $type);
				return false;
				break;
		}

		return true;
	}

	/**
	 * 切换插件
	 * @param int $agentid 微信应用id
	 */
	public function chang_plugin($agentid, $pluginid) {

		$this->_def_pluginid = cfg('PLUGIN_ID');
		$this->_def_agentid = cfg('AGENT_ID');

		$pluginid = (int)$pluginid;
		$agentid = (int)$agentid;
		$plugin = array();
		// 根据插件id检查插件是否存在
		if (0 < $pluginid && !empty($this->_pluginid2plugin[$pluginid])) {
			$plugin = $this->_pluginid2plugin[$pluginid];
		} elseif (0 < $agentid && !empty($this->_agentid2plugin[$agentid])) { // 根据agentid检查
			$plugin = $this->_agentid2plugin[$agentid];
		}

		// 如果插件存在
		if (!empty($plugin)) {
			cfg('PLUGIN_ID', $plugin['cp_pluginid']);
			cfg('AGENT_ID', $plugin['cp_agentid']);
		}

		return true;
	}

	// 重置, 恢复默认数据
	public function reset_plugin() {

		cfg('PLUGIN_ID', $this->_def_pluginid);
		cfg('AGENT_ID', $this->_def_agentid);
		return true;
	}

	/**
	 * 检查发送信息
	 * @param array $uids 用户uid
	 * @param array $cdids 部门id
	 * @param string|array $openids 用户openid
	 * @param int|array $departmentids 企业号部门id
	 * @return boolean
	 */
	protected function _check_send($uids, $cdids, &$openids, &$departmentids) {

		// 如果是demo，且非新闻公告应用，则不发送给部门
		if ($this->_is_demo() && $this->_news_name == cfg('PLUGIN_IDENTIFIER')) {
			$cdids = '';
		}

		// 获取真实要接收的人员和部门列表
		if (!$this->_to_openid_departmentid($openids, $departmentids, $uids, $cdids)) {
			Log::record('uids:' . var_export($uids, true) . 'cdids:' . var_export($cdids, true), Log::ALERT);
			return false;
		}

		return true;
	}

	/**
	 * 重组微信企业号消息
	 * @param mixed $title 标题
	 * @param mixed $desc 描述
	 * @param mixed $url 链接
	 * @param mixed $picurl 图片
	 * @return array
	 */
	private static function __format_news($title, $desc, $url, $picurl) {

		// 单条图文消息
		if (is_scalar($title)) {
			return array(
				'title' => $title,
				'description' => $desc,
				'url' => $url,
				'picurl' => $picurl
			);
		}

		// 多条图文消息
		$msgs = array();
		foreach ($title as $_k => $_title) {
			$msgs[] = array(
				'title' => $_title,
				'description' => isset($desc[$_k]) ? $desc[$_k] : '',
				'url' => isset($url[$_k]) ? $url[$_k] : '',
				'picurl' => isset($picurl[$_k]) ? $picurl[$_k] : ''
			);
		}

		return $msgs;
	}

	/**
	 * 把uids/cdids转成openids/departmentids
	 * @param array $openids openid数组
	 * @param unknown $departmentids
	 * @param unknown $uids
	 * @param unknown $cdids
	 * @return boolean
	 */
	protected function _to_openid_departmentid(&$openids, &$departmentids, $uids, &$cdids) {

		// 如果设置小于0，或者@all，则发全部
		if ((is_scalar($uids) && (0 > $uids || false !== stripos($uids, '@all')))
				|| (is_scalar($cdids) && (0 > $cdids || false !== stripos($cdids, '@all')))) {
			$openids = '@all';
			$departmentids = '';
			return true;
		}

		// 如果接收人或者部门数组内存在负数，则发给所有人
		if ((is_array($uids) && false !== array_search('-1', $uids))
				|| (is_array($cdids) && false !== array_search('-1', $cdids))) {
			$openids = '@all';
			$departmentids = '';
			return true;
		}

		// 获取用户信息
		$members = User::instance()->list_by_uid($uids);
		// 获取部门信息
		$serv_dp = D('Common/CommonDepartment', 'Service');
		$departments = $serv_dp->list_by_pks($cdids);

		// 如果用户和部门都不存在, 则
		if (empty($members) && empty($departments)) {
			return false;
		}

		// 获取openid/departmentid
		$openids = implode('|', array_column($members, 'm_openid'));
		$departmentids = implode('|', array_column($departments, 'cd_qywxid'));

		return true;
	}

	// 如果是 demo
	protected function _is_demo() {

		return !empty($this->_setting['domain']) && 'demo.vchangyi.com' == $this->_setting['domain'];
	}

}
