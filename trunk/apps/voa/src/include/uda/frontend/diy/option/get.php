<?php
/**
 * voa_uda_frontend_diy_option_get
 * 读取指定产品选项
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_option_get extends voa_uda_frontend_diy_option_abstract {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 输入参数
	 * @param array $in 输入参数
	 * @param array $out 输出参数
	 * @return boolean
	 */
	public function execute($in, &$out) {

		$this->_params = $in;
		$conds = array(
			'tco_id' => (int)$this->get('tco_id'),
			'tid' => self::$_s_table['tid']
		);
		$out = $this->_serv_tablecolopt->get_by_conds($conds);

		return true;
	}

}
