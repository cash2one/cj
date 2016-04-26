<?php
/**
 * Created by PhpStorm.
 * User: ppker
 * Date: 2015/10/26
 * Time: 19:50
 */

namespace OaRpc\Service;

class EnterpriseMessageService extends AbstractService {

	// 构造方法
	public function __construct() {

		$this->_d = D("EnterpriseMessageLog");
		parent::__construct();
	}

	/**
	 * @description 根据条件获取总数
	 * @param $ep_id
	 * @param $re_array
	 * @return mixed
	 */
	public function get_real_count($ep_id, $re_array) {

		return $this->_d->get_real_count($ep_id, $re_array);
	}


	/**
	 * @description 根据条件获取已读数据
	 * @param $ep_id
	 * @param $re_array
	 * @return mixed
	 */
	public function get_yd_count($ep_id, $re_array) {

		return $this->_d->get_yd_count($ep_id, $re_array);
	}



	/**
	 * 根据条件获取未读数据集啊
	 * @param $ep_id
	 * @param $re_array
	 * @param $page_start
	 * @param $limit
	 * @param $orderby
	 * @return mixed
	 */
	public function get_real_list($ep_id, $re_array, $page_start, $limit, $orderby) {

		return $this->_d->get_real_list($ep_id, $re_array, $page_start, $limit, $orderby);
	}

	/**
	 * 根据条件获取已读数据集
	 * @param $ep_id
	 * @param $re_array
	 * @param $page_start
	 * @param $limit
	 * @return mixed
	 */
	public function get_old_list($ep_id, $re_array, $page_start, $limit) {

		return $this->_d->get_old_list($ep_id, $re_array, $page_start, $limit);
	}



}