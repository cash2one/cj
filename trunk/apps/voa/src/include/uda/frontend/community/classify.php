<?php

/**
 * voa_uda_frontend_news_category
 * 统一数据访问/新闻公告/类型设置
 *
 * $Author$
 * $Id$
 */
class voa_uda_frontend_community_classify extends voa_uda_frontend_community_abstract {

	/** service 类 */
	private $__service = null;

	public function __construct() {

		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_community_category();
		}
	}

	/**
	 * 获取单条
	 * @param $classid
	 * @return bool
	 */
	public function get_classify($classid) {

		$result = $this->__service->get($classid);
		if ($result) {
			return $result;
		}

		return true;
	}

	/**
	 * 获取所有
	 * @param $classid
	 * @return bool
	 */
	public function list_all_classify(&$result) {

		$result = $this->__service->list_all();

		return true;
	}

	/**
	 * 处理编辑分类
	 * @param $cond
	 */
	public function handle($tid = 0, $request) {

		if (empty($tid)) {

			$result = $this->__service->insert($request);

			return $result;
		}
		$result = $this->__service->update($tid, $request);

		return $result;
	}

	/**
	 * 删除分类
	 * @param $tid
	 */
	public function classify_del($tid) {

		$result = $this->__service->delete($tid);

		if ($result) {
			return true;
		}

		return false;
	}

	/**
	 * 根据条件查找列表
	 * @param array $conds 条件数组
	 * @param int|array $pager 分页参数
	 */
	public function list_classify($conds, $pager) {

		$result = array();
		$result['list'] = $this->_list_news_by_conds($conds, $pager);
		$result['total'] = $this->_count_news_by_conds($conds);

		return $result;
	}

	/**
	 * 根据条件查找
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
	 * 根据条件数据数量
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
	private function __format_list(&$list) {

		if ($list) {
			//
			foreach ($list as $k => &$v) {
				$v['updated'] = rgmdate($v['updated'], 'Y-m-d H:i');
			}
		}
	}

}
