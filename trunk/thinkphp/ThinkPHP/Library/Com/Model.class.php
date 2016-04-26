<?php
/**
 * Model.class.php
 * $author$
 */

namespace Com;

abstract class Model {

	// 正常状态
	const ST_CREATE = 1;
	// 已更新
	const ST_UPDATE = 2;
	// 已删除
	const ST_DELETE = 3;

	// Model 实例
	protected $_m = null;
	// 当前 Model 名称
	protected $_model_name = '';

	public $prefield = ''; // 字段前缀

	// 构造方法
	public function __construct($name = '', $table_prefix = '', $connection = '') {
            
		// 如果当前模块名称为空
		if (!empty($name)) {
			$this->_model_name = $name;
		}
                
		// 初始化数据库连接
		$this->_m = new \Think\Model($this->get_model_name(), $table_prefix, $connection);
	}

	/**
	 * 得到当前的数据对象名称
	 * @access public
	 * @return string
	 */
	public function get_model_name() {

		// 如果当前 Model 名称为空
		if (empty($this->_model_name)) {
			$name = substr(get_class($this), 0, - strlen(C('DEFAULT_M_LAYER')));
			if ($pos = strrpos($name, '\\')) { // 有命名空间
				$this->_model_name = substr($name, $pos + 1);
			} else {
				$this->_model_name = $name;
			}
		}

		return $this->_model_name;
	}

	// 获取表名
	public function get_tname() {

		return $this->_m->getTableName();
	}

	/**
	 * 根据主键获取数据
	 * @param mixed $val 主键值
	 * @return Ambigous <multitype:, unknown>
	 */
	public function get($val) {

		// 设置条件
		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE `".$this->_m->getPk()."`=? AND `{$this->prefield}status`<?", array(
			(string)$val, $this->get_st_delete()
		));
	}

	/**
	 * 获取数据列表
	 * @param int|array $page_option 分页参数
	 *  + int => limit $page_option
	 *  + array => limit $page_option[0], $page_option[1]
	 * @param array $order_option 排序信息
	 */
	public function list_all($page_option = null, $order_option = array()) {

		// limit
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}
		// 执行 SQL
		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE `{$this->prefield}status`<?{$orderby}{$limit}", array(
			$this->get_st_delete()
		));
	}

	/**
	 * 删除字段
	 * @param mixed $vals 主键对应的值
	 * @return object
	 */
	public function delete($vals) {

		// 如果参数为空
		if (empty($vals)) {
			E('_ERR_DELETE_CONDS_INVALID_');
			return false;
		}

		// 执行 SQL
		return $this->_m->execsql("UPDATE __TABLE__ SET `{$this->prefield}status`=?, `{$this->prefield}deleted`=? WHERE `".$this->_m->getPk()."` IN (?) AND `{$this->prefield}status`<?", array(
			$this->get_st_delete(), NOW_TIME, (array)$vals, $this->get_st_delete()
		));
	}

	/**
	 * 更新数据
	 * @param int $val 主键键值
	 * @param array $data 待更新数据
	 */
	public function update($val = null, $data = array()) {

		// 补齐时间信息
		$this->_fill_status_timestamp($data, $this->get_st_update());

		// PDO params
		$params = array();
		// 更新时 SET 数据
		$sets = array();
		if (!$this->_parse_set($sets, $params, $data)) {
			return false;
		}

		// 加入查询条件

		$params[] = (array)$val;
		$params[] = $this->get_st_delete();
		// 执行
		return $this->_m->execsql("UPDATE __TABLE__ SET ".implode(",", $sets)." WHERE `".$this->_m->getPk()."` IN (?) AND `{$this->prefield}status`<?", $params);
	}

	/**
	 * 统计总数
	 * @param mixed $options 分页选项
	 * @return Ambigous
	 */
	public function count($page_options = 0) {

		// LIMIT
		$limit = '';
		if (!$this->_limit($limit, $page_options)) {
			return false;
		}

		// 状态查询条件
		$params = array($this->get_st_delete());
		// 执行 SQL
		return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE `{$this->prefield}status`<?{$limit}", $params);
	}

	/**
	 * 根据主键值读取数据
	 * @param int|string|array $vals 查询条件
	 * @param array $orders 排序
	 */
	public function list_by_pks($vals, $orders = array()) {

		if (empty($vals)) {
			return array();
		}

		// 设置条件
		$orderby = '';
		if (!$this->_order_by($orderby, $orders)) {
			return false;
		}

		// 状态查询条件
		$params = array($vals, $this->get_st_delete());
		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE `".$this->_m->getPk()."` IN (?) AND `{$this->prefield}status`<?", $params);
	}

	/**
	 * 根据条件读取数据
	 * @param array $conds 条件数组
	 * @throws service_exception
	 */
	public function get_by_conds($conds) {

		$params = array();
		// 条件
		$wheres = array();
		if (!$this->_parse_where($wheres, $params, $conds)) {
			return false;
		}

		// 状态条件
		$wheres[] = "`{$this->prefield}status`<?";
		$params[] = $this->get_st_delete();
		// 执行 SQL
		return $this->_m->fetch_row("SELECT * FROM __TABLE__ WHERE ".implode(' AND ', $wheres), $params);
	}

	/**
	 * 根据条件读取数据数组
	 * @param array $conds 条件数组
	 * @param int|array $page_option 分页参数
	 * @param array $order_option 排序
	 * @throws service_exception
	 */
	public function list_by_conds($conds, $page_option = null, $order_option = array()) {

		$params = array();
		// 条件
		$wheres = array();
		if (!$this->_parse_where($wheres, $params, $conds)) {
			return false;
		}

		// 状态条件
		$wheres[] = "`{$this->prefield}status`<?";
		$params[] = $this->get_st_delete();

		// 排序
		$orderby = '';
		if (!$this->_order_by($orderby, $order_option)) {
			return false;
		}

		// 分页参数
		$limit = '';
		if (!$this->_limit($limit, $page_option)) {
			return false;
		}

		// 读取记录
		return $this->_m->fetch_array("SELECT * FROM __TABLE__ WHERE ".implode(' AND ', $wheres)."{$orderby}{$limit}", $params);
	}

	/**
	 * 根据条件更新数据
	 * @param array $conds 条件数组
	 * @param array $data 数据数组
	 * @throws service_exception
	 */
	public function update_by_conds($conds, $data) {

		// 补齐时间信息
		$this->_fill_status_timestamp($data, $this->get_st_update());

		$params = array();
		// 更新时 SET 数据
		$sets = array();
		if (!$this->_parse_set($sets, $params, $data)) {
			return false;
		}

		// 更新条件
		$wheres = array();
		if (!$this->_parse_where($wheres, $params, $conds)) {
			return false;
		}

		// 状态条件
		$wheres[] = "`{$this->prefield}status`<?";
		$params[] = $this->get_st_delete();

		return $this->_m->execsql("UPDATE __TABLE__ SET ".implode(',', $sets)." WHERE ".implode(' AND ', $wheres), $params);
	}

	/**
	 * 根据条件删除数据
	 * @param array $conds 删除条件数组
	 * @throws Exception
	 * @throws service_exception
	 */
	public function delete_by_conds($conds) {

		$params = array();
		// SET
		$sets = array("`{$this->prefield}status`=?", "`{$this->prefield}deleted`=?");
		$params[] = $this->get_st_delete();
		$params[] = NOW_TIME;
		// 更新条件
		$wheres = array();
		if (!$this->_parse_where($wheres, $params, $conds)) {
			return false;
		}

		// 状态条件
		$wheres[] = "`{$this->prefield}status`<?";
		$params[] = $this->get_st_delete();

		return $this->_m->execsql("UPDATE __TABLE__ SET ".implode(',', $sets)." WHERE ".implode(' AND ', $wheres), $params);
	}

	/**
	 * 根据条件计算数量
	 * @param array $conds
	 * @throws service_exception
	 * @return number
	 */
	public function count_by_conds($conds) {

		$params = array();
		// 更新条件
		$wheres = array();
		if (!$this->_parse_where($wheres, $params, $conds)) {
			return false;
		}

		// 状态条件
		$wheres[] = "`{$this->prefield}status`<?";
		$params[] = $this->get_st_delete();

		return $this->_m->result("SELECT COUNT(*) FROM __TABLE__ WHERE ".implode(' AND ', $wheres), $params);
	}

	/**
	 * 插入单条数据
	 * @param string $data 待插入数据
	 * @param array $options 表达式
	 * @param boolean $replace 是否使用 REPLACE INTO
	 * @return \Think\mixed
	 */
	public function insert($data = '', $options = array(), $replace = false) {

		// 补齐时间信息
		$this->_fill_status_timestamp($data, $this->get_st_create());
		return $this->_m->insert($data, $options, $replace);
	}

	/**
	 * 插入多条数据
	 * @param array $list 待插入数据数组
	 * @param array $options 表达式
	 * @param boolean $replace 是否使用 REPLACE INTO
	 * @return Ambigous <boolean, string, unknown>
	 */
	public function insert_all($list, $options = array(), $replace = false) {

		// 补齐所有时间信息
		foreach ($list as &$_data) {
			$this->_fill_status_timestamp($_data, $this->get_st_create());
		}

		return $this->_m->insert_all($list, $options, $replace);
	}

	// 开始存储过程
	public function start_trans() {

		return $this->_m->startTrans();
	}

	// 回滚
	public function rollback() {

		return $this->_m->rollback();
	}

	// 提交
	public function commit() {

		return $this->_m->commit();
	}

	/**
	 * 解析 SQL 的 WHERE 参数
	 * @param array $wheres SQL 的更新
	 * @param array $params PDO 的参数数组
	 * @param array $data WHERE 的数据
	 * @return boolean
	 */
	protected function _parse_where(&$wheres, &$params, $data) {

		// 遍历所有 where
		foreach ($data as $field => $_v) {
			// 检查字段的合法性
			$field = trim($field);
			if (!$this->__chk_field($field)) {
				E('_ERR_WHERE_FIELD_INVALID');
				return false;
			}

			if (false === strpos($field, '?')) {
				$wheres[] = is_array($_v) ? "{$field} IN (?)" : "{$field}=?";
			} else {
				$wheres[] = $field;
			}

			$params[] = $_v;
		}

		return true;
	}

	/**
	 * 解析 SQL 的 SET 参数
	 * @param array $sets SQL 的更新
	 * @param array $params PDO 的参数数组
	 * @param array $data SET 的数据
	 * @return boolean
	 */
	protected function _parse_set(&$sets, &$params, $data) {

		// 遍历所有 set
		foreach ($data as $field => $_v) {
			// 检查字段的合法性
			if (!$this->__chk_field($field)) {
				E('_ERR_SET_FIELD_INVALID');
				return false;
			}

			$sets[] = "{$field}=?";
			$params[] = is_array($_v) ? serialize($_v) : $_v;
		}

		return true;
	}

	/**
	 * 检查字段合法性
	 * @param string $field 字段名
	 * @return number
	 */
	private function __chk_field($field) {

		// 验证字段规则
		if (!preg_match("/^[\`\=\!\<\>\(\)\s\?\w\.]+$/i", $field)) {
			return false;
		}

		return true;
	}

	/**
	 * 生成排序
	 * @param string $orderby 排序字串
	 * @param array $options 传入参数
	 * @return boolean
	 */
	protected function _order_by(&$orderby, $options = array()) {

		$orders = array();
		// 遍历所有排序
		foreach ($options as $_field => $_dir) {
			// 检查字段的合法性
			if (!$this->__chk_field($_field)) {
				E('_ERR_ORDER_BY_FIELD_INVALID_', array('field' => $_field));
				return false;
			}

			$_dir = rstrtoupper($_dir);
			$orders[] = $_field.' '.('DESC' == $_dir ? $_dir : 'ASC');
		}

		// 排序字串
		if (!empty($orders)) {
			$orderby = ' ORDER BY '.implode(',', $orders);
		}

		return true;
	}

	/**
	 * 解析 SQL limit
	 * @param string $limit limit字串
	 * @param number $options 传入参数
	 * @return boolean
	 */
	protected function _limit(&$limit, $options = 10) {

		// 如果参数为数组时
		if (is_array($options)) {
			// 如果数组元素个数 <= 2
			if (count($options) <= 2) {
				foreach ($options as &$_i) {
					$_i = (int)$_i;
				}

				unset($_i);
				$limit = implode(',', $options);
			} else { // > 2 时, 报错
				E('_ERR_SQL_LIMIT_INVALID_');
				return false;
			}
		} else {
			$limit = (int)$options;
		}

		// 如果有, 则
		if (!empty($limit)) {
			$limit = ' LIMIT '.$limit;
		} else {
			$limit = '';
		}

		return true;
	}

	/**
	 * 根据状态补齐时间字段信息
	 * @param array $data 更新数据
	 * @param int $status 状态值
	 * @return boolean
	 */
	protected function _fill_status_timestamp(&$data, $status) {

		// 状态字段
		$k_status = $this->prefield . 'status';
		if ($status == $this->get_st_create()) { // 创建时间字段
			$k_ts = $this->prefield . 'created';
		} elseif ($status == $this->get_st_update()) { // 更新时间字段
			$k_ts = $this->prefield . 'updated';
		} elseif ($status == $this->get_st_delete()) { // 删除时间字段
			$k_ts = $this->prefield . 'deleted';
		}

		// 如果没有指定状态值
		if (!isset($data[$k_status])) {
			$data[$k_status] = $this->get_st_create();
		}

		// 如果没有指定创建时间
		if (!isset($data[$k_ts])) {
			$data[$k_ts] = NOW_TIME;
		}

		return true;
	}

	// 获取删除状态值
	public function get_st_delete() {

		return self::ST_DELETE;
	}

	public function get_st_update() {

		return self::ST_UPDATE;
	}

	public function get_st_create() {

		return self::ST_CREATE;
	}

}
