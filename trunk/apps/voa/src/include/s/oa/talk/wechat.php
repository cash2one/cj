<?php
/**
 * voa_s_oa_talk_wechat
 * 聊天记录操作
 *
 * $Author$
 * $Id$
 */

class voa_s_oa_talk_wechat extends voa_s_abstract {


	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 设置参数
	 * @param array $params 请求参数
	 * @return boolean
	 */
	public function set_params($params = array()) {

		$this->_params = $params;
		return true;
	}

	/**
	 * 格式化聊天信息
	 * @param array $chat 聊天信息
	 * @return boolean
	 */
	public function fmt_chat(&$chat) {

		// 创建时间格式转换
		$chat['_created_u'] = rgmdate($chat['created'], 'u');
		// 过滤 html
		$chat['message'] = rhtmlspecialchars($chat['message']);

		return true;
	}

	/**
	 * 格式化聊天信息列表
	 * @param array $chats 聊天信息
	 * @return boolean
	 */
	public function fmt_chat_list(&$chats) {

		// 遍历聊天记录
		foreach ($chats as &$_chat) {
			$this->fmt_chat($_chat);
		}

		return true;
	}

	/**
	 * 检查 sales 是否登录以及访客是否登录
	 * @param int $uid 用户 uid
	 * @param int $tv_uid 访客 uid
	 * @throws Exception
	 * @return boolean
	 */
	public function chk_sales(&$uid, &$tv_uid) {

		// 取登录用户的 uid
		$uid = startup_env::get('wbs_uid');
		// 如果非登录用户(即访客)
		if (empty($uid)) {
			$uid = (int)$this->_get('uid');
		}

		// 取访客 tv_uid
		$tv_uid = $this->_params['tv_id'];

		// 如果访客 uid 为空
		if (empty($tv_uid)) {
			$tv_uid = (int)$this->_get('tv_id');
			return false;
		}

		// 如果 uid 为空
		if (empty($uid)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_talk::PLEASE_SELECT_SALES);
			return false;
		}

		// 如果 tv_uid
		if (empty($tv_uid)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_talk::PLEASE_REFRESH);
			return false;
		}

		return true;
	}

	/**
	 * 聊天记录入库
	 * @param array $chat 聊天记录信息
	 * @return boolean
	 */
	public function add(&$chat) {

		// 获取聊天信息
		$fields = array(
			array('message', self::VAR_STR, 'chk_message', null, false), // 聊天信息
			array('uid', self::VAR_INT, 'chk_uid', null, false), // sales uid
			array('tv_uid', self::VAR_INT, 'chk_tv_uid', null, false), // 访客 uid
		);
		// 提取数据
		if (!$this->extract_field($chat, $fields)) {
			return false;
		}

		// 如果是登录账号, 则
		if (0 < startup_env::get('wbs_uid')) {
			$chat['tw_type'] = voa_d_oa_talk_wechat::TYPE_SALES;
		} else {
			$chat['tw_type'] = voa_d_oa_talk_wechat::TYPE_VIEWER;
		}

		// 入库
		$chat = $this->insert($chat);

		// 格式化聊天
		$this->fmt_chat($chat);

		return true;
	}

	/**
	 * 检查聊天消息
	 * @param string $msg 聊天消息
	 * @param string $err 错误
	 * @throws Exception
	 * @return boolean
	 */
	public function chk_message($msg, $err = null) {

		// 如果消息为空
		if (empty($msg)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_talk::MESSAGE_IS_NULL);
			return false;
		}

		return true;
	}

	/**
	 * 检查 uid 是否有值
	 * @param int $uid 用户 uid
	 * @param string $err 提示错误
	 * @throws Exception
	 * @return boolean
	 */
	public function chk_uid($uid, $err = null) {

		// 如果 sales uid 为空
		if (empty($uid)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_talk::SALES_UID_IS_NULL);
			return false;
		}

		return true;
	}

	/**
	 * 判断 uid 是否有值
	 * @param int $uid 访客 uid
	 * @param string $err 提示错误
	 * @throws Exception
	 * @return boolean
	 */
	public function chk_tv_uid($uid, $err = null) {

		// 访客 uid
		if (empty($uid)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_talk::VIEWER_UID_IS_NULL);
			return false;
		}

		return true;
	}

}
