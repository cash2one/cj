<?php
/**
 * base.php
 * uc前端基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_uc_home_base extends voa_c_uc_base {

	/** 用于储存返回给登录端的url的cookie名 */
	protected $_redirect_url_cookie_name = '_redirect_url_';

	/**
	 * 客户端返回的url
	 * 用于客户端提交登录、注册等请求操作成功后的返回url
	 * @var string
	 */
	protected $_redirect_url = '';

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		// 保存客户端请求返回的url到cookie内，避免因页面跳转导致url字符串丢失
		$redirect_url = $this->request->get('redirect_url');
		$redirect_url = (string)$redirect_url;
		if ($redirect_url) {
			$this->_redirect_url = $redirect_url;
			$this->session->set($this->_redirect_url_cookie_name, $this->_redirect_url);
		}
		unset($_redirect_url);

		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 获取客户端要求的返回url
	 * @param array $data 待GET传递的数据
	 * @return string
	 */
	protected function _get_redirect_url($data = array()) {

		// 默认自cookie获取 redirect_url
		$redirect_url = $this->session->get($this->_redirect_url_cookie_name);
		if ($this->_redirect_url) {
			// 尝试获取到来自外部（get、post）请求的redirect_url
			$redirect_url = $this->_redirect_url;
		}

		$redirect_host = '';
		if ($redirect_url) {
			// 解析返回url进行验证
			$parse_url = @parse_url($redirect_url);
			if (!$parse_url || !isset($parse_url['host'])) {
				// 非法的url
				$redirect_url = '';
			} else {
				$clients = config::get('uc.clients');
				$redirect_host = rstrtolower($parse_url['host']);
				if (!isset($clients[$redirect_host])) {
					// 指定的返回主机不在有效的应用列表里
					$redirect_url = '';
				}
			}
		}

		if ($redirect_url) {
			// 存在返回url，则尝试附加传递的数据

			if ($data && is_array($data)) {
				if (!isset($data['submit_time'])) {
					$data['submit_time'] = startup_env::get('timestamp');
				}
				$data = serialize($data);
				// 加密字符串，有效期为120秒
				// 需要确保加密方式与客户端保持一致
				$data = authcode($data, $clients[$redirect_host], 'ENCODE', 120);

				if (substr($redirect_url, -1) == '=') {
					// 返回链接已经指定了参数名
					$redirect_url .= $data;
				} else {
					// 未指定参数名，则定义传递的数据的参数名为“_uc_data”
					if (strpos($redirect_url, '?') === false) {
						$redirect_url .= '?';
					} else {
						$redirect_url .= substr($redirect_url, -1) != '?' ? '&' : '';
					}
					$redirect_url .= '_uc_data='.$data;
				}
			}
		} else {
			// 无返回链接，则返回到主站
			$redirect_url = 'http://www.vchangyi.com/';
		}

		return $redirect_url;
	}

	/**
	 * 清除跳转url的cookie储存
	 * @return boolean
	 */
	public function _clear_redirect_url_cache() {
		$this->session->set($this->_redirect_url_cookie_name, '');
		return true;
	}

	/**
	 * 将uc用户数据库数据转换为客户端格式
	 * @param array $member
	 * @return array
	 */
	public function _member2client($member) {
		return array(
			'id' => $member['m_id'],
			'mobilephone' => $member['m_mobilephone'],
			'email' => $member['m_email'],
			'realname' => $member['m_realname']
		);
	}

}
