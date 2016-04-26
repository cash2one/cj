<?php
/**
 * 巡店相关的入库操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_inspect_add extends voa_uda_frontend_inspect_abstract {

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
		// 入库的数据
		$fields = array(
			array('csp_id', self::VAR_INT, array($this->_serv, 'chk_csp_id'), null, false)
		);
		$inspect = array();
		if (!$this->extract_field($inspect, $fields)) {
			return false;
		}

		// 检查用户信息是否为空
		$user = $this->get('_user', array());
		if (empty($user)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_common::USER_IS_NOT_EXIST);
			return false;
		}

		$inspect['m_uid'] = $user['m_uid'];
		$inspect['m_username'] = $user['m_username'];
		$out = $this->_serv->insert($inspect);

		return true;
	}

}
