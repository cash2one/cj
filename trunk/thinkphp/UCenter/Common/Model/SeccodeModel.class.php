<?php
/**
 * SeccodeModel.class.php
 * $author$
 */

namespace Common\Model;

class SeccodeModel extends AbstractModel {
	// 已使用
	const CODE_USED = 1;
	// 未使用
	const CODE_UNUSE = 2;

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 根据 formhash 和 seccode 读取记录
	 * @param string $formhash hash值
	 * @param string $seccode 验证码
	 */
	public function get_by_formhash_seccode($formhash, $seccode) {

		return $this->_m->fetch_row('SELECT * FROM __TABLE__ WHERE `formhash`=? AND `code`=? AND `status`<? ORDER BY `id` DESC LIMIT 1', array(
			$formhash, $seccode, $this->get_st_delete()
		));
	}

	public function get_code_used() {

		return self::CODE_USED;
	}

	public function get_code_unuse() {

		return self::CODE_UNUSE;
	}

}
