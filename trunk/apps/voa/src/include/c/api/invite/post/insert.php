<?php
/**
 * voa_c_api_invite_post_insert
 * 提交处理
 * @Author: ppker
 * @Date:   2015-07-09 17:43:49
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-08-04 16:51:55
 */

class voa_c_api_invite_post_insert extends voa_c_api_invite_abstract {

	// 不强制登录，允许外部访问
	protected function _before_action($action) {

		$this->_require_login = false;
		if (! parent::_before_action($action)) {
			return false;
		}

		return true;
	}

	public function execute() {

			// 是否需要核审
		$open = voa_h_cache::get_instance()->get('plugin.invite.setting', 'oa');
		$control = intval($open['is_approval']);
		// 跳转链接
		if ($control) {
			$url = "/frontend/invite/success";
			$sh_message = "申请加入企业号";
		} else {
			$url = "/frontend/invite/concern";
			$sh_message = "已成功加入企业号";
		}

		$post_data = $this->request->postx();
		logger::error(var_export($post_data, true));
		if (! $this->__check_params($post_data)) {
			return false;
		}

		try {
			// 获取数据
			$out = array();
			if ($control) {
				$post_data['approval_state'] = 0;
			} else {
				$post_data['approval_state'] = 3;
			}

			logger::error(var_export($post_data, true));
			$personnel = &uda::factory('voa_uda_frontend_invite_insert');
			$is_true = $personnel->doit($post_data, $out);

			// 发送消息啊
			if ($is_true) {
				$scheme = config::get('voa.oa_http_scheme');
				$settings = voa_h_cache::get_instance()->get('setting', 'oa');
				// $m_uid = startup_env::get( "wbs_uid" ); //m_uid
				$m_uid = $post_data['invite_uid'];
				$per_id = $out['per_id'];
				$f_name = $post_data['name']; // name
				$f_wx_id = $post_data['weixin_id'] ? $post_data['weixin_id'] : ''; // weixin
				$msg_title = $f_name . $sh_message;
				$msg_desc = "姓名: " . $f_name . "\n";
				$msg_desc .= "手机: " . $post_data['phone'] . "\n";
				if (!empty($f_wx_id)) {
					$msg_desc .= "微信号: " . $f_wx_id . "\n";
				}
				if (!empty($post_data['email'])) {
					$msg_desc .= "邮箱: " . $post_data['email'] . "\n";
				}
				$msg_url = $scheme . $settings['domain'] . '/frontend/invite/view/?per_id=' . $per_id . '&pluginid=' . startup_env::get('pluginid');
				// 发送消息
				voa_h_qymsg::push_news_send_queue($this->session, $msg_title, $msg_desc, $msg_url, array("$m_uid"), array(), '', 0, 0, - 1);
			}
		} catch (help_exception $h) {
			return $this->_set_errcode(voa_errcode_api_invite::UNKNOW);
		} catch (Exception $e) {
			return $this->_set_errcode(voa_errcode_api_invite::UNKNOW);
		}

		logger::error('invite ok.');
		// 判断方式
		if ($control) {
			$this->_result = array(
				'per_id' => $out['per_id'],
				'url' => $url,
				'message' => '提交成功，等待核审吧!',
				'success' => 1
			);
		} else {
			$this->_result = array(
				'per_id' => $out['per_id'],
				'url' => $url,
				'message' => '提交成功!',
				'success' => 0
			);
		}

		// 输出结果
		return true;
	}

	/**
	 * 检查请求参数
	 *
	 * @return bool|void
	 */
	private function __check_params(&$post_data) {

		// 名字的检查
		if (empty($this->_params['name'])) {
			return $this->_set_errcode(voa_errcode_api_invite::NAME_NULL);
		}

		if (! empty($this->_params['name']) && strlen($this->_params['name']) > 18) {
			return $this->_set_errcode(voa_errcode_api_invite::NAME_LEN);
		}

		if (! validator::is_mobile($post_data['phone'])) {
			return $this->_set_errcode(voa_errcode_api_invite::PHONE_ERR);
		}

		if (empty($post_data['email'])) {
			$post_data['email'] = $post_data['phone'] . '@vchangyi.com';
			//return $this->_set_errcode(voa_errcode_api_invite::NULL_THREE);
		}

		if (! empty($post_data['email']) && ! validator::is_email($post_data['email'])) {
			return $this->_set_errcode(voa_errcode_api_invite::EMAIL_ERR);
		}

		if (! empty($post_data['weixin_id']) && ! preg_match('/^[a-z]{1}[a-z0-9_-]{4,39}$/i', $post_data['weixin_id'])) { // 匹配微信号
			return $this->_set_errcode(voa_errcode_api_invite::WEI_ERR);
		}

		if (empty($post_data['invite_uid'])) {
			return $this->_set_errcode(voa_errcode_api_invite::UNKNOW);
		}

		return true;
	}

}
