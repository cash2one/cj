<?php
/**
 * voa_uda_frontend_mpuser_update
 * 统一数据访问/公众号用户/编辑服务号用户
 * $Author$
 * $Id$
 */

class voa_uda_frontend_mpuser_update extends voa_uda_frontend_base {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 输入参数
	 * @param array $in 输入参数
	 * @param array &$out 输出参数
	 * @return boolean
	 */
	public function execute($in, &$out) {

		$this->_params = $in;
		// 提取数据
		$fields = array(
			array('password', self::VAR_STR, null, null, true),
			array('salt', self::VAR_STR, null, null, true),
			array('saleid', self::VAR_STR, null, null, true),
			array('web_access_token', self::VAR_STR, null, null, true),
			array('web_token_expires', self::VAR_STR, null, null, true),
			array('mobilephone', self::VAR_STR, null, null, true),
			array('email', self::VAR_STR, null, null, true),
			array('unionid', self::VAR_STR, null, null, true),
			array('username', self::VAR_STR, null, null, true),
			array('index', self::VAR_STR, null, null, true),
			array('groupid', self::VAR_STR, null, null, true),
			array('gender', self::VAR_STR, null, null, true),
			array('face', self::VAR_STR, null, null, true),
			array('facetime', self::VAR_STR, null, null, true)
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		// mpuid
		$mpuid = (string)$this->get('mpuid');

		// 入库
		$serv = new voa_s_oa_mpuser();
		$out = $serv->update($mpuid, $data);

		return true;
	}

}
