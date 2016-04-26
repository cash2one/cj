<?php
/**
 * voa_uda_frontend_thread_setting_update
 * 统一数据访问/社区应用/设置/修改
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_thread_setting_update extends voa_uda_frontend_thread_setting_abstract {

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
			array('offical_name', self::VAR_STR, null, null, true),
		    array('offical_img', self::VAR_STR, null, null, true),
		    array('hot_key', self::VAR_STR, null, null, true),
		    array('hot_value', self::VAR_STR, null, null, true),
		    array('choice_key', self::VAR_STR, null, null, true),
		    array('choice_value', self::VAR_STR, null, null, true)
		);
		$data = array();
		if (!$this->extract_field($data, $fields)) {
			return false;
		}
		$this->_serv->update_setting($data);
		
		return true;
	}

}
