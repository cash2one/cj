<?php
/**
 * Created by PhpStorm.
 * User: ChangYi
 * Date: 2015/6/29
 * Time: 15:16
 */

class voa_uda_cyadmin_agent_list extends voa_uda_cyadmin_base {

	/** service 类 */
	private $__service = null;

	public function __construct() {
		parent::__construct();
		if ($this->__service === null) {
			$this->__service = new voa_s_cyadmin_agent_index();
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
		$list = $this->__service->list_by_conds($conds, $pager, array('created' => 'DESC'));
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
			foreach ($list as $k => &$v) {
				$v['created'] = rgmdate($v['created'], 'Y-m-d H:i');
			}
		}
	}


}
