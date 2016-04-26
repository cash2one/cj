<?php
/**
 * voa_uda_frontend_interface_steplist
 * 统一数据访问/测试应用/流程列表
 *
 * gaosong
 * $Id$
 */

class voa_uda_frontend_interface_flowlist extends voa_uda_frontend_interface_abstract {

	/**
	 * 返回状态中文
	 *
	 * @param mixed $status	整数或空
	 * @return string	字符串或数组
	 */
	function status($status = null)
	{
		$map = array(
			'0'	=>	'未执行',
			'1'	=>	'执行中',
			'2'	=>	'已执行'
		);
		if($status) {
			return $map[$status] ? $map[$status] : '未执行';
		}
		return '未执行';
	}

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
			array('f_name', self::VAR_STR, null, null, true),
			array('cp_pluginid', self::VAR_INT, null, null, true),
			array('page', self::VAR_INT, null, null, true),
			array('perpage', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		if (!empty($conds['f_name'])) {
			$conds['f_name like ?'] = "%".$conds['f_name']."%";
		}

		if (isset($conds['f_name'])) {
			unset($conds['f_name']);
		}
		if (empty($conds['cp_pluginid'])) {
			unset($conds['cp_pluginid']);
		}

		$option = array();
		// 分页信息
		$this->_get_page_option($option, $conds);

		$t = new voa_d_oa_interface_flow();

		$this->_total = $t->count_by_conds($conds);
		// 读取表格字段
		$out = $t->list_by_conds($conds, $option, array('created' => 'DESC'));
		if (empty($out)) {
			$out = array();
		}

		return true;
	}

	/**
	 * 重置执行操作
	 * @param $f_id
	 */
	public function update_exec($f_ids) {

		$t = new voa_d_oa_interface_flow();
		// 设置条件
		$conds = array('f_exec' => 0);

		$fid_arr = explode("," ,$f_ids['f_id']);
		// 更新状态
		$t->update($fid_arr, $conds);

		return true;
	}

}
