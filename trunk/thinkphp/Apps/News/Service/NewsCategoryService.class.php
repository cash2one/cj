<?php
/**
 * 新闻公告 分类列表 Service
 * User: Muzhitao
 * Date: 2015/9/15 0015
 * Time: 14:21
 * Email:muzhitao@vchangyi.com
 */
namespace  News\Service;

class NewsCategoryService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("News/NewsCategory");
	}

	/**
	 * 格式化数组
	 * @param $data
	 * @param $result
	 * @return mixed
	 */
	public function format_data($data, &$result) {

		// 如果为空，返回空数组
		if (empty($data)) {
			return $result;
		}

		// 循环取出相关字段
		foreach ($data as $_key => $_v) {
			$result[$_key]['nca_id'] = $_v['nca_id'];
			$result[$_key]['parent_id'] = $_v['parent_id'];
			$result[$_key]['name']   = $_v['name'];
			$result[$_key]['orderid']   = $_v['orderid'];
		}
	}

	/**
	 * 格式化分类列表
	 * @param $data
	 * @param $result
	 */
	public function format_cate($data, &$result) {

		$this->format_data($data, $results);

		//整理输出
		$result = array();
		foreach ($results as $cat) {
			if ($cat['parent_id'] == 0) {
				$result[$cat['nca_id']]['nca_id'] = $cat['nca_id'];
				$result[$cat['nca_id']]['name'] = $cat['name'];
				$result[$cat['nca_id']]['orderid'] = $cat['orderid'];
			}
			if ($cat['parent_id'] != 0) {
				$result[$cat['parent_id']]['nodes'][] = $cat;
			}
		}

		//排序
		$orderids = array();
		foreach ($result as &$cat) {
			$orderids[] = $cat['orderid'];
			$suborderids = array();
			if (isset($cat['nodes'])) {
				foreach ($cat['nodes'] as $sub) {
					$suborderids[] = $sub['orderid'];
				}
				array_multisort($suborderids, SORT_ASC, $cat['nodes']);
			}
		}
		array_multisort($orderids, SORT_ASC, $result);
	}
}

// end
