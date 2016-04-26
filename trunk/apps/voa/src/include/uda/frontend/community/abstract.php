<?php

/**
 * voa_uda_frontend_thread_abstract
 * 统一数据访问/社区应用/基类
 * $Author$
 * $Id$
 */
class voa_uda_frontend_community_abstract extends voa_uda_frontend_base {

	// 最大附件数
	protected $_attach_max = 5;
	// 配置信息
	protected $_sets = array();
	/** 应用信息 */
	protected $_plugin = array();
	protected $_setting = array();

	public function __construct() {

		parent::__construct();
		$this->_setting = voa_h_cache::get_instance()->get('setting', 'oa');
		$this->_sets = voa_h_cache::get_instance()->get('plugin.community.setting', 'oa');
		/** 取应用插件信息 */
		$pluginid = $this->_sets['pluginid'];
		startup_env::set('pluginid', $pluginid);
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		// 如果应用信息不存在
		if (!array_key_exists($pluginid, $plugins)) {
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
	 * 构造话题查看页面前端url
	 * @param unknown $url
	 * @param unknown $dr_id
	 * @return boolean
	 */
	public function viewurl(&$url, $tid) {

		//todo
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$pluginid = $this->_sets['pluginid'];
		$http = config::get(startup_env::get('app_name') . '.oa_http_scheme');
		$url = voa_wxqy_service::instance()->oauth_url($http . $sets['domain'] . "/previewh5/micro-community/index.html#/app/page/topic/topic-detail-preview?id=".$tid."&pluginid=".$pluginid);

		return true;
	}

	/**
	 * 发送话题微信消息
	 * @author Deepseath@20141222#310
	 * @param array $mq_ids (引用结果)当前消息队列ID
	 * @param array $thread 话题详情数据
	 * @param string $type 消息类型: new=新话题,draft=预览
	 * @param number $senderid 消息发送者的uid
	 * @return true;
	 */
	public function send_msg($thread, $type, $senderid, $session_obj) {

		// 构造日报查看链接/
		$viewurl = '';
		$this->viewurl($viewurl, $thread['cid']);

		$users = array();//发送消息id
		if ($type == "new") {
			$users[] = "@all";
		} else {
			// 找到用户
			$user_list = array();
			if (!empty($senderid)) {
				$userlist = voa_h_user::get_multi($senderid);
			}

			// 所有人用户信息
			foreach ($userlist as $u) {
				// 不发消息给当前发送者
				$users[] = $u['m_openid'];
			}
		}

		// 确定消息正文内容
		$content = array();
		if ($type == 'new') {
			$msg_title = "您收到一条新话题";
			$msg_desc = '话题: ' . $thread['subject'];
			$msg_desc .= "\n来自: " . $senderid['m_username'];
			$msg_url = $viewurl;
			$msg_picurl = '';
		} elseif ($type == 'draft') {
			$msg_title = "您收到一条新话题预览";
			$msg_desc = "话题： " . $thread['subject'];
			$msg_desc.= "\n摘要：" . rhtmlspecialchars(substr(strip_tags($thread['message']), 0, 120));
			$msg_url = $viewurl;
			$msg_picurl = '';
			/* $img = '';
			$this->_get_first_img($thread['message'], $img);
			$msg_picurl = $img; */
		}
		if (empty($users)) {
			return true;
		}
		$touser = implode('|', $users);
		$toparty = '';
		logger::error($touser);
		// 过滤html实体
		$msg_desc = strip_tags($msg_desc);
		// 发送消息
		voa_h_qymsg::push_news_send_queue($session_obj, $msg_title, $msg_desc, $msg_url, $touser, $toparty);

		return true;
	}

	/**
	 * 提取第一张图片src地址
	 */
	protected function  _get_first_img($request, &$result) {

		$imgs_url = '';
		$result = '';
		$pattern = '/<img.+src=\"?(.+\.(jpg|gif|bmp|bnp|png))\"?.+>/i';
		preg_match($pattern, $request, $imgs_url);
		if (!empty($imgs_url[1])) {
			$result = $imgs_url[1];
		}

		return true;
	}

}
