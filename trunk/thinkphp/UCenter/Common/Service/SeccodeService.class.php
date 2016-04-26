<?php
/**
 * SeccodeService.class.php
 * $author$
 */

namespace Common\Service;
use Com\Formhash;

class SeccodeService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Common/Seccode");
	}

	/**
	 * 生成验证码
	 * @param string $formhash hash值
	 * @param string $code 验证码
	 */
	public function generate_seccode($formhash, &$code) {

		// formhash 是32位md5值
		$timestamp = 0;
		if (!\Com\Formhash::instance()->check($formhash, $timestamp)) {
			E('_ERR_FORMHASH_ERROR');
			return false;
		}

		// 获取验证码的时间不能超过配置时间
		if ($timestamp + cfg('FORMHASH_EXPIRE') < NOW_TIME) {
			E('_ERR_FORMHASH_EXPIRED');
			return false;
		}

		// 验证码
		$code = random(4);
		$this->_d->insert(array(
			'formhash' => $formhash,
			'code' => $code
		));

		return true;
	}

	/**
	 * 根据 formhash 和 seccode 读取未使用记录
	 * @param string $formhash hash值
	 * @param string $code 验证码
	 */
	public function get_unuse_by_formhash_seccode(&$sc_result, $formhash, $seccode) {

		$sc_result = $this->_d->get_by_formhash_seccode($formhash, $seccode);
		if (empty($sc_result) || $this->_d->get_code_used() == $sc_result['used']) {
			return false;
		}

		return true;
	}

	/**
	 * 设置指定验证码为已用状态
	 * @param int $id 验证码标识
	 * @return Ambigous <boolean, multitype:>
	 */
	public function set_used($id) {

		return $this->update($id, array('used' => $this->get_code_used()));
	}

	public function get_code_used() {

		return $this->_d->get_code_used();
	}

	public function get_code_unuse() {

		return $this->_d->get_code_unuse();
	}

}
