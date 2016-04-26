<?php
/**
 * voa_uda_frontend_diy_option_add
 * 统一数据访问/商品应用/基类
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_option_add extends voa_uda_frontend_diy_option_abstract {

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
		$tablecolopt = array('tid' => self::$_s_table['tid']);
		if (!$this->_parse_gp($tablecolopt)) {
			return false;
		}

		// 数据处理类
		$out = $this->_serv_tablecolopt->insert($tablecolopt);

		return true;
	}

}
