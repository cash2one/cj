<?php
/**
 * SalesBusinessService.class.php
 * $author$
 */

namespace Sales\Service;

class SalesBusinessService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Sales/SalesBusiness");
	}

	/**
	 * 新增商机
	 * @param array $business 商机详情
	 * @param array $params 传入参数
	 * @param array $extend 扩展参数
	 * @return bool 是否添加成功 true:添加成功 false：添加失败
	 */
	public function create_business(&$business, $params, $extend = array()) {

		// 获取入库参数
		// 当前用户ID
		$uid = (int)$extend['uid'];
		// 当前用户名称
		$username = (string)$extend['username'];
		// 客户ID
		$sc_id = (int)$params['sc_id'];
		// 商机名称
		$sb_name = (string)$params['sb_name'];
		// 客户来源
		$sb_source = (int)$params['sb_source'];
		// 客户进展（当前客户状态）
		$sb_type = (int)$params['sb_type'];
		// 预计金额
		$sb_amount = (float)$params['sb_amount'];
		// 备注
		$sb_comments = (String)$params['sb_comments'];

		// 商机名称不能为空
		if (empty($sb_name)) {
			$this->_set_error('_ERR_BUSINESS_NAME_MESSAGE');
			return false;
		}

		// 客户不能为空
		if (empty($sc_id)) {
			$this->_set_error('_ERR_CUSTOMER_MESSAGE');
			return false;
		}

		// 当前客户状态不能为空
		if (empty($sb_type)) {
			$this->_set_error('_ERR_CUSTOMER_TYPE_MESSAGE');
			return false;
		}

		// 预计销售金额
		if (empty($sb_amount)) {
			$this->_set_error('_ERR_BUSINESS_AMOUNT_MESSAGE');
			return false;
		}

		// 预计销售金额不能小于0
		if ($sb_amount <= 0) {
			$this->_set_error('_ERR_BUSINESS_AMOUNT_MESSAGE');
			return false;
		}

		// 填充参数
		$business = array(
			'sb_name' => $sb_name,
			'sc_id' => $sc_id,
			'm_uid' => $uid,
			'm_username' => $username,
			'sb_amount' => $sb_amount,
			'sb_type' => $sb_type,
			'sb_source' => $sb_source,
			'sb_comments' => $sb_comments,
			'sb_status' => $this->_d->get_st_create(),
			'sb_created' => NOW_TIME,
			'sc_created_year'=>date('Y')
		);

		// 执行入库操作
		if (!$id = $this->_d->insert($business)) {
			$this->_set_error('_ERR_BUSINESS_INSERT_ERROR');
			return false;
		}

		$business['sb_id'] = $id;
		return true;
	}

	/**
	 * 编辑商机
	 * @param array $params 传入参数
	 * @param array $extend 扩展参数
	 * @return bool 是否编辑成功 true编辑成功 false编辑失败
	 */
	public function edit_business($params, $extend) {

		// 获取入库参数
		// 商机ID
		$sb_id = (int)$params['sb_id'];
		// 商机名称
		$sb_name = (string)$params['sb_name'];
		// 客户进展（当前客户状态）
		$sb_type = (int)$params['sb_type'];
		// 预计金额
		$sb_amount = (float)$params['sb_amount'];
		// 备注
		$sb_comments = (String)$params['sb_comments'];
		// 当前用户ID
		$uid = (int)$extend["m_uid"];

		//判断商机id不能为空
		if (empty($sb_id)) {
			$this->_set_error('_ERR_BUSINESS_ID_MESSAGE');
			return false;
		}

		// 判断商机的负责人和当前操作人是否匹配
		$olden = $this->get($sb_id);
		if ($olden["m_uid"] != $uid) {
			$this->_set_error('_ERR_BUSINESS_PRINCIPAL_ISNOTMATCH');
			return false;
		}

		// 商机名称不能为空
		if (empty($sb_name)) {
			$this->_set_error('_ERR_BUSINESS_NAME_MESSAGE');
			return false;
		}

		// 当前客户状态不能为空
		if (empty($sb_type)) {
			$this->_set_error('_ERR_CUSTOMER_TYPE_MESSAGE');
			return false;
		}

		// 预计销售金额
		if (empty($sb_amount)) {
			$this->_set_error('_ERR_BUSINESS_AMOUNT_MESSAGE');
			return false;
		}

		// 预计销售金额不能小于0
		if ($sb_amount <= 0) {
			$this->_set_error('_ERR_BUSINESS_AMOUNT_MESSAGE');
			return false;
		}

		// 执行更新 如果执行出错
		if (!$this->_d->edit_business($sb_id, $sb_name, $sb_amount, $sb_type, $sb_comments)) {
			$this->_set_error('_ERR_EDIT_BUSINESS_MESSAGE');
			return false;
		}

		return true;
	}

	/**
	 * 删除商机
	 * @param array $sb_ids 商机IDS
	 * @return bool 是否删除成功 true 成功 false 失败
	 */
	public function del_business($sb_ids) {

		// 要删除的商机
		$sb_ids = (array)$sb_ids;

		// 剔除空值，如果为空就直接返回true
		$sb_ids = array_filter($sb_ids);

		//判断商机id不能为空
		if (empty($sb_ids)) {
			$this->_set_error('_ERR_BUSINESS_ID_MESSAGE');
			return false;
		}

		// 获得存在的商机的ID
		$lst_business = $this->list_by_pks($sb_ids);
		$lst_ids = array();
		foreach ($lst_business as $m => $v) {
			$lst_ids[] = $v['sb_id'];
		}

		// 删除商机
		if (!$this->_d->delete_business($lst_ids)) {
			$this->_set_error('_ERR_DEL_BUSINESS_MESSAGE');
			return false;
		}

		return true;
	}

	/**
	 * 商机列表查询
	 * @param array $list_business 商机列表
	 * @param array $params 传入参数
	 * @param array $page_option 分页参数
	 * @param array $order_by 排序参数
	 * @return bool 是否查询成功 true 查询成功 false 查询失败
	 * $author chen
	 */
	public function list_business(&$list_business, $params, $page_option, $order_by) {

		// 排序条件
		$order_option = array('A.sb_created' => 'DESC');
		if (!empty($order_by)) {
			$order_by = (int)$order_by;
			// 签单可能性
			if ($order_by == $this->_d->get_type_sign()) {
				$order_option = array('A.sb_type' => 'Desc');
			}
			// 预计销售金额
			if ($order_by == $this->_d->get_type_amount()) {
				$order_option = array('A.sb_amount' => 'Desc');
			}
			// 跟进日期
			if ($order_by == $this->_d->get_type_date) {
				$order_option = array('A.sb_updated' => 'Desc');
			}
		}

		// 获得商机查询结果
		$list_business = $this->_d->list_business($params, $page_option, $order_option);
		return true;
	}


	/**
	 * 商机详情
	 * @param int $sb_id 商机id
	 * @return business_inf:商机信息  manager_inf:负责人信息
	 * $author zhubeihai
	 */
	public function business_detail(&$business_detail, $sb_id) {

		//判断商机id不能为空
		if (empty($sb_id)) {
			$this->_set_error('_ERR_BUSINESS_ID_MESSAGE');
			return false;
		}

		$manager_inf = array();

		// 执行入库操作
		if (!$business_detail = $this->_d->business_detail($sb_id)) {
			$this->_set_error('_ERR_INSERT_ERROR');
			return false;
		}


		// 执行入库操作
		if (!$manager_inf = $this->get_member_department($business_detail['m_uid'])) {
			$this->_set_error('_ERR_INSERT_ERROR');
			return false;
		}

		$business_detail['m_username'] = $manager_inf['m_username'];
		$business_detail['mp_name'] = $manager_inf['mp_name'];
		return true;
	}

	/**
	 * 变更商机负责人
	 * @param array $params 页面提交参数
	 * @param array $extend 扩展参数
	 * @return bool ture成功，false失败
	 */
	public function change_manager($params, $extend) {

		// 商机ID
		$sb_id = (int)$params['sb_id'];
		// 新的负责人ID
		$m_uid = (int)$params['m_uid'];
		// 当前用户的uid
		$uid = (int)$extend["uid"];

		// 判断商机是否为空
		if (empty($sb_id)) {
			$this->_set_error('_ERR_BUSINESS_ID_MESSAGE');
			return false;
		}

		// 判断商机负责人是否为空
		if (empty($m_uid)) {
			$this->_set_error('_ERR_BUSINESS_PRINCIPAL_EMPTY');
			return false;
		}

		// 判断商机的负责人和当前操作人是否匹配
		$olden = $this->get($sb_id);
		if ($olden["m_uid"] != $uid) {
			$this->_set_error('_ERR_BUSINESS_PRINCIPAL_ISNOTMATCH');
			return false;
		}

		// 根据用户uid获取用户实体
		$member = $this->get_user($m_uid);

		// 判断商机负责人是否存在
		if (empty($member)) {
			$this->_set_error('_ERR_BUSINESS_PRINCIPAL_ISEXIST');
			return false;
		}

		return $this->_d->change_manager($sb_id, $m_uid, $member["m_username"]);
	}

	/**
	 * 数据管理
	 * @param array $data_manage 数据集合
	 * @param array $params 参数
	 */
	public function  data_management(&$data_manage, $params) {

		$data_manage = $this->_d->data_management($params);

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
