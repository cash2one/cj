<?php
/**
 * voa_uda_frontend_exam_tj
 * 统一数据访问/统计相关操作
 * Create By wogu
 * $Author$
 * $Id$
 */

class voa_uda_frontend_exam_tj extends voa_uda_frontend_base {
	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_exam_tj();
		}
	}

	/**
	 * 根据条件查找统计结果
	 * @param array $conds 条件数组
	 * @param int|array $pager 分页参数
	 */
	public function list_tj(&$result, $conds, $start, $limit) {
		$result =  $this->__service->list_stats_by_conds($conds, $start, $limit);
		return true;
	}


	/**
	 * 根据条件计算数据数量
	 * @param array $conds
	 * @return number
	 */
	public function count_by_conds($conds) {
		$total = $this->__service->count_by_conds($conds);
		return $total;
	}
}
