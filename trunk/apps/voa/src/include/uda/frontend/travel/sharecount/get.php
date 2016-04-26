<?php
/**
 * voa_uda_frontend_travel_sharecount_get
 * 获取分享统计
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_sharecount_get extends voa_uda_frontend_travel_abstract {
	// sharecount service
	protected $_s_sharecount = null;

	public function __construct($pset = null) {

		parent::__construct($pset);
		$this->_s_sharecount = new voa_s_oa_travel_sharecount();
	}

	/**
	 * 执行
	 * @param array $in 输入参数
	 * @param array $out 输出参数
	 * @return boolean
	 */
	public function execute($in, &$out = null) {

		$this->_params = $in;
		// 获取更改数据
		$fields = array(
			array('uid', self::VAR_INT, null, null),
			array('goods_id', self::VAR_INT, null, null),
			array('sig', self::VAR_STR, null, null)
		);
		if (!$this->extract_field($data, $fields)) {
			return false;
		}

		// 数据入库
		$out = $this->_s_sharecount->get_by_conds($data);

		return true;
	}

}
