<?php
/**
 * voa_uda_frontend_express_list
 * 统一数据访问/社区应用/快递列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_express_list extends voa_uda_frontend_express_abstract {

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
			array('username', self::VAR_STR, null, null, true),
			array('flag', self::VAR_INT, null, null, true),
			array('page', self::VAR_INT, null, null, true),
			array('perpage', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		if (!empty($conds['username'])) {
			$conds['username like ?'] = "%".$conds['username']."%";
		}

		if (isset($conds['username'])) {
			unset($conds['username']);
		}

		if(isset($conds['flag'])){
			if($conds['flag'] == 0) {
				unset($conds['flag']);
			}
		}
		$option = array();
		// 分页信息
		$this->_get_page_option($option, $conds);

		$this->_total = $this->_serv->count_by_conds($conds);
		// 读取表格字段
		$out = $this->_serv->list_by_conds($conds, $option, array('created' => 'DESC'));
		if (empty($out)) {
			$out = array();
		}

		// 判断是否需要过滤
		$this->_fmt && $this->_format($out, true);

		return true;
	}

}
