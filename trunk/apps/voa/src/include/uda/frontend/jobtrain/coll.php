<?php
/**
 * 培训-收藏
 * Create By wowxavi
 * $Author$
 * $Id$
 */

class voa_uda_frontend_jobtrain_coll extends voa_uda_frontend_base {
	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_jobtrain_coll();
		}
	}

	/**
	 * 获取列表
	 * @return array
	 */
	public function list_coll(&$result, $conds, $pager) {
		$result['list'] =  $this->_list_coll_by_conds($conds, $pager);
		$result['total'] = $this->_count_coll_by_conds($conds);
		return true;
	}
	/**
	 * 根据条件查找
	 * @param array $conds 条件数组
	 * @param int|array $pager 分页参数
	 * @return array $list
	 */
	protected function _list_coll_by_conds($conds, $pager) {
		$list = array();
		$list = $this->__service->list_by_conds($conds, $pager, array('id' => 'DESC'));
		return $list;
	}
	/**
	 * 根据条件计算数据数量
	 * @param array $conds
	 * @return number
	 */
	protected function _count_coll_by_conds($conds) {
		$total = $this->__service->count_by_conds($conds);
		return $total;
	}
	
}