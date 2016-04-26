<?php
/**
 * voa_uda_frontend_interface_steplist
 * 统一数据访问/测试应用/流程步骤列表
 *
 * gaosong
 * $Id$
 */

class voa_uda_frontend_interface_steplist extends voa_uda_frontend_interface_abstract {


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
		// 查询表格的条件
		$fields = array(
			array('page', self::VAR_INT, null, null, true),
			array('perpage', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		$option = array();
		// 分页信息
		$this->_get_page_option($option, $conds);

		$t = new voa_d_oa_interface_paramter();

		$this->_total = $t->count_by_conds($conds);
		// 读取表格字段
		$out = $t->list_by_conds($conds, $option, array('created' => 'DESC'));
		if (empty($out)) {
			$out = array();
		}

		// 判断是否需要过滤
		$this->_fmt && $this->_format($out, true);

		return true;
	}

    /**
     * 接口参数列表
     * @param 条件 $conds
     * @param 返回数据 $datas
     */
	public function list_by_conds($conds,&$datas) {

		$option = array();
		$t = new voa_d_oa_interface_step();

		// 读取表格字段
		$datas = $t->list_by_conds($conds, $option, array('created' => 'ASC'));
		return true;
	}

}
