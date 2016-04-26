<?php

/**
 * Created by PhpStorm.
 * 添加 编辑 客户
 * User: zhoutao
 * Date: 15/10/17
 * Time: 下午4:31
 */
class voa_c_cyadmin_api_company_add extends voa_c_cyadmin_api_base {

	protected $_uc_api_url = '';

	public function execute() {

		// 获取数据
		$post = $this->request->postx();
		$uda = &uda::factory('voa_uda_cyadmin_company_add');
		$error = array();

		/** 添加 */
		if (isset($post['act']) && $post['act'] == 'add') {
			// 验证数据
			if (!$uda->filter($post, $error)) {
				$this->_errcode = $error['errcode'];
				$this->_errmsg = $error['errmsg'];

				return false;
			}

			// 提交到UC开通
			if (!$this->_post_to_uc($post)) {
				return false;
			}

			// 根据手机号 添加其他数据
			$result = array();
			$uda->insert_other_data($post, $result);

			$this->_errcode = 0;
			$this->_errmsg = '添加成功';
			/** 编辑 */
		} elseif (isset($post['act']) && $post['act'] == 'edit') {
			// 验证数据
			if (!$uda->filter_edit($post, $error)) {
				$this->_errcode = $error['errcode'];
				$this->_errmsg = $error['errmsg'];

				return false;
			}

			// 更新数据
			$result = array();
			if (!$uda->update_edit($post, $result)) {
				$this->_errcode = $result['errcode'];
				$this->_errmsg = $result['errmsg'];

				return false;
			}

			$this->_errcode = 0;
			$this->_errmsg = '编辑成功';
			/** 丢失参数 */
		} else {
			$this->_errcode = '30000';
			$this->_errmsg = '缺少必要参数:act';

			return false;
		}

		return true;
	}

	/**
	 * 提交到UC
	 */
	protected function _post_to_uc($post) {

		// 获取UC路径
		$this->_set_uc_api_url(config::get('voa.uc_url') . 'uc/api/post/register/');

		// 将传送过来的POST原始数据接收
		$post['ref_domain'] = '';

		// 与接口通讯的http方法
		$http_method = 'POST';

		/** UC接口开通 */

		// 生成验证码
		$seccode = random(6, true);
		$serv_smscode = &service::factory('voa_s_uc_smscode');
		$post_data = array(
			'smscode_mobile' => $post['ep_mobilephone'], // 手机号
			'smscode_code' => $seccode, // 随机六位数
			'smscode_ip' => $this->request->get_client_ip() // IP地址
		);
		$serv_smscode->insert($post_data);

		// 获取验证码扰码串
		$post_data = array(
			'smscode' => $seccode,
			'mobilephone' => $post['ep_mobilephone'],
		);
		if (!voa_h_func::get_json_by_post_and_header($data, $this->_uc_api_url, $post_data, array(), $http_method, $visit_reporting)) {
			return false;
		}
		if (isset($data['errcode']) && $data['errcode'] != 0) {
			$this->_errcode = $data['errcode'];
			$this->_errmsg = $data['errmsg'];

			return false;
		}

		// 企业信息写入
		$post_data = array(
			'mobilephone' => $post['ep_mobilephone'],
			'smsauth' => $data['result']['smsauth'],
			'realname' => $post['ep_contact'],
			'email' => $post['ep_email'],
			'ename' => $post['ep_name'],
			'industry' => $post['ep_industry'],
			'companysize' => $post['ep_companysize'],
			'enumber' => substr($post['ep_domain'], 0, - 13),
			'password' => $post['ep_password'],
		);
		if (!voa_h_func::get_json_by_post_and_header($data, $this->_uc_api_url, $post_data, array(), $http_method, $visit_reporting)) {
			return false;
		}
		if (isset($data['errcode']) && $data['errcode'] != 0) {
			$this->_errcode = $data['errcode'];
			$this->_errmsg = $data['errmsg'];

			return false;
		}

		// DNS写入 和发邮件
		$post_data = array(
			'submitauth' => $data['result']['submitauth'],
			'mobilephone' => $post['ep_mobilephone'],
			'ref' => config::get('voa.cyadmin_domain.ref')
		);
		if (!voa_h_func::get_json_by_post_and_header($data, $this->_uc_api_url, $post_data, array(), $http_method, $visit_reporting)) {
			return false;
		}
		if (isset($data['errcode']) && $data['errcode'] != 0) {
			$this->_errcode = $data['errcode'];
			$this->_errmsg = $data['errmsg'];

			return false;
		}

		return true;
	}

	/**
	 * 给出一个uc的接口相对路径，设定完整uc接口路径
	 * @param string $path
	 * @return string
	 */
	protected function _set_uc_api_url($path = '') {

		// uc 接口根url
		$this->_uc_api_url = config::get('frontend.uc_url') . $path;
	}


}
