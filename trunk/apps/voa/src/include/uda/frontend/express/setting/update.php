<?php
/**
 * voa_uda_frontend_express_setting_update
 * 统一数据访问/快递助手/设置/修改
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_express_setting_update extends voa_uda_frontend_express_setting_abstract {

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
		// 提取用户提交的数据
		$fields = array(
			array('m_uids', self::VAR_STR, null, null, true),
		    array('cd_ids', self::VAR_STR, null, null, true)
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}
		$this->_serv->update_setting($data);

		return true;
	}

}
