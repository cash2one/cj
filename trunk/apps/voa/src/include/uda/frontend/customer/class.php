<?php
/**
 * voa_uda_frontend_customer_class
 * 统一数据访问/客户应用/分类操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_customer_class extends voa_uda_frontend_customer_abstract {

	/**
	 * 构造方法
	 * @param array $ptname 插件和表格名称
	 * + string plugin 插件名称
	 * + string table 表格名称
	 * + string classes 分类信息
	 */
	public function __construct($ptname) {

		parent::__construct($ptname);
		$this->_classes = $ptname['classes'];
	}

	/**
	 * 获取分类列表
	 * @param array &$list 分类列表
	 * @return boolean
	 */
	public function list_all($gp, &$list, &$total) {

		// 查询表格的条件
		$fields = array(
			array('classid', self::VAR_INT, null, null, true),
			array('pid', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields, $gp)) {
			return false;
		}

		$conds['tid'] = $this->_table['tid'];
		// 获取分类
		$t = new voa_d_oa_customer_class();
		$list = $t->list_by_conds($conds);

		$t->reset();
		$total = $t->count_by_conds($conds);

		return true;
	}

	/**
	 * 根据 classid 获取分类信息
	 * @param int $classid 分类id
	 * @param array $class 分类信息
	 * @return boolean
	 */
	public function get_one($classid, &$class) {

		$t = new voa_d_oa_customer_class();
		$class = $t->get($classid);

		return true;
	}

	/**
	 * 更新当个分类信息
	 * @param array $gp 数据
	 * @param int $classid 分类id
	 * @throws service_exception
	 * @return boolean
	 */
	public function update($gp, $classid) {

		// 提取数据
		$data = array();
		if (!$this->__parse_gp($gp, $data)) {
			return false;
		}

		$classid = (int)$classid;
		$t = new voa_d_oa_customer_class();

		try {
			$t->update($classid, $data);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 新增分类
	 * @param array $gp 数据
	 * @throws service_exception
	 * @return boolean
	 */
	public function add($gp, &$class) {

		// 提取数据
		$class['tid'] = $this->_table['tid'];
		if (!$this->__parse_gp($gp, $class)) {
			return false;
		}

		$t = new voa_d_oa_customer_class();
		// 根据类名读取分类
		$so_conds = array(
			'tid' => $this->_table['tid'],
			'classname' => $class['classname']
		);
		// 如果分类名称已存在
		if ($t->get_by_conds($so_conds)) {
			$this->set_errmsg(voa_errcode_oa_customer::CLASSNAME_DUPLICATE);
			return false;
		}

		try {
			$t->reset();
			$class = $t->insert($class);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 删除分类信息
	 * @param int $classid 分类ID
	 * @throws service_exception
	 * @return boolean
	 */
	public function delete($classid) {

		// 获取搜索条件
		$conds = array(
			'classid' => $classid,
			'tid' => $this->_table['tid']
		);

		// 初始化数据表操作类
		$t = new voa_d_oa_customer_class();
		// 如果存在子分类, 则不让删除
		if ($t->get_by_conds(array('tid=?' => $this->_table['tid'], 'pid=?' => $classid))) {
			$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_CLASS_HAS_CHILD);
			return false;
		}

		try {
			$t->reset();
			$t->delete_by_conds($conds);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 从 G/P 中提取数据
	 * @param array $gp 请求数据
	 * @param array $table 数据结果
	 * @return boolean
	 */
	private function __parse_gp($gp, &$data) {

		$fields = array(
			array('classname', self::VAR_STR, 'chk_classname', voa_errcode_oa_customer::CUSTOMER_CLASSNAME_IS_EMPTY),
			array('pid', self::VAR_INT, 'chk_classid', voa_errcode_oa_customer::CUSTOMER_CLASSID_ERR)
		);
		// 提取数据
		if (!$this->extract_field($data, $fields, $gp)) {
			return false;
		}

		return true;
	}

	/**
	 * 检查分类名称
	 * @param string $name 分类名称
	 * @param string $err 错误信息
	 * @return boolean
	 */
	public function chk_classname($name, $err = null) {

		// 如果名称为空
		if (empty($name)) {
			$this->set_errmsg($err);
			return false;
		}

		return true;
	}

	/**
	 * 检查分类id
	 * @param int $id 分类id
	 * @param string $err 错误信息
	 * @return boolean
	 */
	public function chk_classid($id, $err = null) {

		// 如果 $id 大于 0
		if (0 < $id) {
			// 如果 $id 不在当前分类中
			if (!array_key_exists($id, $this->_classes)) {
				$this->set_errmsg($err);
				return false;
			}
		}

		return true;
	}
}

