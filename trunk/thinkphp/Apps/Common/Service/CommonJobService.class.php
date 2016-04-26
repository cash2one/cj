<?php
/**
 * CommonJobService.class.php
 * $author$
 */

namespace Common\Service;
use Common\Service\AbstractService;

class CommonJobService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Common/CommonJob');
	}

	/**
	 * 读取所有职位信息
	 * @see \Com\Service::list_all()
	 */
	public function list_all($page_option = null, $order_option = array()) {

		$list = parent::list_all($page_option, $order_option);

		return array_combine_by_key($list, 'cj_id');
	}

	/**
	 * 获取表的默认值
	 * @return mixed
	 */
	public function list_field() {

		$rows =  $this->_d->list_field();
		$data = array();
		foreach ($rows as $_key => $_val) {
			$data[$_val['field']] = $_val['default'];
		}

		return $data;
	}

}
