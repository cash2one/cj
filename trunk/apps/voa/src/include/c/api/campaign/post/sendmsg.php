<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/8/27 0027
 * Time: 13:04
 */
class voa_c_api_campaign_post_sendmsg extends voa_c_api_campaign_base {
	//不强制登录，允许外部访问
	protected function _before_action($action) {

		$this->_require_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

		if (!$this->__check_params($this->_params)) {
			return false;
		}

		// 判断当前预览人ID是否为空
		if (!isset($this->_params['m_uids'])) {
			$this->_set_errcode(voa_errcode_api_campaign::MUID_NULL);
			return false;
		}

		$is_push = 0; // 当前状态为编辑
		$m_uid = $this->_params['m_uids']; // 获取当前预览人m_uid
		$cid = $this->_params['cid']; // 获取当前活动ID

		$uda = &uda::factory('voa_uda_frontend_campaign_customcols');
		$uda->add_customcols($this->_params, $is_push);

//		/* 判断发送次数 */
//		if ($this->session->get('cid')) {
//			$this->_set_errcode(voa_errcode_api_campaign::SEND_OUT);
//			return false;
//		}

		$this->session->set('cid', $cid, 300);

		// 发送消息
		$this->__to_queue($m_uid, $cid);

		return true;
	}

	/**
	 * 发送预览信息
	 * @param $uid
	 * @param $cid
	 * @throws service_exception
	 */
	private function __to_queue($uid, $cid) {

		$msg_title = "您收到一条预览信息";
		$viewurl = '';
		$uda_get = &uda::factory('voa_uda_frontend_campaign_get');
		$data = $uda_get->get_act($cid);

		if (empty($data)) {
			$this->_set_errcode(voa_errcode_api_campaign::SUBJECT_NULL);
			return false;
		}

		$msg_desc = "主题：" . $data['subject'];
		$touser = array($uid);
		$toparty = '';
		$this->get_preview_url($viewurl, $cid);
		if (!empty($data['cover'])) {
			$msg_picurl = voa_h_attach::attachment_url($data['cover'], 0);
		}
		$msg_url = $viewurl;

		// 发送消息
		voa_h_qymsg::push_news_send_queue($this->session, $msg_title, $msg_desc, $msg_url, $touser, $toparty, $msg_picurl);//发消息

	}

	/**
	 * 判断当前是否上传图片
	 *
	 * @param
	 *            $con
	 * @return bool
	 */
	private function __check_params($con) {

		if (empty($con)) {
			$this->_set_errcode(voa_errcode_api_campaign::DATA_NULL);
			return false;
		}

		// 图片不能为空
		if (isset($con['is_photo']) && !empty($con['is_photo'])) {
			if (empty($con['cover'])) {
				$this->_set_errcode(voa_errcode_api_campaign::PIC_NULL);
				return false;
			}
		}

		return true;
	}
}

//end