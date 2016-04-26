<?php
/**
 * voa_uda_frontend_news_list
 * 统一数据访问/新闻公告/列表
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_news_list extends voa_uda_frontend_news_abstract {

	/** service 类 */
	private $__service = null;

	public function __construct() {
		parent::__construct();
		if ($this->__service === null) {
			$this->__service = new voa_s_oa_news();
		}
	}

	/**
	 * 根据条件查找新闻公告列表
	 * @param array $conds 条件数组
	 * @param int|array $pager 分页参数
	 */
	public function list_news(&$result, $conds, $pager) {

		$result['list'] =  $this->_list_news_by_conds($conds, $pager);
		$result['total'] = $this->_count_news_by_conds($conds);

		return true;
	}

	/**
	 * 根据条件查找目录
	 * @param array $conds 条件数组
	 * @param int|array $pager 分页参数
	 * @return array $list
	 */
	protected function _list_news_by_conds($conds, $pager) {

		$list = array();
		$list = $this->__service->list_by_conds($conds, $pager, array('updated' => 'DESC'));
		$this->__format_list($list);

		return $list;
	}

	/**
	 * 根据条件计算日报数据数量
	 * @param array $conds
	 * @return number
	 */
	protected function _count_news_by_conds($conds) {

		$total = $this->__service->count_by_conds($conds);

		return $total;
	}

	/**
	 * 格式化数据列表
	 * @param array $list 列表（引用）
	 */
	private function __format_list(&$list)
	{
		if ($list) {
			//获取阅读人数列表
			$ne_ids = array_column($list, 'ne_id');
			$s_read = new voa_s_oa_news_read();
			//载入阅读人员类
			$read = &uda::factory('voa_uda_frontend_news_read');

			foreach ($list as $k => &$v) {
				$v['updated'] = rgmdate($v['updated'], 'Y-m-d H:i');
				$v['_status'] = $this->_status[$v['is_publish']];
				$v['_secret'] = $this->_secret[$v['is_secret']];
				$count_numbers = $s_read->count_users($v['ne_id']);//可阅读总人数
				$v['count_number'] = $count_numbers;
				$numbers = $read->count_real_read_users($v['ne_id']);
				$v['read_number'] = isset($numbers) ? $numbers : '0';//已阅读人数
			}
		}
	}

}
