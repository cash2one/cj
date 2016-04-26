<?php
/**
 * voa_c_api_auth_get_authdata
 * 获取企业成员登录二维码、输出二维码图片
 * 注：接口本身不需要外部参数。
 * 提供外部参数时，用于输出二维码图片数据
 *
 * 思路：
 * 请求本接口时（不需要参数），随机生成一个字符串（唯一）用于当前访客的身份标识
 * 将该字符串写入数据表（oa_member_loginqrcode）记录备用
 *
 * 同时，利用该身份标识字符串生成用于检测当前访客是否登录的URL
 * 以及用于扫描的二维码图片URL
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_auth_get_authdata extends voa_c_api_auth_base {

	//不强制登录，允许外部访问
	protected function _before_action($action) {
		$this->_auto_login = false;
		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}

	/** 当前访客的唯一标识字符串 */
	private $__authcode = '';

	/** 验证authcode字符串 */
	private $__singture = '';

	/** 记录当前时间戳 */
	private $__timestamp = '';

	/** 加密密钥 */
	private $__state_secret_key = '';

	public function execute() {

		// 记录当前时间戳
		$this->__timestamp = startup_env::get('timestamp');

		// 加密时间戳
		$this->__state_secret_key = config::get('voa.auth_key');
		$this->__timestamp = rbase64_encode(authcode($this->__timestamp, $this->__state_secret_key, 'ENCODE', ''));

		// 生成唯一标识符
		$this->__authcode();
		// 生成验证authcode字符串
		$this->__singture();

		// authcode存入数据库
		$uda = &uda::factory('voa_uda_frontend_auth_insert');
		$out = null;
		$data = array(
			'authcode' => $this->__authcode,
			'ip' => $this->request->get_client_ip()
		);
		$uda->insert_authcode($data, $out);

		// 生成二维码图片地址
		$data = array(
			'authcode' => $this->__authcode,
			'singture' => $this->__singture,
			'timestamp'=> $this->__timestamp
		);
		$sets = voa_h_cache::get_instance()->get('setting', 'oa');
		$scheme = config::get('voa.oa_http_scheme');
		$qrcode_url = $scheme . $sets['domain'] . "/frontend/auth/qrcode?authcode=" . $data['authcode'] . "&singture=" . $data['singture'] . "&timestamp=" . $this->__timestamp;
		// 检查扫描结果状态URL
		$check_login = $scheme . $sets['domain'] . "/api/auth/get/authlogin?authcode=" . $data['authcode'] . "&singture=" . $data['singture'] . "&timestamp=" . $this->__timestamp;

		// 返回结果
		$this->_result = array(
			'qrcode_url' => $qrcode_url, // 二维码图片 URL
			'checkqrcode_url' => $check_login // 检查扫描结果状态 URL
		);

		return true;
	}

	/**
	 * 生成身份唯一标识字符串
	 * @return string
	 */
	private function __authcode() {
		if ($this->__authcode !== '') {
			// 已经生成了唯一标识符，则直接返回
			return $this->__authcode;
		}
		// 生成唯一标识符
		$this->__authcode = md5(uniqid() . "\t". $this->__timestamp);

		return $this->__authcode;
	}

	/**
	 * 生成authcode验证字符串
	 * @return string
	 */
	private function __singture() {
		if ($this->__singture !== '') {
			// 已经生成了验证标识符，则直接返回
			return $this->__singture;
		}
		// 生成验证标识符
		$this->__singture = md5($this->__authcode . $this->__timestamp . config::get('voa.auth_key'));

		return $this->__singture;
	}

}
