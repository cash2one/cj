<?php
/**
 * wxwall.php
 * 微信墙公共类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_h_wxwall {

	/** 微信墙状态描述 */
	public static $status = array(
			voa_d_oa_wxwall::STATUS_NORMAL => '申请中',
			voa_d_oa_wxwall::STATUS_APPROVE => '已批准',
			voa_d_oa_wxwall::STATUS_REFUSE => '审核未通过',
	);

	/** 微信墙开启状态描述 */
	public static $isopen = array(
			voa_d_oa_wxwall::IS_CLOSE => '关闭',
			voa_d_oa_wxwall::IS_OPEN => '开放'
	);

	/** 微信墙上墙内容验证状态描述 */
	public static $postverify = array(
			0 => '不需要验证',
			1 => '需要验证'
	);

	/** 微信墙内容状态描述 */
	public static $post_status = array(
			voa_d_oa_wxwall_post::STATUS_NORMAL => '待审核',
			voa_d_oa_wxwall_post::STATUS_APPROVE => '通过',
			voa_d_oa_wxwall_post::STATUS_REFUSE => '拒绝',
			voa_d_oa_wxwall_post::STATUS_REMOVE => '删除',
	);

	/** 内容审核状态文字描述 */
	public static $post_status_description 	=	array(
			voa_d_oa_wxwall_post::STATUS_APPROVE => '已上墙消息',
			voa_d_oa_wxwall_post::STATUS_NORMAL => '待上墙消息',
			voa_d_oa_wxwall_post::STATUS_REFUSE => '已下墙消息',
			voa_d_oa_wxwall_post::STATUS_REMOVE => '已删除消息',
	);


	/** 已删除消息状态 */
	public static $post_status_remove = voa_d_oa_wxwall_post::STATUS_REMOVE;

	/**
	 * 登录微信墙成功后给出的提示信息文字
	 * @return string
	 */
	public static function wxwall_success_login_msg() {
		return '您已成功登录微信墙，直接回复消息可发布上墙信息，退出微信墙请回复：'.voa_h_wxcmd::WXWALL_QUIT_CODE;
	}

	/**
	 * 发布上墙的代码
	 * @param number $ww_id
	 * @return string
	 */
	public static function wxwall_post_message_code($ww_id) {
		return voa_h_wxcmd::WXWALL_POST_CODE.$ww_id;
	}

	/**
	 * 下墙代码
	 * @return string
	 */
	public static function wxwall_quite_code() {
		return voa_h_wxcmd::WXWALL_QUIT_CODE;
	}

	/**
	 * 微信墙展示url
	 * @param number $ww_id
	 * @return string
	 */
	public static function wxwall_url($ww_id) {
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$scheme = config::get('voa.oa_http_scheme');
		return $scheme.$sets['domain'].'/'.config::get('voa.wxwall_path').'/'.$ww_id;
	}

	/**
	 * 微信墙管理Url
	 * @return string
	 */
	public static function wxwall_cpurl() {
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$scheme = config::get('voa.oa_http_scheme');
		return $scheme.$sets['domain'].'/'.config::get('voa.wxwall_path').'/';
	}

	/**
	 * 格式化微信墙信息
	 * @param array $wxwall
	 * @return array
	 */
	public static function format_wxwall($wxwall) {
		$wxwall['_begintime'] = rgmdate($wxwall['ww_begintime'], 'Y-m-d H:i');
		$wxwall['_endtime'] = rgmdate($wxwall['ww_endtime'], 'Y-m-d H:i');

		$wxwall['_begintime_all'] = $wxwall['ww_begintime'] ? rgmdate($wxwall['ww_begintime'], 'Y-m-d') : rgmdate(startup_env::get('timestamp'), 'Y-m-d');
		$wxwall['_endtime_all'] = $wxwall['ww_endtime'] ? rgmdate($wxwall['ww_endtime'], 'Y-m-d') : rgmdate(startup_env::get('timestamp'), 'Y-m-d');

		$isopen = self::$isopen;
		$postverify = self::$postverify;
		$status = self::$status;

		$wxwall['_isopen'] = isset($isopen[$wxwall['ww_isopen']]) ? $isopen[$wxwall['ww_isopen']] : '--';
		$wxwall['_postverify'] = isset($postverify[$wxwall['ww_postverify']]) ? $postverify[$wxwall['ww_postverify']] : '--';
		$wxwall['_status'] = isset($status[$wxwall['ww_status']]) ? $status[$wxwall['ww_status']] : '--';

		$wxwall['_created'] = rgmdate($wxwall['ww_created'], 'Y-m-d H:i');
		$wxwall['_updated'] = rgmdate($wxwall['ww_updated'] ? $wxwall['ww_updated'] : $wxwall['ww_created'], 'Y-m-d H:i');

		if (startup_env::get('timestamp') < $wxwall['ww_begintime']) {
			$wxwall['_runstatus'] = '未开始';
		} elseif ($wxwall['ww_endtime'] < startup_env::get('timestamp')) {
			$wxwall['_runstatus'] = '已结束';
		} else {
			$wxwall['_runstatus'] = '开放中';
		}

		$wxwall['_message'] = nl2br($wxwall['ww_message']);

		$wxwall['_maxpost'] = $wxwall['ww_maxpost'] ? $wxwall['ww_maxpost'] : '不限制';
		return $wxwall;
	}

	/**
	 * 获取指定微信墙信息
	 * @param number $ww_id
	 * @return Ambigous <multitype:, string, multitype:string >
	 */
	public static function get_wxwall($ww_id) {
		if (!$ww_id || !validator::is_int($ww_id)) {
			return '指定微信墙信息不存在 或 已被删除';
		}
		$serv = &service::factory('voa_s_oa_wxwall', array('pluginid' => startup_env::get('pluginid')));
		$wxwall = $serv->fetch_by_id($ww_id);
		if (empty($wxwall) || !isset($wxwall['ww_id']) || $wxwall['ww_id'] != $ww_id) {
			return '指定微信墙信息不存在 或 已被删除';
		}
		return self::format_wxwall($wxwall);
	}

	/**
	 * 验证微信墙设置字段合法性
	 * @param array $param
	 * @param array $old
	 * @return string|multitype:unknown NULL number string
	 */
	public static function wxwall_field_check($param = array(), $old = array()) {
		$update = array();
		if (isset($param['ww_subject']) && $param['ww_subject'] != $old['ww_subject']) {
			if (!validator::is_len_in_range($param['ww_subject'], 1, 80)) {
				return '微信墙标题长度应介于1到80字节之间';
			} elseif (rhtmlspecialchars($param['ww_subject']) != $param['ww_subject']) {
				return '微信墙标题不能包含特殊字符';
			}
			$update['ww_subject'] = $param['ww_subject'];
		}
		if (isset($param['ww_status']) && $param['ww_status'] != $old['ww_status']) {
			$status = self::$status;
			if (!isset($status[$param['ww_status']])) {
				return '请正确设置微信墙审核状态';
			}
			$update['ww_status'] = $param['ww_status'];
		}
		if (isset($param['ww_begintime']) && isset($param['ww_endtime'])) {
			if (!validator::is_date($param['ww_begintime'])) {
				return '请正确设置微信墙开放起始时间，格式为：yyyy-mm-dd';
			}
			if (!validator::is_date($param['ww_endtime'])) {
				return '请正确设置微信墙开放结束时间，格式为：yyyy-mm-dd';
			}
			$begintime = rstrtotime($param['ww_begintime'].' 00:00:00');
			$endtime = rstrtotime($param['ww_endtime'].' 23:59:59');
			if ($begintime >= $endtime) {
				return '微信墙结束时间必须大于开始时间';
			}
			if ($param['ww_begintime'] != $old['_begintime_all'] && $begintime != $old['ww_begintime']) {
				$update['ww_begintime'] = $begintime;
			}
			if ($param['ww_endtime'] != $old['_endtime_all'] && $endtime != $old['ww_endtime']) {
				$update['ww_endtime'] = $endtime;
			}
		}
		if (isset($param['ww_isopen']) && $param['ww_isopen'] != $old['ww_isopen']) {
			$isopen = self::$isopen;
			if (!isset($isopen[$param['ww_isopen']])) {
				return '请正确设置微信墙开启状态';
			}
			$update['ww_isopen'] = $param['ww_isopen'];
		}
		if (isset($param['ww_postverify']) && $param['ww_postverify'] != $old['ww_postverify']) {
			$postverify = self::$postverify;
			if (!isset($postverify[$param['ww_postverify']])) {
				return '请正确设置微信墙内容审核状态';
			}
			$update['ww_postverify'] = $param['ww_postverify'];
		}
		if (isset($param['ww_maxpost']) && $param['ww_maxpost'] != $old['ww_maxpost']) {
			if ($param['ww_maxpost'] != 0 && (!is_numeric($param['ww_maxpost']) || !validator::is_int($param['ww_maxpost']) || !validator::is_in_range($param['ww_maxpost'], 0, 10000))) {
				return '发表微信墙内容数量限制设置错误，请设置0到9999之间的整数';
			}
			$update['ww_maxpost'] = $param['ww_maxpost'];
		}
		if (isset($param['ww_message']) && $param['ww_message'] != $old['ww_message']) {
			if (!validator::is_len_in_range($param['ww_message'], 0, 1000)) {
				return '微信墙详情备注长度应该小于1000字节';
			}
			$update['ww_message'] = $param['ww_message'];
		}
		if (isset($param['new_password']) && $param['new_password'] != '') {
			$update['ww_salt'] = random(4);
			$update['ww_password'] = self::generate_passwd($param['new_password'], $update['ww_salt']);
		}
		if (empty($update)) {
			return '微信墙信息未发生变动无须进行提交保存';
		}
		return $update;
	}

	/**
	 * 生成数据表中的用户密码,
	 * @param string $passwd 用户提交的密码
	 * @param string $salt 干扰字串
	 */
	public static function generate_passwd($passwd, $salt, $pwd_is_md5 = false) {
		if (!$pwd_is_md5) {
			$passwd = md5($passwd);
		}
		return md5($passwd.$salt);
	}

	/**
	 * 格式化微信墙消息内容，以利于前端显示
	 * @param string $message
	 */
	public static function message_format($message){
		$message = @strip_tags($message);
		if ( preg_match('/\[img\](.+?)\[\/img\]/', $message, $match) ) {
			$message	=	'<a href="'.$match[1].'" target="_blank"><img src="'.$match[1].'" class="item_message_pic" alt="" /></a>';
		}
		$message	=	voa_weixin_smiley::smiley($message);
		return $message;
	}

	/**
	 * 检查微信墙状态
	 * @param array $wxwall 微信墙信息
	 */
	public static function check_status($wxwall){
		/** 微信墙不存在 */
		if (empty($wxwall) || empty($wxwall['ww_id']) || !is_array($wxwall)) {
			return '您要访问的微信墙不存在，您可以尝试创建一个新的微信墙';
		}
		$subject = rhtmlspecialchars($wxwall['ww_subject']);
		/** 尚未开始 */
		if ($wxwall['ww_begintime'] > startup_env::get('timestamp')) {
			return '您正在访问的微信墙《'.$subject.'》尚未开始（开始时间为：'.$wxwall['_begintime'].'）';
		}
		/** 已过期 */
		if ($wxwall['ww_endtime'] > 0 && $wxwall['ww_endtime'] < startup_env::get('timestamp')) {
			return '您正在访问的微信墙《'.$subject.'》已于 '.$wxwall['_endtime'].' 结束，感谢您的支持。';
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

	public static function make_qrcode($ww_id) {
		$wxwall = self::get_wxwall($ww_id);
		if (empty($wxwall) || !is_array($wxwall)) {
			return self::error_qrcode(1);
		}

		if ((startup_env::get('timestamp') - $wxwall['ww_qrcodeexpire']) < 120) {
			/** 距离临时二维码有效期小于规定时间，则返回缓存 */
			return $wxwall['ww_qrcodeurl'];
		}

		//TODO 后续可能需要修改此处，通过统一接口获取到场景id
		$sceneid = startup_env::get('timestamp') + 86400*7;

		/** 初始化微信服务 */
		$wx_service = voa_weixin_service::instance();
		/** 二维码url */
		$qrcode_url = '';
		if (!$wx_service->get_qrcode($qrcode_url, $sceneid)) {
			return self::error_qrcode(2);
		}

		/** 更新二维码地址以及临时二维码场景id，确保对应关系 */
		$serv_wxwall = &service::factory('voa_s_oa_wxwall', array('pluginid' => startup_env::get('pluginid')));
		$serv_wxwall->update(array(
				'ww_sceneid' => $sceneid,
				'ww_qrcodeexpire' => $wx_ticket->expire_seconds * 0.8 + startup_env::get('timestamp'),
				'ww_qrcodeurl' => $qrcode_url,
		),array('ww_id' => $ww_id));

		return $qrcode_url;
	}

	/**
	 * 返回出错的二维码数据流
	 * 错误文字为：二维码发生故障请刷新后重试
	 * @return string
	 */
	public static function error_qrcode($number){
		return APP_STATIC_URL.'/qrcodeerror/'.$number.'.png';
	}
}
