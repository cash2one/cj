<?php
/**
 * voa_uda_frontend_thread_listmine
 * 统一数据访问/社区应用/获取我发表的帖子列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_thread_listmine extends voa_uda_frontend_thread_abstract {

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
			array('uid', self::VAR_INT, null, null, false),
			array('page', self::VAR_INT, null, null, true),
			array('perpage', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields)) {
			return false;
		}

		// 分页信息
		$this->_get_page_option($option, $conds);

		// 读取总数
		$this->_total = $this->_serv->count_by_conds($conds);
		// 读取
		$out = $this->_serv->list_by_conds($conds, $option, array('updated' => 'DESC'));
		if (empty($out)) {
			$out = array();
		}

		// 判断是否需要过滤
		$this->_fmt && $this->_format($out, true);

		return true;
	}

}
