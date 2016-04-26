<?php
/**
 * SalesTypeService.class.php
 * $author$
 */

namespace Sales\Service;

use Com\Validator;

class SalesCustomerService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Sales/SalesCustomer");
	}


	/**
	 * 新增客户
	 *
	 * @param array &$customer 客户信息
	 * @param array $params 传入的参数
	 * @param mixed $extend 扩展参数
	 *
	 * $author$ zhubeihai
	 */
	public function add_customer(&$customer, $params, $extend = array()) {

		// 获取入库参数
		$uid = (string)$extend['uid'];
		$username = (string)$extend['username'];

		// 公司全称
		$sc_name = (string)$params['sc_name'];
		// 公司简称
		$sc_short_name = (string)$params['sc_short_name'];
		// 客户来源
		$sc_source = (int)$params['sc_source'];
		// 联系人
		$sc_contacter = (string)$params['sc_contacter'];
		// 联系方式
		$sc_phone = (string)$params['sc_phone'];
		// 地址
		$sc_address = (string)$params['sc_address'];
		// 跟进人
		$sc_m_uid = (string)$params['sc_m_uid'];

		// 用户信息不能为空
		if (empty($uid) || empty($username)) {
			$this->_set_error('_ERR_UID_USERNAME_MESSAGE');
			return false;
		}

		// 公司全称不能为空
		if (empty($sc_name)) {
			$this->_set_error('_ERR_CUSTOMER_COMPANY_NAME_MESSAGE');
			return false;
		}

		// 公司简称不能为空
		if (empty($sc_short_name)) {
			$this->_set_error('_ERR_CUSTOMER_COMPANY_SHORT_NAME_MESSAGE');
			return false;
		}

		// 客户来源不能为空
		if (empty($sc_source)) {
			$this->_set_error('_ERR_CUSTOMER_COMPANY_SOURCE_MESSAGE');
			return false;
		}

		// 联系人不能为空
		if (empty($sc_contacter)) {
			$this->_set_error('_ERR_CUSTOMER_CONTACTER_MESSAGE');
			return false;
		}

		// 联系方式不能为空
		if (empty($sc_phone)) {
			$this->_set_error('_ERR_CUSTOMER_PHONE_MESSAGE');
			return false;
		}

		// 地址不能为空
		if (empty($sc_address)) {
			$this->_set_error('_ERR_CUSTOMER_ADDRESS_MESSAGE');
			return false;
		}

		// 跟进人不能为空
		if (empty($sc_m_uid)) {
			$this->_set_error('_ERR_CUSTOMER_PARTNER_MESSAGE');
			return false;
		}

		// 客户信息
		$customerInfo = array(
			'sc_name' => $sc_name,
			'sc_short_name' => $sc_short_name,
			'sc_source' => $sc_source,
			'sc_contacter' => $sc_contacter,
			'sc_phone' => $sc_phone,
			'sc_address' => $sc_address,
			'sc_m_uid' => $sc_m_uid,
			'cg_created' => NOW_TIME
		);

		// 执行入库操作
		if (!$sc_id = $this->_d->insert($customerInfo)) {
			$this->_set_error('_ERR_INSERT_ERROR');
			return false;
		}

		$customer['sc_id'] = $sc_id;
		return true;
	}


	/**
	 * 编辑客户
	 *
	 * @param array &$customer 客户信息
	 * @param array $params 传入的参数
	 * @param mixed $extend 扩展参数
	 *
	 * $author$ zhubeihai
	 */
	public function edit_customer(&$customer, $params, $extend = array()) {

		// 获取入库参数
		$uid = (string)$extend['uid'];
		$username = (string)$extend['username'];

		// 客户id
		$sc_id = (string)$params['sc_id'];
		// 公司全称
		$sc_name = (string)$params['sc_name'];
		// 公司简称
		$sc_short_name = (string)$params['sc_short_name'];
		// 客户来源
		$sc_source = (int)$params['sc_source'];
		// 联系人
		$sc_contacter = (string)$params['sc_contacter'];
		// 联系方式
		$sc_phone = (string)$params['sc_phone'];
		// 地址
		$sc_address = (string)$params['sc_address'];
		// 跟进人
		$sc_m_uid = (string)$params['sc_m_uid'];

		// 用户信息不能为空
		if (empty($uid) || empty($username)) {
			$this->_set_error('_ERR_UID_USERNAME_MESSAGE');
			return false;
		}

		// 客户id不能为空
		if (empty($sc_id)) {
			$this->_set_error('_ERR_CUSTOMER_SCIDS_MESSAGE');
			return false;
		}

		// 公司全称不能为空
		if (empty($sc_name)) {
			$this->_set_error('_ERR_CUSTOMER_COMPANY_NAME_MESSAGE');
			return false;
		}

		// 公司简称不能为空
		if (empty($sc_short_name)) {
			$this->_set_error('_ERR_CUSTOMER_COMPANY_SHORT_NAME_MESSAGE');
			return false;
		}

		// 客户来源不能为空
		if (empty($sc_source)) {
			$this->_set_error('_ERR_CUSTOMER_COMPANY_SOURCE_MESSAGE');
			return false;
		}

		// 联系人不能为空
		if (empty($sc_contacter)) {
			$this->_set_error('_ERR_CUSTOMER_CONTACTER_MESSAGE');
			return false;
		}

		// 联系方式不能为空
		if (empty($sc_phone)) {
			$this->_set_error('_ERR_CUSTOMER_PHONE_MESSAGE');
			return false;
		}

		// 地址不能为空
		if (empty($sc_address)) {
			$this->_set_error('_ERR_CUSTOMER_ADDRESS_MESSAGE');
			return false;
		}

		// 跟进人不能为空
		if (empty($sc_m_uid)) {
			$this->_set_error('_ERR_CUSTOMER_PARTNER_MESSAGE');
			return false;
		}

		// 客户信息
		$customerInfo = array(
			'sc_name' => $sc_name,
			'sc_short_name' => $sc_short_name,
			'sc_source' => $sc_source,
			'sc_contacter' => $sc_contacter,
			'sc_phone' => $sc_phone,
			'sc_address' => $sc_address,
			'sc_m_uid' => $sc_m_uid
		);

		// 执行入库操作
		if (!$sc_id = $this->_d->edit_customer($sc_id, $customerInfo)) {
			$this->_set_error('_ERR_INSERT_ERROR');
			return false;
		}

		return true;
	}


	/**
	 * 客户列表查询
	 * @param array $params 查询条件
	 * @param array $page_option 分页参数
	 * return array 客户列表
	 */
	public function list_customer(&$counstomerlist, $params, $page_option) {

		// 客户来源
		$sc_source = (int)$params['sc_source'];
		// 客户状态
		$sc_type = (int)$params['sc_type'];

		// 客户来源不能为空
		if (empty($sc_source)) {
			$this->_set_error('_ERR_CUSTOMER_SOURCE_MESSAGE');
			return false;
		}

		// 客户状态不能为空
		if (empty($sc_type)) {
			$this->_set_error('_ERR_CUSTOMER_TYPE_MESSAGE');
			return false;
		}

		$counstomerlist = $this->_d->list_customer($params, $page_option);
		return true;
	}

	/**
	 * 根据条件获取客户列表总数
	 * @param array $params 查询条件
	 * retutn int $count 总数
	 */
	public function count_by_condition($params) {

		return $this->_d->count_by_condition($params);
	}

	/**
	 * 删除客户
	 * @param int $sc_id 待删除客户id
	 * @return bool
	 * $author: husendong@vchangyi.com
	 */
	public function delete_customer($sc_ids) {

		$sc_ids = (array)$sc_ids;
		// 剔除空值，如果为空就直接返回true
		$sc_ids = array_filter($sc_ids);

		if (empty($sc_ids)) {
			return true;
		}

		// 通过id获得存在的客户
		$lst_customer = $this->list_by_pks($sc_ids);
		$lst_ids = array();

        // 获得存在的客户的ID
		foreach ($lst_customer as $m => $v) {
			$lst_ids[] = $v['sc_id'];
		}

		// 删除客户
		if (!$this->_d->delete_customer($lst_ids)) {
			$this->_set_error('_ERR_CUSTOMER_DELETE_MESSAGE');
			return false;
		}

		return true;
	}

	/**
	 * 客户详情
	 * @param array $customerinfo 客户信息
	 * @param int $sc_id 客户id
	 * @return bool
	 */
	public function  customer_detail(&$customerinfo, $sc_id) {

		//判断客户id是否为空
		if (empty($sc_id)) {
			$this->_set_error('_ERR_CUSTOMER_SCIDS_MESSAGE');
			return false;
		}

		//获取客户信息
		$customerinfo = $this->get($sc_id);
		if (empty($customerinfo)) {
			$this->_set_error('_ERR_CUSTOMER_ISEXIST_MESSAGE');
			return false;
		}

		return true;
	}
}
