<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/12/18
 * Time: 下午6:59
 */

namespace Common\Service;

use Common\Service\AbstractService;

class MemberFieldService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D('Common/MemberField');
	}

	/**
	 * 根据uid获取字段
	 * @param $uid_list array 用户id
	 * @param $field array 开启的自定义字段
	 * @return mixed 结果集
	 */
	public function list_field_by_uid($uid_list, $field) {

		return $this->_d->list_field_by_uid($uid_list, $field);
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
