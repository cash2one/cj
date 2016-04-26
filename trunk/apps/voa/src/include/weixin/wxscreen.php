<?php
/**
 * 微信墙处理类
 * $Author$
 * $Id$
 */

/**
 * 微信墙处理类
 * @author Administrator
 */
class voa_weixin_wxscreen {
	/**
	 * 后台认证cookie储存名
	 * @var string
	 */
	public $cookiename_auth = 'wxscreen_auth';

	/**
	 * 微信墙变量定义
	 * @var array
	 */
	public $setting = array();

	/**
	 * 微信墙开启状态文字描述
	 * @var array
	 */
	public $ww_isopen_tips = array(
		voa_d_oa_wxwall::IS_OPEN => '开放',
		voa_d_oa_wxwall::IS_CLOSE => '关闭',
	);

	/**
	 * 微信墙内容验证状态文字描述
	 * @var array
	 */
	public $ww_postverify_tips	 = array(
		0 => '不需要验证',
		1 => '需要验证',
	);

	/**
	 * 内容审核状态文字描述
	 * @var array
	 */
	public $wwp_status = array(
		voa_d_oa_wxwall_post::STATUS_APPROVE => '已上墙消息',
		voa_d_oa_wxwall_post::STATUS_NORMAL => '待上墙消息',
		voa_d_oa_wxwall_post::STATUS_REFUSE => '已下墙消息',
		voa_d_oa_wxwall_post::STATUS_REMOVE => '已删除消息',
	);

	public function __construct() {
		$this->setting = voa_h_cache::get_instance()->get('plugin.wxwall.setting', 'oa');
	}

	/**
	 * 检查用户是否在某个微信墙线上
	 * @param unknown $m_openid
	 * @param unknown $ww_id
	 */
	public function wxwall_online($m_openid, $ww_id) {
		$serv = &service::factory('voa_s_oa_wxwall_online', array('pluginid' => startup_env::get('pluginid')));
		return $serv->fetch_by_openid_id($m_openid, $ww_id);
	}

	/**
	 * 清理过期了的微信墙在线信息
	 */
	public function wxwall_online_clear() {
		$serv = &service::factory('voa_s_oa_wxwall', array('pluginid' => startup_env::get('pluginid')));
		/** 找到已经关闭了的微信墙id */
		$ww_ids = $serv->fetch_all_close();

		/** 删除所有这些微信墙的在线信息 */
		$serv_ol = &service::factory('voa_s_oa_wxwall_online', array('pluginid' => startup_env::get('pluginid')));
		$serv_ol->delete_by_ww_id($ww_ids);
	}

	/**
	 * 获取用户发送的消息内容
	 * @param unknown $serv_msg
	 */
	public function get_content($serv_msg) {
		$content = '';
		switch (rstrtolower($serv_msg->msg_type)) {
			case 'image':
				$content = '[img]'.$serv_msg->msg['pic_url'].'[/img]';
			break;
			case 'event':
				$content = $serv_msg->msg['event_key'];
			break;
			default:
				$content = $serv_msg->msg['content'];
		}

		if ($content) {
			/** 转换IOS字符表情为可储存的字符串 */
			$smiley = new voa_weixin_smiley();
			$content = $smiley->emoji($content);
		}

		return $content;
	}

	/**
	 * 自微信消息（含扫描二维码）提取微信墙id
	 * @param object $serv_msg 来自微信的消息对象
	 * @param int|false 二维码场景id
	 */
	public function get_wwid($serv_msg, $sceneid = false) {
		$serv = &service::factory('voa_s_oa_wxwall', array('pluginid' => startup_env::get('pluginid')));
		$ww_id = 0;
		/** 自扫描二维码的场景id获取微信墙id */
		if ($sceneid !== false) {
			$wxwall = $serv->fetch_by_sceneid($sceneid);
			if (!$wxwall) {
				return $ww_id;
			}
			/** 签到 */
			$this->wxwall_sign($serv_msg->from_user_name, $wxwall['ww_id']);
			return $wxwall['ww_id'];
		}

		/** 获取消息内容 */
		$msgContent = $this->get_content($serv_msg);
		/** 自消息内容提取上墙命令中的微信墙id */
		if (preg_match('/'.preg_quote(voa_h_wxcmd::WXWALL_POST_CODE).'\s*(\d+)/i', $msgContent, $match)) {
			return $match[1];
		}

		/** 自发布者openid获取微信墙id */
		$online	 = $this->wxwall_online($serv_msg->msg['from_user_name'], false);
		if ($online) {
			/** 获取到最新在线的微信墙id */
			return $online['ww_id'];
		}

		return $ww_id;
	}

	/**
	 * 微信墙签到（写入在线状态）
	 * @param unknown $m_openid
	 * @param unknown $ww_id
	 */
	public function wxwall_sign($m_openid, $ww_id){
		if (!$this->wxwall_online($m_openid, $ww_id)) {
			$serv = &service::factory('voa_s_oa_wxwall_online', array('pluginid' => startup_env::get('pluginid')));
			/** 不在线则写入在线状态 */
			$serv->insert(array(
				'm_openid' => $m_openid,
				'ww_id' => $ww_id,
			));
		}
	}

	/**
	 * 发布上墙内容（带上墙命令检查）
	 * @param object $serv_msg 来自微信的消息对象
	 */
	public function wxwall_post($serv_msg) {
		/** 获取微信墙ww_id */
		$ww_id = $this->get_wwid($serv_msg);
		if (!$ww_id) {
			/** 无法获取微信墙id，则认为不是处于微信墙操作命令 */
			return '';
		}

		/** 发布者的openid */
		$m_openid = $serv_msg->msg['from_user_name'];

		/** 微信墙信息 */
		$wxwall = $this->get_wxwall_by_id($ww_id);
		if (($status = $this->wxwall_check_status($wxwall)) !== true) {
			/** 检查微信墙状态 */
			return $status;
		}

		/** 获取消息内容 */
		$wwp_message = $this->get_content($serv_msg);
		if (preg_match('/^('.preg_quote(voa_h_wxcmd::WXWALL_POST_CODE).'\s*\d+\s*[+]*)/i', $wwp_message, $match)) {
			$wwp_message = preg_replace('/^'.preg_quote($match[0]).'/i', '', $wwp_message);
		}

		$wwp_message = trim($wwp_message);
		if ($wwp_message == '') {
			return '请输入 “'.voa_h_wxwall::wxwall_post_message_code($ww_id).'+要说的话” ，来发布上墙的内容。';
		}

		/** 尝试写入在线状态（签到） */
		$this->wxwall_sign($m_openid, $ww_id);

		/** 获取用户信息 */
		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$user = $serv_m->fetch_by_openid($m_openid);
		if (!$user) {
			/** 用户表内不存在，则尝试找已经删除的数据 */
			$user = $serv_m->fetch_by_openid($m_openid, true);
			if (!$user) {
				/** 仍旧不存在，则伪造一个 */
				$user['m_uid'] = 0;
				$user['m_username'] = '来宾-'.startup_env::get('timestamp');
			}
		}

		/** 发布上墙内容 */
		$serv_p = &service::factory('voa_s_oa_wxwall_post', array('pluginid' => startup_env::get('pluginid')));
		$serv_p->insert(array(
			'm_uid' => $user['m_uid'],
			'm_username' => $user['m_username'],
			'ww_id' => $ww_id,
			'wwp_message' => $wwp_message,
			'wwp_status' => $wxwall['ww_postverify'] ? voa_d_oa_wxwall_post::STATUS_NORMAL : voa_d_oa_wxwall_post::STATUS_APPROVE,
		));

		return '消息已发布，您的内容可能需要通过审核后才能上墙显示，直接回复可继续发布上墙内容。（如果退出微信墙模式请回复：'.voa_h_wxcmd::WXWALL_QUIT_CODE.'）';
	}

	/**
	 * 请求下墙
	 * @param unknown $serv_msg
	 */
	public function wxwall_quit($serv_msg) {
		/** 获取当前在线的微信墙 */
		$ww_id = $this->get_wwid($serv_msg,false);
		if ($ww_id) {
			$serv = &service::factory('voa_s_oa_wxwall_online', array('pluginid' => startup_env::get('pluginid')));
			$serv->delete_by_openid_wwid($serv_msg->msg['from_user_name'], $ww_id);
			return '您已退出微信墙模式，感谢您的使用，如想重新上墙，请回复：'.voa_h_wxwall::wxwall_post_message_code($ww_id);
		}

		return '对不起，您未登录任何微信墙，无需下墙操作';
	}

	/**
	 * 检查微信墙状态
	 * @param array $wxwall 微信墙信息
	 */
	public function wxwall_check_status($wxwall) {
		/** 微信墙不存在 */
		if (empty($wxwall)) {
			return '您要访问的微信墙不存在，您可以尝试创建一个新的微信墙';
		}

		$subject = rhtmlspecialchars($wxwall['ww_subject']);
		/** 尚未开始 */
		if ($wxwall['ww_begintime'] > startup_env::get('timestamp')) {
			return '您正在访问的微信墙《'.$subject.'》尚未开始（开始时间为：'.$wxwall['_begintime_u'].'）';
		}

		/** 已过期 */
		if ($wxwall['ww_endtime'] < startup_env::get('timestamp')) {
			return '您正在访问的微信墙《'.$subject.'》已于 '.$wxwall['_endtime_u'].' 结束，感谢您的支持。';
		}

		/** 检查微信墙审核状态 */
		if ($wxwall['ww_status'] != voa_d_oa_wxwall::STATUS_APPROVE) {
			switch ($wxwall['ww_status']) {
				case voa_d_oa_wxwall::STATUS_NORMAL:
					$statusmsg = '正等待审核状态';
					break;
				case voa_d_oa_wxwall::STATUS_REFUSE:
					$statusmsg = '已被拒绝申请';
					break;
				case voa_d_oa_wxwall::STATUS_REMOVE:
					$statusmsg = '不存在';
					break;
				default:
					$statusmsg = '不存在';
					break;
			}

			return '您正在访问的微信墙《'.$subject.'》'.$statusmsg.'，请联系总管理员进行解决。';
		}

		/** 被微信墙管理员设置为不开放 */
		if ($wxwall['ww_isopen'] == voa_d_oa_wxwall::IS_CLOSE) {
			return '您正在访问的微信墙《'.$subject.'》已被管理员（'.rhtmlspecialchars($wxwall['m_username']).'）关闭，请联系解决。';
		}

		return true;
	}

	/**
	 * 根据微信墙获取该微信墙的配置信息
	 * @param int $ww_id
	 */
	public function get_wxwall_by_id($ww_id) {
		if (!empty($this->_wxwall)) {
			return $this->_wxwall;
		}

		$serv = &service::factory('voa_s_oa_wxwall', array('pluginid' => startup_env::get('pluginid')));
		$wxwall = $serv->fetch_by_id($ww_id);
		if (empty($wxwall)) {
			return array();
		}

		$wxwall['_created']	 = rgmdate($wxwall['ww_created'],'Y-m-d H:i');
		$wxwall['_begintime'] = rgmdate($wxwall['ww_begintime'],'Y-m-d H:i');
		$wxwall['_endtime']	 = rgmdate($wxwall['ww_endtime'],'Y-m-d H:i');
		$wxwall['_begintime_u'] = rgmdate($wxwall['ww_begintime'],'u');
		$wxwall['_endtime_u'] = rgmdate($wxwall['ww_endtime'],'u');
		return $this->_wxwall = $wxwall;
	}
}
