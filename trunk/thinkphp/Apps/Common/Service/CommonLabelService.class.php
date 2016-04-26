<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/12/18
 * Time: 下午2:39
 */

namespace Common\Service;

class CommonLabelService extends AbstractService {

	// 构造方法
	public function __construct() {

		$this->_d = D("Common/CommonLabel");
		parent::__construct();
	}

	/**
	 * 标签列表方法
	 *
	 * @param $params array 参数
	 * @param $page_option 分页条件
	 * @return array 分页数据
	 */
	public function list_label($params) {

		return $this->_d->list_by_conds_label($params);
	}

	/**
	 * 列表二次排序方法
	 *
	 * @param $in array 待排序数组
	 * @return $list array 排序好的数组
	 */
	public function displayorder($in) {

		// 以order为键
		foreach ($in as $val) {
			$order[$val['displayorder']][$val['lastordertime']][] = $val;
		}
		// 排序
		foreach ($order as &$_val) {
			krsort($_val);
		}
		// 最后的数组
		foreach ($order as $__val) {
			foreach ($__val as $__list) {
				foreach ($__list as $v) {
					$list[] = $v;
				}
			}
		}

		return $list;
	}

}
