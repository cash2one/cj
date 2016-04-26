<?php
/**
 * voa_uda_frontend_diy_option_update
 * 产品选项更新
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_diy_option_update extends voa_uda_frontend_diy_option_abstract {

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
		// 提取数据
		$data = array();
		if (!$this->__parse_gp($data, true)) {
			return false;
		}

		// 更新条件
		$conds = array(
			'tco_id' => (int)$this->get('tco_id'),
			'tid' => self::$_s_table['tid']
		);
		$this->_serv_tablecolopt->update_by_conds($conds, $data);

		return true;
	}

}
