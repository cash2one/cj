<?php
/**
 * EnterpriseAdminerModel.class.php
 * $author$
 */

namespace Common\Model;

class EnterpriseAdminerModel extends AbstractModel {

	// 正常状态
	const USER_ST_NORMAL = 1;
	// 禁止状态
	const USER_ST_BANED = 2;

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	public function list_user_status() {

		return array(self::USER_ST_NORMAL, self::USER_ST_BANED);
	}

}
