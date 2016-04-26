<?php

/**
 * voa_c_frontend_invite_share
 * 邀请人员/分享页面
 * Created by zhoutao.
 * Created Time: 2015/7/9  10:20
 */
class voa_c_frontend_invite_share extends voa_c_frontend_invite_base {

	// 当前时间戳(加密)
	private $__en_timestamp = null;
	// 当前时间戳(未加密)
	private $__de_timestamp = null;
	// 当前m_uid(加密)
	private $__en_m_uid = null;
	// 当前m_uid(未加密)
	private $__de_m_uid = null;
	// 分享的链接
	private $__share_url = null;

	protected function _before_action($action) {
		// 不强制登录
		$this->_auto_login = false;

		if (!parent::_before_action($action)) {
			return false;
		}

		// 获取登录用户id
		$this->__de_m_uid = startup_env::get('wbs_uid');
		$this->__de_timestamp = startup_env::get('timestamp');
		$this->__en_m_uid = $this->request->get('m_uid');
		$get_timestamp = $this->request->get('timestamp');

		if (empty($get_timestamp) || empty($this->__en_m_uid)) {
			if (!empty($this->__de_m_uid)) {
				$this->_encryption($this->__de_timestamp, $this->__en_timestamp);
				$this->_encryption($this->__de_m_uid, $this->__en_m_uid);
				$this->redirect("/frontend/invite/share?timestamp=" . $this->__en_timestamp . "&m_uid=" . $this->__en_m_uid);
			}
		} else {
			$this->_deciphering($this->__en_m_uid, $get_uid);
			if (empty($this->__de_m_uid) ||
				$get_uid != $this->__de_m_uid) {
				$url = "/frontend/invite/introduction?timestamp=" . $get_timestamp . "&m_uid=" . $this->__en_m_uid;
				$this->redirect($url);
			}
		}

		$this->_encryption($this->__de_timestamp, $this->__en_timestamp);
		return true;
	}

	public function execute() {


		// 判断是否在可邀请列表里
		$primary_id = explode(',', $this->_invite_setting['primary_id']);
		if (!in_array($this->__de_m_uid, $primary_id)) {
			$this->_no_authority('您尚无权限邀请人员，请联系管理员开通', null, null, '发生错误');
		}
		$member = voa_h_user::get($this->__de_m_uid);

		// 生成分享链接
		$scheme = config::get('voa.oa_http_scheme');
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$this->__share_url = $scheme . $sets['domain'] . "/frontend/invite/introduction?timestamp=" . $this->__en_timestamp . "&m_uid=" . $this->__en_m_uid;

		//从缓存获取过期时间
		$overdue = $this->__de_timestamp + $this->_invite_setting['overdue'];
		$overdue = rgmdate($overdue, 'Y-m-d');

		// 获取企业号名称
		$sitename = $sets['sitename'];

		// 企业logo
		if (!empty($this->_invite_setting['logo'])) {
			$logo = voa_h_attach::attachment_url($this->_invite_setting['logo']);
			$this->view->set('logo', $logo); // logo图片地址
		}

		/*
		 * 分享链接的信息
		 */
		// 应用默认图标
		$icon_url = config::get(startup_env::get('app_name') . '.oa_http_scheme');
		$icon_url .= $this->_setting['domain'] . '/admincp/static/images/application/invite.png';
		// 分享链接（跳转企业介绍）
		$link = $this->__share_url;
		$share_data = array(
			'title' => $member['m_username'] . '邀请您加入【' . $sitename . '】',// 分享标题
			'desc' => $this->_invite_setting['short_paragraph'],// 分享描述
			'link' => $link,// 分享链接
			'imgUrl' => $icon_url,// 分享图标
			//'type' => '',// 分享类型,music、video或link，不填默认为link
			//'dataUrl' => '',// 如果type是music或video，则要提供数据链接，默认为空
			//'cb_success' => '',// 成功时的回调函数名
			//'cb_cancel' => ''// 失败时的回调函数名
		);

		// 给二维码的时间戳
		$this->view->set('qrcode_timestamp', $this->__en_timestamp);
		// 给二维码的m_uid
		$this->view->set('m_uid', $this->__en_m_uid);
		// 二维码的绝对路径
		$url = $scheme . $sets['domain'] . "/frontend/invite/";
		$this->view->set('url', $url);

		$this->view->set('share_data', $share_data); // 分享链接的信息
		$this->view->set('short_paragraph', $this->_invite_setting['short_paragraph']); // 分享邀请语
		$this->view->set('sitename', $sitename);
		$this->view->set('username', $member['m_username']);
		$this->view->set('overdue', $overdue);
		$this->view->set('share_url', $this->__share_url);
		$this->view->set('navtitle', '邀请人员');
		$this->_output('mobile/invite/share');
		return true;
	}


}
