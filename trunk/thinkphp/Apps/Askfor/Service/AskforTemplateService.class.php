<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/11/5
 * Time: 下午2:19
 */
namespace Askfor\Service;

class AskforTemplateService extends AbstractService {
	// 构造方法
	public function __construct() {

		$this->_d = D("Askfor/AskforTemplate");
		parent::__construct();
	}

	/**
	 * 根据aft_id 获取数据
	 * @return mixed
	 */
	public function get_by_aft_id($aft_id) {

		return $this->_d->get_by_aft_id($aft_id);
	}

	/**
	 * 根据orderid排序
	 * @param array $order_option 排序条件
	 * @return mixed
	 */
	public function list_all_orderby_orderid($order_option){

		return $this->_d->list_all_orderby_orderid($order_option);
	}
}
