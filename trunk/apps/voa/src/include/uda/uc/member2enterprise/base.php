<?php
/**
 * base.php
 *
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_uc_member2enterprise_base extends voa_uda_uc_base {

	public $serv_member2enterprise = null;

	/** 用于邮件重置密码的key */
	private $_pwdreset_authkey = '&*A6hhi0qG$Hs%';

	/** 用于邮件重置密码的有效期 */
	public $pwdreset_expire = 7200;

	public function __construct() {
		parent::__construct();
		if ($this->serv_member2enterprise === null) {
			$this->serv_member2enterprise = &service::factory('voa_s_uc_member2enterprise');
		}
	}

	/**
	 * 根据时间和帐号信息生成用于重置密码的key值
	 * @param number $time
	 * @param string $account
	 * @param string $auth <strong style="color:red">(引用结果)</strong>加密扰码信息
	 * @return boolean
	 */
	private function _pwdreset_key_make($enumber, $time, $account, &$auth) {
		$crypt_xxtea = new crypt_xxtea($this->_pwdreset_authkey);
		$auth = rbase64_encode($crypt_xxtea->encrypt($enumber."\t".$time."\t".$account));

		return true;
	}

	/**
	 * 构造用于重置密码的url
	 * @param string $enumber
	 * @param string $account
	 * @param string $url <strong style="color:red">(引用结果)</strong>重置密码的url
	 * @return boolean
	 */
	public function pwdreset_url($enumber, $account, &$url) {
		$key = '';
		$time = startup_env::get('timestamp');
		$this->_pwdreset_key_make($enumber, $time, $account, $key);
		$main_url = config::get('voa.main_url');
		$url = $main_url.'pwdreset/?key='.urlencode($key).'&hash='.md5($key.md5($this->_pwdreset_authkey));

		return true;
	}

	/**
	 * 验证重置密码的key值有效性
	 * @param string $key
	 * @param string $hash
	 * @param array $data <strong style="color:red">(引用结果)</strong>解析后的信息
	 * + enumber
	 * + account
	 * + time
	 * @return boolean
	 */
	public function pwdreset_validator($key = '', $hash = '', &$data) {

		if (strlen($hash) != 32) {
			return $this->error_msg(voa_errcode_uc_pwd::PWD_EMAIL_RESET_HASH_ERROR);
		}

		if ($hash != md5($key.md5($this->_pwdreset_authkey))) {
			return $this->error_msg(voa_errcode_uc_pwd::PWD_EMAIL_RESET_KEY_HASH_ERROR);
		}

		$crypt_xxtea = new crypt_xxtea($this->_pwdreset_authkey);
		if (($auth = @rbase64_decode($key)) === false || !($auth = $crypt_xxtea->decrypt($auth))) {
			return $this->error_msg(voa_errcode_uc_pwd::PWD_EMAIL_RESET_KEY_ILL);
		}

		list($enumber, $time, $account) = explode("\t", $auth);
		if (startup_env::get('timestamp') - $time > $this->pwdreset_expire) {
			return $this->error_msg(voa_errcode_uc_pwd::PWD_EMAIL_RESET_TIMEOUT);
		}

		$data = array(
			'enumber' => $enumber,
			'time' => $time,
			'account' => $account
		);

		return true;
	}

}
