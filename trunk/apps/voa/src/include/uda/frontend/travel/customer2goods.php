<?php
/**
 * voa_uda_frontend_travel_customer2goods
 * 统一数据访问/客户应用/客户所对应的商品信息
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_customer2goods extends voa_uda_frontend_travel_abstract {
	// 关注数字段
	protected $_attentions = 'proto_1';
	// 特殊条件参数
	protected $_special_conds = array();

	public function __construct($ptname = null) {

		parent::__construct($ptname);
		if (is_array($ptname)) {
			isset($ptname['classes']) && $this->_classes = $ptname['classes'];
			isset($ptname['columns']) && $this->_tablecols = $ptname['columns'];
			isset($ptname['options']) && $this->_tablecolopts = $ptname['options'];
			// 特殊条件参数
			if (!empty($ptname['conds'])) {
				$this->_special_conds = $ptname['conds'];
			}
		}
	}

	/**
	 * 获取客户关注的商品列表
	 * @param array &$list 表格列选项
	 * @return boolean
	 */
	public function list_all($gp, $page_option, &$list) {

		// 查询表格的条件
		$fields = array(
			array('cgid', self::VAR_INT, null, null, true),
			array('customer_id', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields, $gp)) {
			return false;
		}

		$conds = array_merge($conds, $this->_special_conds);
		// 读取客户和产品对应关系
		$t = new voa_d_oa_travel_customer_goods();
		if (!$list = $t->list_by_conds($conds)) {
			return true;
		}

		$goods_ids = array();
		foreach ($list as $_v) {
			$goods_ids[] = $_v['goods_id'];
		}

		// 读取产品信息
		$t = new voa_d_oa_goods_data();
		$goods = $t->list_by_pks($goods_ids);

		// 构造返回数据
		foreach ($list as &$_v) {
			$this->_merge_goods($_v, $goods[$_v['goods_id']]);
		}

		return true;
	}

	/**
	 * 获取客户关注的商品列表
	 * @param array &$list 表格列选项
	 * @return boolean
	 */
	public function list_customer_by_goods_id($goods_id, $page_option, &$list) {

		// 判断 goods_id 是否有效
		if (empty($goods_id)) {
			$this->_set_errcode(voa_errcode_oa_travel::GOODS_ID_IS_EMPTY);
			return false;
		}

		// 查询表格的条件
		$conds = array('goods_id' => $goods_id);
		$conds = array_merge($conds, $this->_special_conds);
		// 读取客户和产品对应关系
		$t = new voa_d_oa_travel_customer_goods();
		if (!$list = $t->list_by_conds($conds)) {
			return true;
		}

		return true;
	}

	/**
	 * 根据 cgid 获取客户关注的指定商品
	 * @param int $cgid 产品id
	 * @param array &$data 商品信息
	 * @return boolean
	 */
	public function get_one($cgid, &$data) {

		// 读取数据
		$t = new voa_d_oa_travel_customer_goods();
		// 如果数据不存在
		if (!$data = $t->get($cgid)) {
			$this->set_errmsg(voa_errcode_oa_travel::CUSTOMER_GOODS_IS_NOT_EXIST);
			return false;
		}

		// 读取产品信息
		$t = new voa_d_oa_goods_data();
		$goods = $t->get($data['goods_id']);

		$this->_merge_goods($data, $goods);

		return true;
	}

	/**
	 * 新增客户商品
	 * @param array $gp 数据
	 * @param array $goods 客户和商品关联数据
	 * @throws service_exception
	 * @return boolean
	 */
	public function add($member, $gp, &$goods) {

		$this->_mem = $member;
		$goods['uid'] = $member['m_uid'];
		// 提取数据
		if (!$this->__parse_gp($gp, $goods)) {
			return false;
		}

		// customer_id
		$customer_id = $gp['customer_id'];
		if (!is_array($customer_id)) {
			$customer_id = explode(',', (string)$customer_id);
		}

		$t = new voa_d_oa_travel_customer_goods();
		$uda_goods = &uda::factory('voa_uda_frontend_goods_data', $this->_ptname);

		// 取产品的客户关注列表
		$all_customer = $t->list_by_conds(array('goods_id' => $goods['goods_id'], 'customer_id' => $customer_id));
		// 如果已有客户
		$exist_ids = array();
		if (!empty($all_customer)) {
			// 遍历关注信息, 取出 customer_id
			foreach ($all_customer as $_v) {
				$exist_ids[] = $_v['customer_id'];
			}
		}

		try {
			// 遍历客户, 逐个添加对应关系
			$new_ct = 0;
			foreach ($customer_id as $_id) {
				// 如果已存在
				if (in_array($_id, $exist_ids)) {
					continue;
				}

				$new_ct ++;
				$goods['customer_id'] = $_id;
				$t->insert($goods);
			}

			// 更新该产品的关注数
			0 < $new_ct && $uda_goods->incr($this->_attentions, $goods['goods_id'], null, $new_ct);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 删除客户关注的产品信息
	 * @param mixed $cgid 客户和产品的关联id
	 * @throws service_exception
	 * @return boolean
	 */
	public function delete($cgid) {

		// 初始化数据表操作类
		$t = new voa_d_oa_travel_customer_goods();

		try {
			$t->delete($cgid);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 从 G/P 中提取数据
	 * @param array $gp 请求数据
	 * @param array $data 数据结果
	 * @return boolean
	 */
	private function __parse_gp($gp, &$data) {

		$fields = array(
			array('goods_id', self::VAR_INT, null, null)
		);
		// 提取数据
		if (!$this->extract_field($data, $fields, $gp)) {
			return false;
		}

		return true;
	}

	/**
	 * 合并商品信息
	 * @param array $cg 管理数组信息
	 * @param array $goods 商品
	 * @return boolean
	 */
	protected function _merge_goods(&$cg, $goods) {

		$cg['goods_subject'] = empty($goods) ? '' : $goods['subject'];
		return true;
	}

}
