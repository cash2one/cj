<?php
/**
 * voa_uda_frontend_customer_table
 * 统一数据访问/客户应用/表格操作
 * $Author$
 * $Id$
 */

class voa_uda_frontend_customer_table extends voa_uda_frontend_customer_abstract {

	/**
	 * 构造方法
	 * @param array $ptname 插件和表格名称
	 * + string plugin 插件名称
	 * + string table 表格名称
	 */
	public function __construct($ptname) {

		parent::__construct($ptname);
	}

	/**
	 * 获取表格列表
	 * @param array &$list 表格列表
	 * @return boolean
	 */
	public function list_all($gp, &$list) {

		// 查询表格的条件
		$fields = array(
			array('tid', self::VAR_INT, null, null, true),
			array('cp_identifier', self::VAR_STR, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields, $gp)) {
			return false;
		}

		$t = new voa_d_oa_customer_table();
		$list = $t->list_by_conds($conds);

		return true;
	}

	/**
	 * 根据 tid 获取表格信息
	 * @param int $tid 表格id
	 * @param array &$table 表格信息
	 * @return boolean
	 */
	public function get_one($tid, &$table) {

		$t = new voa_d_oa_customer_table();
		$table = $t->get($tid);

		return true;
	}

	/**
	 * 更新当个表格信息
	 * @param array $gp 数据
	 * @param int $tid 表格id
	 * @throws service_exception
	 * @return boolean
	 */
	public function update($member, $gp, $tid) {

		$tid = (int)$tid;
		if (!$this->chk_tid($tid)) {
			return false;
		}

		// 提取数据
		$data = array('uid' => $member['m_uid']);
		if (!$this->__parse_gp($gp, $data)) {
			return false;
		}

		$t = new voa_d_oa_customer_table();

		try {
			$t->update($tid, $data);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		// 缓存更新
		voa_h_cache::get_instance()->get('customertable', 'oa', true);

		return true;
	}

	/**
	 * 新增表格
	 * @param array $gp 数据
	 * @throws service_exception
	 * @return boolean
	 */
	public function add($member, $gp, &$table) {

		// 提取数据
		$table = array('uid' => $member['m_uid']);
		if (!$this->__parse_gp($gp, $table)) {
			return false;
		}

		// 判断唯一标识是否存在
		if (array_key_exists($table['tunique'], $this->_tables)) {
			$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_TUNIQUE_DUPLICATE);
			return false;
		}

		$t = new voa_d_oa_customer_table();

		try {
			$table = $t->insert($table);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		// 缓存更新
		voa_h_cache::get_instance()->get('customertable', 'oa', true);

		return true;
	}

	/**
	 * 删除表格信息
	 * @param int $tid 数据表id
	 * @throws service_exception
	 * @return boolean
	 */
	public function delete($tid) {

		$t = new voa_d_oa_customer_table();

		try {
			$t->delete($tid);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		// 更新缓存
		voa_h_cache::get_instance()->get('customertable', 'oa', true);

		return true;
	}

	/**
	 * 从 G/P 中提取数据
	 * @param array $gp 请求数据
	 * @param array $table 数据结果
	 * @return boolean
	 */
	private function __parse_gp($gp, &$table) {

		$fields = array(
			array('cp_identifier', self::VAR_STR, 'chk_cp_identifier', null),
			array('tunique', self::VAR_STR, 'chk_tunique', null),
			array('tname', self::VAR_STR, 'chk_tname', voa_errcode_oa_customer::CUSTOMER_TABLENAME_IS_EMPTY),
			array('t_desc', self::VAR_STR, null, null)
		);
		// 提取数据
		if (!$this->extract_field($table, $fields, $gp)) {
			return false;
		}

		return true;
	}

	/**
	 * 检查插件的唯一标识
	 * @param string $identifier 插件的唯一标识
	 * @param string $err 错误提示
	 * @return boolean
	 */
	public function chk_cp_identifier($identifier, $err = null) {

		return true;
		// 如果插件唯一标识为空
		if (empty($identifier)) {
			$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_CP_IDENTIFIER_IS_EMPTY);
			return false;
		}

		// 判断插件为标识是否存在
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		$is_exists = false;
		foreach ($plugins as $_p) {
			if ($identifier == $_p['cp_identifier']) {
				$is_exists = true;
				break;
			}
		}

		// 如果该插件标识不存在
		if (false == $is_exists) {
			$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_CP_IDENTIFIER_IS_NOT_EXIST);
			return false;
		}

		return true;
	}

	/**
	 * 检查表格唯一标识
	 * @param string $unique 表格唯一标识
	 * @param string $err
	 * @return boolean
	 */
	public function chk_tunique($unique, $err = null) {

		// 如果表格唯一标识为空
		if (empty($unique)) {
			$this->set_errmsg(voa_errcode_oa_customer::CUSTOMER_TUNIQUE_IS_EMPTY);
			return false;
		}

		return true;
	}

	/**
	 * 检查表格名称
	 * @param string $name 表格名称
	 * @param string $err 错误信息
	 * @return boolean
	 */
	public function chk_tname($name, $err = null) {

		// 如果表名为空
		if (empty($name)) {
			$this->set_errmsg($err);
			return false;
		}

		return true;
	}
}

