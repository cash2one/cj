<?php
/**
 * SalesCustomerController.class.php
 * $author$
 */
namespace Sales\Controller\Apicp;

class SalesCustomerController extends AbstractController {

	/**
	 * 新增客户
	 * @param String $sc_name 公司全称
	 * @param String $sc_short_name 公司简称
	 * @param int $sc_source 客户来源
	 * @param String $sc_contacter 联系人
	 * @param String $sc_phone 联系方式
	 * @param String $sc_address 地址
	 * @param String $sc_m_uid 跟进人
	 * @return int $sc_id 客户ID
	 * $author zhubeihai
	 */
	public function Add_customer_post() {

		// 客户信息
		$customer = array ();
		// 用户提交的参数
		$params = I('request.');
		// 非用户提交的扩展参数
		$extend = array (
			'uid'      => $this->_login->user['m_uid'],
			'username' => $this->_login->user['m_username']
		);

		// 如果新增操作失败
		$serv_customer = D('Sales/SalesCustomer', 'Service');
		$serv_customer->add_customer($customer, $params, $extend);
		if (!$serv_customer->add_customer($customer, $params, $extend)) {
			E($serv_customer->get_errcode().':'.$serv_customer->get_errmsg());
			return false;
		}

		// 格式化
		$serv_fmt = D('Sales/Format', 'Service');
		$serv_fmt->customer_format($customer);
		$this->_result = $customer;
		return true;
	}

	/**
	 * 编辑客户
	 * @param int $sc_id 客户ID
	 * @param String $sc_name 公司全称
	 * @param String $sc_short_name 公司简称
	 * @param int $sc_source 客户来源
	 * @param String $sc_contacter 联系人
	 * @param String $sc_phone 联系方式
	 * @param String $sc_address 地址
	 * @param String $sc_m_uid 跟进人
	 * $author zhubeihai
	 */
	public function Edit_customer_post() {

		// 编辑客户信息
		$customer = array ();
		// 用户提交的参数
		$params = I('request.');
		// 非用户提交的扩展参数
		$extend = array (
			'uid'      => $this->_login->user['m_uid'],
			'username' => $this->_login->user['m_username']
		);

		// 如果新增操作失败
		$serv_customer = D('Sales/SalesCustomer', 'Service');
		if (!$serv_customer->edit_customer($customer, $params, $extend)) {
			E($serv_customer->get_errcode().':'.$serv_customer->get_errmsg());
			return false;
		}

		// 格式化
		$serv_fmt = D('Sales/Format', 'Service');
		$serv_fmt->customer_format($customer);
		$this->_result = $customer;
		return true;
	}

	/**
	 * 删除客户
	 * @return bool
	 * $author: husendong@vchangyi.com
	 */
	public function Delete_customer_get() {

		// 获取sc_id
		$sc_ids = (array)I('get.sc_ids');
		// 参数无效
		$serv_sc = D('Sales/SalesCustomer', 'Service');

		// 编辑操作失败
		if (!$serv_sc->Delete_customer($sc_ids)) {
			E($serv_sc->get_errcode().':'.$serv_sc->get_errmsg());
			return false;
		}

		return true;
	}

	/**
	 * 客户列表查询
	 * $Author songshuangfeng
	 */
	public function  List_customer_get() {

		// 每页条数
		$limit = (int)I('get.limit');
		$page = I('get.page');

		// 判断每页条数是否正确 ,如果不合法赋予系统默认值
		if ($limit < cfg('perpage_min') || $limit > cfg('perpage_max')) {
			$limit = $this->_plugin->setting['perpage'];
		}
		list($start, $limit, $page) = page_limit($page, $limit);
		// 分页参数
		$page_option = array(
			$start,
			$limit
		);

		// 用户提交的参数
		$params = I('request.');
		$serv_sc = D('Sales/SalesCustomer', 'Service');
		$counstomerlist = array();

		// 获取客户列表
		if (!$serv_sc->list_customer($counstomerlist, $params, $page_option)) {
			E($serv_sc->get_errcode() . ':' . $serv_sc->get_errmsg());
			return false;
		}

		// 列表总数
		$count = $serv_sc->count_by_condition($params);
		$this->_result = array(
			"total" => $count,
			"limit" => $limit,
			"data" => $counstomerlist
		);

		return true;
	}

	/**
	 * 客户详情
	 * $author songshuangfeng
	 * @return bool
	 */
	public function  Customer_detail_get() {

		// 客户id
		$sc_id = I('get.sc_id');
		$serv_sc = D('Sales/SalesCustomer', 'Service');
		$customerinfo = array ();

		// 获取客户详情
		if (!$serv_sc->customer_detail($customerinfo, $sc_id)) {
			E($serv_sc->get_errcode().':'.$serv_sc->get_errmsg());
			return false;
		}

		$this->_result = $customerinfo;
		return true;
	}
}
