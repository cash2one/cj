<?php
/**
 * voa_uda_frontend_travel_sharecount_update
 * 更新分享统计
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_sharecount_update extends voa_uda_frontend_travel_abstract {
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
		$data = array('`inquirycount`=`inquirycount`+?' => 1);

		// 数据更新
		$this->_s_sharecount->update((int)$this->get('tsc_id'), $data);

		return true;
	}

}
