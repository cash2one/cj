<?php
/**
 * voa_h_qymsg
 * 微信微信企业网关消息
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_h_qymsg {

	/** 微信企业应用类型：应用型 */
	const QYWX_TYPE_APPLICATION = 'application';
	/** 微信企业应用类型：会话型 */
	const QYWX_TYPE_SESSION = 'session';

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
	// 文件消息
	const MSGTYPE_FILE = 'file';

	/** 上传 图片 尺寸限制 */
	public static $upload_limit_image = 1048576;//1M = 1048576
	/** 上传 语音 尺寸限制 */
	public static $upload_limit_voice = 1048576;//1M = 1048576
	/** 上传 视频 尺寸限制 */
	public static $upload_limit_video = 10485760;//10M =10485760

	/** 上传 图片 格式要求 */
	public static $upload_type_image = array('jpg');
	/** 上传 音频 格式要求 */
	public static $upload_type_voice = array('amr', 'mp3');
	/** 上传 视频 格式要求 */
	public static $upload_type_video = array('mp4');

	/** 大图最佳尺寸宽度 */
	public static $image_big_width = 640;
	/** 大图最佳尺寸高度 */
	public static $image_big_height = 320;
	/** 小图最佳尺寸宽度 */
	public static $image_small_width = 200;
	/** 小图最佳尺寸高度 */
	public static $image_small_height = 200;

	/** 文本消息内容限制定义 */
	public static $text_limit = array(
			'title_length_min' => 1,//标题文字最小字符数
			'title_length_max' => 64,//标题文字最大字符数
			'content_length_min' => 1,//内容文字最小字符数
			'content_length_max' => 600,//内容文字最大字符数
	);
	/** 音频消息内容限制定义 */
	public static $voice_limit = array(
			'title_length_min' => 0,
			'title_length_max' => 64,
			'description_length_min' => 0,
			'description_length_max' => 600,
	);
	/** 视频消息内容限制定义 */
	public static $video_limit = array(
			'title_length_min' => 0,
			'title_length_max' => 64,
			'description_length_min' => 0,
			'description_length_max' => 600,
	);
	/** 图片消息内容限制定义 */
	public static $image_limit = array(
			'title_length_min' => 0,
			'title_length_max' => 64,
			'description_length_min' => 0,
			'description_length_max' => 600,
	);

	/** 企业微信应用代理，名称长度限制 */
	public static $qywx_app_name_length = array(
			'min' => 1,
			'max' => 10
	);
	/** 企业微信应用代理，描述长度限制 */
	public static $qywx_app_description_length = array(
			'min' => 1,
			'max' => 100
	);
	/** 企业微信应用代理，头像限制 */
	public static $qywx_app_avator = array(
			'max_size' => '1m',
			'file_type' => array('jpg', 'png', 'gif')
	);

	/** 当前媒体类型 */
	private $_media_type = '';

	/**
	 * 获取指定媒体类型的上传限制值
	 * @param string $mediaType
	 * @return number
	 */
	public static function get_conf_upload_limit($mediaType = '') {
		if (!$mediaType) {
			$mediaType = $this->_media_type;
		}
		$variable = 'upload_limit_'.$mediaType;
		if (isset(self::$$variable)) {
			return self::$$variable;
		} else {
			return 0;
		}
	}

	/**
	 * 获取指定媒体类型的上传格式要求
	 * @param string $mediaType
	 * @return string
	 */
	public static function get_conf_upload_type($mediaType = '') {
		if (!$mediaType) {
			$mediaType = $this->_media_type;
		}
		$variable = 'upload_type_'.$mediaType;
		if (isset(self::$$variable)) {
			return self::$$variable;
		} else {
			return 0;
		}
	}

	/**
	 * 返回图片最佳尺寸值
	 * @param string $sizeType set: 'small' or 'big'
	 * @param string $size set:'width' or 'height'
	 * @return string
	 */
	public static function get_conf_image_size($sizeType = 'big', $size = 'height') {
		if (strtolower($sizeType) == 'big') {
			return strtolower($size) == 'height' ? self::IMAGE_BIG_HEIGHT : self::IMAGE_BIG_WIDTH;
		} else {
			return strtolower($size) == 'height' ? self::IMAGE_SMALL_HEIGHT : self::IMAGE_SMALL_WIDTH;
		}
	}

	/**
	 * 返回指定消息类型的格式内容限制定义
	 * @param string $msgType
	 * @return array
	 */
	public static function get_conf_input_limit($msgType) {
		$variable = $msgType.'_limit';
		if (isset(self::$$variable)) {
			return self::$$variable;
		}
		return array();
	}

	/**
	 * 计算字符串长度（字符数）
	 * @param string $string
	 * @return number
	 */
	public static function strlen($string) {
		return mb_strlen($string, 'UTF-8');
	}

	/**
	 * 把待发送信息推入队列以实现消息发送
	 * 如需要真实发送消息，需要同时调用self::set_queue_session()方法
	 * @param array $data (引用结果)消息详情
	 * @return void
	 */
	public static function push_send_queue(&$data) {

		if (empty($data['cp_pluginid'])) {
			$data['cp_pluginid'] = startup_env::get('pluginid');
		}

		self::send_qymsg($data);
		$data['mq_status'] = voa_d_oa_msg_queue::STATUS_SUCCEED;

		$serv_sq_oa = &service::factory('voa_s_oa_msg_queue', array('pluginid' => 0));
		$data['mq_id'] = $serv_sq_oa->insert($data, true);
		return true;
	}

	/**
	 * 设置发送消息队列的ID到seesion以实现尽快的发送对应队列的消息
	 * @param array $mq_ids 消息队列ID
	 * @param object $session_obj session操作对象
	 * @return void
	 */
	public static function set_queue_session($mq_ids, $session_obj) {
		$session_obj->set('mq_ids', implode(',', $mq_ids));
	}

	/**
	 * 发送一条图文消息
	 * @param object $session_obj session对象
	 * @param string $msg_title 消息标题（建议64以内）
	 * @param string $msg_desc 消息摘要描述（建议500字以内）
	 * @param string $msg_url 消息链接地址
	 * @param string $touser 发送给目标人。member['m_openid']，多个之间使用“|”分隔，或者m_uid列表
	 * @param string $toparty 发送给目标部门，多个之间使用“|”分隔，多个之间使用“|”分隔，或者cd_uid列表
	 * @param string $msg_picurl 图片地址
	 * @param number $agentid agentid 当前应用agentid(common_plugin表cp_agentid字段)，如不提供则自全局变量startup_env取
	 * @param number $cp_pluginid cp_pluginid 当前应用id（common_plugin表cp_pluginid字段），如不提供则自全局变量startup_env取
	 * @param number $sender_uid 忽略当前发送人的uid（不给其发送消息），不提供或者设置为0则自全局变量获取startup_env::get('wbs_uid')，
	 * 				会忽略给其发送消息（发送所在部门和全部人除外）。若设置为null 或 -1，则不忽略（会给$sender_uid发送消息）。
	 * 				默认为：0，不给当前登录的人发送消息（如果：$touser 内有此人）
	 * 				如果想给$sender_uid发送消息，则设置其为null或-1（前提是$touser内有此人$sender_uid）
	 */
	public static function push_news_send_queue($session_obj, $msg_title, $msg_desc
			, $msg_url, $touser = '', $toparty = '', $msg_picurl = '', $agentid = 0, $cp_pluginid = 0, $sender_uid = 0) {

		$message = self::__format_news($msg_title, $msg_desc, $msg_url, $msg_picurl);
		return self::_push_queue($session_obj, self::MSGTYPE_NEWS, $message, $touser, $toparty, $agentid, $cp_pluginid, $sender_uid);
	}

	public static function push_image_send_queue($session_obj, $mediaid, $touser = '', $toparty = '', $agentid = 0, $cp_pluginid = 0, $sender_uid = 0) {

		$message = array('media_id' => $mediaid);
		return self::_push_queue($session_obj, self::MSGTYPE_IMAGE, $message, $touser, $toparty, $agentid, $cp_pluginid, $sender_uid);
	}

	public static function push_file_send_queue($session_obj, $mediaid, $touser = '', $toparty = '', $agentid = 0, $cp_pluginid = 0, $sender_uid = 0) {

		$message = array('media_id' => $mediaid);
		return self::_push_queue($session_obj, self::MSGTYPE_FILE, $message, $touser, $toparty, $agentid, $cp_pluginid, $sender_uid);
	}

	public static function push_voice_send_queue($session_obj, $mediaid, $touser = '', $toparty = '', $agentid = 0, $cp_pluginid = 0, $sender_uid = 0) {

		$message = array('media_id' => $mediaid);
		return self::_push_queue($session_obj, self::MSGTYPE_VOICE, $message, $touser, $toparty, $agentid, $cp_pluginid, $sender_uid);
	}

	public static function push_video_send_queue($session_obj, $mediaid, $title = '', $desc = '', $touser = '', $toparty = '', $agentid = 0, $cp_pluginid = 0, $sender_uid = 0) {

		$message = array(
			'media_id' => $mediaid,
			'title' => $title,
			'description' => $description
		);
		return self::_push_queue($session_obj, self::MSGTYPE_VIDEO, $message, $touser, $toparty, $agentid, $cp_pluginid, $sender_uid);
	}

	/**
	 * 发送一条消息
	 * @param object $session_obj session对象
	 * @param string $msg 消息标题（建议64以内）
	 * @param string $touser 发送给目标人。member['m_openid']，多个之间使用“|”分隔，或者m_uid列表
	 * @param string $toparty 发送给目标部门，多个之间使用“|”分隔，多个之间使用“|”分隔，或者cd_uid列表
	 * @param number $agentid agentid 当前应用agentid(common_plugin表cp_agentid字段)，如不提供则自全局变量startup_env取
	 * @param number $cp_pluginid cp_pluginid 当前应用id（common_plugin表cp_pluginid字段），如不提供则自全局变量startup_env取
	 * @param number $sender_uid 忽略当前发送人的uid（不给其发送消息），不提供或者设置为0则自全局变量获取startup_env::get('wbs_uid')，
	 * 				会忽略给其发送消息（发送所在部门和全部人除外）。若设置为null 或 -1，则不忽略（会给$sender_uid发送消息）。
	 * 				默认为：0，不给当前登录的人发送消息（如果：$touser 内有此人）
	 * 				如果想给$sender_uid发送消息，则设置其为null或-1（前提是$touser内有此人$sender_uid）
	 */
	public static function _push_queue($session_obj, $msgtype, $msg, $touser = '', $toparty = '', $agentid = 0, $cp_pluginid = 0, $sender_uid = 0) {

		// 获取缓存
		$settings = voa_h_cache::get_instance()->get('setting', 'oa');
		// 判断当前是否是demo站
		$is_demo = !empty($settings['domain']) && $settings['domain'] == 'demo.vchangyi.com';
		// 如果是demo，且非新闻公告应用，则不发送给部门
		if ($is_demo && $cp_pluginid != 28) {
			$toparty = '';
		}

		// 未提供应用ID则尝试自全局变量中获取
		if (empty($cp_pluginid)) {
			$cp_pluginid = startup_env::get('pluginid');
		}
		// 未提供agentid则尝试自全局变量获取
		if (empty($agentid)) {
			$agentid = startup_env::get('agentid');
		}
		// 未指定当前发送者，则自全局变量获取
		if (empty($sender_uid) && $sender_uid !== null) {
			$sender_uid = (int)startup_env::get('wbs_uid');
		}
		// 获取真实要接收的人员和部门列表
		if (!self::get_to($touser, $toparty, $sender_uid)) {
			// 如果都为空，则不发布
			return false;
		}

		// 存在当前发送者，则试图不给其发消息
		if (is_numeric($sender_uid) && $sender_uid > 0 && $touser) {

			// 获取发送者信息
			$sender = voa_h_user::get($sender_uid);
			if (!empty($sender['m_openid'])) {
				$tmp = explode('|', $touser);
				foreach ($tmp as $_k => $_openid) {
					// 剔除掉当前用户，不给其发消息
					if ($_openid == $sender['m_openid']) {
						unset($tmp[$_k]);
					}
				}
				$touser = implode('|', $tmp);
				unset($tmp, $_openid, $_k);
			}
		}

		// 都为空，则不发消息
		if (empty($touser) && empty($toparty)) {
			return false;
		}

		// 如果是demo站，发送给全体人员且不是新闻公告应用，则不发消息
		if ($is_demo && $cp_pluginid != 28 && ($touser == '@all' || $toparty == '@all')) {
			// 不发送消息
			return true;
		}
		// 重组消息体
		$data = array(
			'cp_pluginid' => $cp_pluginid,
			'mq_touser' => $touser,
			'mq_toparty' => $toparty,
			'mq_msgtype' => $msgtype,
			'mq_agentid' => $agentid,
			'mq_message' => serialize($msg)
		);

		// 已存在的队列ID
		if (!empty($session_obj)) {
			//$mq_ids = explode(',', $session_obj->get('mq_ids'));
		}

		// 当前队列id（新的）
		self::send_qymsg($data);
		$data['mq_status'] = voa_d_oa_msg_queue::STATUS_SUCCEED;
		$serv_sq_oa = &service::factory('voa_s_oa_msg_queue', array('pluginid' => 0));
		$mq_ids[] = $serv_sq_oa->insert($data, true);

		if (!empty($session_obj)) {
			//$session_obj->set('mq_ids', implode(',', $mq_ids));
		}
	}

	/**
	 * 企业消息发送
	 * @param array $data 消息
	 * @return boolean
	 */
	public static function send_qymsg($data) {

		// 如果是debug, 则不发消息
		$debug_domain = config::get('voa.debug.domain');
		if (!empty($debug_domain) && preg_match("/" . preg_quote($debug_domain) . "$/i", $_SERVER['HTTP_HOST'])) {
			return true;
		}

		$pluginid = (int)startup_env::get('pluginid');
		startup_env::set('pluginid', $data['cp_pluginid']);
		$serv_qy = voa_wxqy_service::instance();
		switch ($data['mq_msgtype']) {
			case voa_h_qymsg::MSGTYPE_TEXT:
				$serv_qy->post_text($data['mq_message'], $data['mq_agentid'], $data['mq_touser'], $data['mq_toparty']);
				break;
			case voa_h_qymsg::MSGTYPE_NEWS:
				$serv_qy->post_news(unserialize($data['mq_message']), $data['mq_agentid'], $data['mq_touser'], $data['mq_toparty']);
				break;
			case voa_h_qymsg::MSGTYPE_FILE:
				$serv_qy->post_file(unserialize($data['mq_message']), $data['mq_agentid'], $data['mq_touser'], $data['mq_toparty']);
				break;
			case voa_h_qymsg::MSGTYPE_VOICE:
				$serv_qy->post_voice(unserialize($data['mq_message']), $data['mq_agentid'], $data['mq_touser'], $data['mq_toparty']);
				break;
			case voa_h_qymsg::MSGTYPE_VIDEO:
				$serv_qy->post_video(unserialize($data['mq_message']), $data['mq_agentid'], $data['mq_touser'], $data['mq_toparty']);
				break;
			case voa_h_qymsg::MSGTYPE_IMAGE:
				$serv_qy->post_image(unserialize($data['mq_message']), $data['mq_agentid'], $data['mq_touser'], $data['mq_toparty']);
				break;
		}

		// 恢复
		startup_env::set('pluginid', $pluginid);
		return true;
	}

	/**
	 * 把消息推入 pm 表
	 * @param array $from_user 来源用户信息
	 * @param array $uids 接收者的uid
	 * @param array $message 消息信息
	 * @param number $pluginid 插件id
	 * @return boolean
	 */
	public static function add_to_pm($from_user, $uids, $title = '', $message = '', $params = '', $pluginid = 0) {

		// 如果没有标题或消息内容
		if (empty($message) && empty($title)) {
			return true;
		}

		// 如果是数组, 则序列化
		if (is_array($params)) {
			$params = serialize($params);
		}

		// 读取用户信息
		$serv_mem = &service::factory('voa_s_oa_member');
		$userlist = $serv_mem->fetch_all_by_ids($uids);
		if (empty($userlist)) {
			return true;
		}

		// 判断来源用户是否存在, 不存在, 则认为是系统发的
		if (empty($from_user) || !is_array($from_user)) {
			$from_user = array(
				'm_uid' => 0,
				'm_username' => 'system'
			);
		}

		// 消息数组
		$pms = array();
		foreach ($userlist as $_u) {
			$pms[] = array(
				'cp_pluginid' => $pluginid,
				'm_uid' => $_u['m_uid'],
				'm_username' => $_u['m_username'],
				'from_uid' => $from_user['m_uid'],
				'from_username' => $from_user['m_username'],
				'pm_title' => $title,
				'pm_message' => $message,
				'pm_params' => $params,
				'pm_isread' => 0
			);
		}

		// 消息入库
		$serv_pm = &service::factory('voa_s_oa_common_pm');
		$serv_pm->insert_multi($pms);

		return true;
	}

	/**
	 * 整理真实需要发送的人员和部门列表
	 * @param mixed $touser (引用结果)要发送的人员
	 * @param mixed $toparty (引用结果)要发送的部门
	 * @param number $sender_uid 当前发送人uid，会忽略此人
	 * @return boolean
	 */
	public static function get_to(&$touser, &$toparty) {

		// 如果设置小于0，或者@all，则发全部

		if ((is_scalar($touser) && ($touser < 0 || stripos($touser, '@all') !== false))
				|| (is_scalar($toparty) && ($toparty < 0 || stripos($toparty, '@all') !== false))) {
			$touser = '@all';
			$toparty = '';

			return true;
		}

		// 如果接收人或者部门数组内存在负数，则发给所有人
		if ((is_array($touser) && array_search('-1', $touser) !== false)
				|| (is_array($toparty) && array_search('-1', $toparty) !== false)) {
			$touser = '@all';
			$toparty = '';

			return true;
		}

		// 获取人员和部门列表，如果传入的不是uid和cd_id列表，则会为空
		$members= self::__get_member($touser);// 尝试获取人员列表
		$departments = self::__get_departments($toparty);// 尝试获取部门列表
		// 要发送的用户openid列表
		$openids = array();
		// 要发送的部门id列表
		$dpids = array();

		// 给定的不是uid和cd_id列表，则输出原始的touser和toparty
		if (empty($members) && empty($departments)) {
			// 都为空，则不发送

			self::__format_touser($touser, $openids);
			self::__format_toparty($toparty, $dpids);
			$touser = implode('|', array_unique($openids));
			$toparty = implode('|', array_unique($dpids));
			if (empty($touser) && empty($toparty)) {
				return false;
			}

			return true;
		}

		// 初始化输出
		$touser = '';
		$toparty = '';

		// 遍历用户列表
		// 剔除已经在部门内存在的人员（避免重复发送）
		// 获取要发送的人员的微信openid列表
		foreach ($members as $_user) {
			// 存在于待发送的部门，则不单独发给此人，避免重复收到
			if (isset($departments[$_user['cd_id']])) {
				continue;
			}
			$openids[$_user['m_openid']] = $_user['m_openid'];
		}
		// 遍历部门列表，提取微信部门ID
		foreach ($departments as $_dp) {
			if (isset($dpids[$_dp['cd_qywxid']])) {
				continue;
			}
			$dpids[$_dp['cd_qywxid']] = $_dp['cd_qywxid'];
		}

		// 如果传的是openid字符串，则整理过滤
		if (is_string($touser)) {
			foreach (explode(',', $touser) as $_openid) {
				$_openid = trim($_openid);
				if (!$_openid || isset($openids[$_openid])) {
					continue;
				}
				$openids[$_openid] = $_openid;
			}
		}

		// 如果传的是部门微信id字符串，则整理过滤
		if (is_string($toparty)) {
			foreach (explode(',', $toparty) as $_dpid) {
				$_dpid = trim($_dpid);
				if (!$_dpid || isset($dpids[$_dpid])) {
					continue;
				}
				$dpids[$_dpid] = $_dpid;
			}
		}

		// 转为发消息需要的字符串
		$touser = implode('|', $openids);
		$toparty = implode('|', $dpids);

		return true;
	}

	/**
	 * 获取部门列表
	 * @param mixed $toparty
	 * @return array
	 */
	private static function __get_departments($toparty) {

		// 给定的是部门ID（cd_id）列表
		if (!is_array($toparty) || empty($toparty)) {
			return array();
		}

		// 获取部门id列表
		$serv = &service::factory('voa_s_oa_common_department');
		return $serv->fetch_all_by_key($toparty);
	}

	/**
	 * 获取用户列表
	 * @param mixed $touser
	 * @return array
	 */
	private static function __get_member($touser) {
		// 不是m_uid列表
		if (!is_array($touser) || empty($touser)) {
			return array();
		}

		return voa_h_user::get_multi($touser);;
	}

	/**
	 * 整理接收人数据
	 * @param string $touser 接收人的openid字符串
	 * @param array $openids (引用结果)openid列表
	 * @return boolean
	 */
	private static function __format_touser($touser, &$openids = array()) {

		// 如果传的是openid字符串，则整理过滤
		if (is_string($touser)) {
			foreach (explode(',', $touser) as $_openid) {
				$_openid = trim($_openid);
				if (!$_openid || isset($openids[$_openid])) {
					continue;
				}
				$openids[$_openid] = $_openid;
			}
		} else {
			$openids = array();
		}

		return true;
	}

	/**
	 * 整理接收部门数据
	 * @param string $toparty 接收人的微信部门id字符串
	 * @param array $dpids (引用结果)部门id列表
	 * @return boolean
	 */
	private static function __format_toparty($toparty, &$dpids = array()) {
		// 如果传的是部门微信id字符串，则整理过滤
		if (is_string($toparty)) {
			foreach (explode(',', $toparty) as $_dpid) {
				$_dpid = trim($_dpid);
				if (!$_dpid || isset($dpids[$_dpid])) {
					continue;
				}
				$dpids[$_dpid] = $_dpid;
			}
		} else {
			$dpids = array();
		}

		return true;
	}

	/**
	 * 重组微信企业号消息
	 * @param mixed $msg_title 标题
	 * @param mixed $msg_desc 描述
	 * @param mixed $msg_url 链接
	 * @param mixed $msg_picurl 图片
	 * @return array
	 */
	private static function __format_news($msg_title, $msg_desc, $msg_url, $msg_picurl) {

		// 单条图文消息
		if (is_scalar($msg_title)) {
			return array(
				'title' => $msg_title,
				'description' => $msg_desc,
				'url' => $msg_url,
				'picurl' => $msg_picurl
			);
		}

		// 多条图文消息
		$msg = array();
		foreach ($msg_title as $_k => $_title) {
			$msg[$_k]['title'] = $_title;
			$msg[$_k]['description'] = isset($msg_desc[$_k]) ? $msg_desc[$_k] : '';
			$msg[$_k]['url'] = isset($msg_url[$_k]) ? $msg_url[$_k] : '';
			$msg[$_k]['picurl'] = isset($msg_picurl[$_k]) ? $msg_picurl[$_k] : '';
		}

		return $msg;
	}

}
