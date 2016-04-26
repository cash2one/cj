<?php
/**
 * SalesRecordService.class.php
 * $author$ zhubeihai
 */

namespace Sales\Service;

class SalesRecordService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Sales/SalesRecord");
	}


	/**
	 * 商机状态变更记录列表查询
	 * @param array $list_record 商机状态变更列表
	 * @param array $params 传入参数
	 * @param array $page_option 分页参数
	 * @param array $order_by 排序参数
	 * @return bool 是否查询成功 true 查询成功 false 查询失败
	 * $author zhubeihai
	 */
	public function list_business_modify_record(&$list_record, $params, $page_option, $order_by) {

		//判断商机id不能为空
		if (empty($params['sb_id'])) {
			$this->_set_error('_ERR_BUSINESS_ID_MESSAGE');
			return false;
		}

		// 排序条件
		$order_option = array('sr_created' => 'DESC');
		if (!empty($order_by)) {
			$order_by = (int)$order_by;
		}

		// 获得商机状态变更记录
		$list_record = $this->_d->list_business_modify_record($params, $page_option, $order_option);
		return true;
	}

	/**
	 * 获得查询数量
	 * @param array $params 传入参数
	 * @return mixed 获得查询数量
	 */
	public function count_by_condition($params) {

		// 获得商机查询结果
		return $this->_d->count_by_condition($params);
	}
}
