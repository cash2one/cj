<?php
/**
 * 数据表格操作基类
 * $Author$
 * $Id$
 */

class dao_mysql {

	/**
	 * insert
	 *
	 * @param string $table 	数据表名称
	 * @param array $data		字段、值的关联数组
	 * @return mixed 成功返回新记录的id数
	 */
	protected  static function _insert($table, $data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		try {
			$t = &db_table::factory($table, $shard_key);
			return $t->insert($data, $return_insert_id, $replace, $silent);
		} catch (Exception $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}

		return false;
	}

	/**
	 * update
	 * 根据某个字段更新一条或多条记录
	 *
	 * @param  string $table
	 * @param  array $data
	 * @param  array $conditions
	 * @return void
	 */
	protected static function _update($table, $data, $conditions, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		try {
			$t = &db_table::factory($table, $shard_key);
			return $t->update($data, $conditions, $unbuffered, $low_priority);
		} catch (Exception $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}

		return false;
	}

	/**
	 * delete
	 * 根据主键删除单条记录
	 *
	 * @param string $table 表名
	 * @param  array $conditions
	 * @return boolean
	 */
	protected static function _delete($table, $conditions, $limit = 0, $unbuffered = false, $shard_key = array()) {
		try {
			$t = &db_table::factory($table, $shard_key);
			return $t->delete($conditions, $limit, $unbuffered);
		} catch (Exception $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}

		return false;
	}

	/**
	 * query
	 * @param string $sql SQL语句
	 * @param  array $args
	 * @return boolean
	 */
	protected static function _query($table, $sql, $args = array(), $shard_key = array()) {
		try {
			$t = &db_table::factory($table, $shard_key);
			return $t->query($sql, $args);
		} catch (Exception $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}

		return false;
	}

	/**
	 * fetch_all
	 * 获取记录
	 *
	 * @param  mixed $table
	 * @param  mixed $sql
	 * @param  mixed $params
	 * @return void
	 */
	protected static function _fetch_all($table, $sql, $params = array(), $keyfield = '', $shard_key = array()) {
		try {
			$t = &db_table::factory($table, $shard_key);
			return $t->fetch_all($sql, $params, $keyfield);
		} catch (Exception $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}

		return false;
	}

	protected static function _fetch_first($table, $sql, $params = array(), $shard_key = array()) {
		try {
			$t = &db_table::factory($table, $shard_key);
			return $t->fetch_first($sql, $params);
		} catch (Exception $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}

		return false;
	}

	/**
	 * 取得结果数
	 *
	 * @param  mixed $table
	 * @param  mixed $sql
	 * @param  array $args
	 * @return void
	 */
	protected static function _result_first($table, $sql, $args = array(), $shard_key = array()) {
		try {
			$t = &db_table::factory($table, $shard_key);
			return $t->result_first($sql, $args);
		} catch (Exception $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}

		return false;
	}

	/**
	 * count
	 * 计算总数
	 *
	 * @param  mixed $table
	 * @param  mixed $conditions
	 * @param  array $params
	 * @return void
	 */
	protected static function _count($table, $conditions = null, $params = array(), $shard_key = array()) {
		array_unshift($params, $table);
		return self::result_first($table, "SELECT COUNT(*) FROM %t {$conditions}", $params, $shard_key);
	}

	/**
	 * incr
	 * 给一个字段增加一个单位值
	 *
	 * @param string $table 表名
	 * @param string $field 字段名
	 * @param string $conditions 条件
	 * @param array $params 参数
	 * @param integer $unit
	 * @return boolean
	 */
	protected static function _incr($table, $field, $conditions = null, $params = array(),  $unit = 1, $shard_key = array()) {
		try {
			$t = &db_table::factory($table, $shard_key);
			array_unshift($params, $table);
			return $t->query("UPDATE %t SET `{$field}`=`{$field}`+{$unit} WHERE {$conditions}", $params);
		} catch (Exception $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}

		return false;
	}

	/**
	 * decr
	 * 给一个字段减少一个单位值
	 *
	 * @param string $table 表名
	 * @param string $field 字段名
	 * @param string $conditions 条件
	 * @param array $params 参数
	 * @param integer $unit
	 * @return boolean
	 */
	protected static function _decr($table, $field, $conditions = null, $params = array(),  $unit = 1, $shard_key = array()) {
		try {
			$t = &db_table::factory($table, $shard_key);
			array_unshift($params, $table);
			return $t->query("UPDATE %t SET `{$field}`=`{$field}`-{$unit} WHERE {$conditions}", $params);
		} catch (Exception $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}

		return false;
	}

	/**
	 * 清空表数据
	 * @param string $table 表名称
	 * @throws dao_exception
	 * @return boolean
	 */
	protected static function _truncate($table, $shard_key = array()) {
		try {
			$t = &db_table::factory($table, $shard_key);
			return $t->query("TRUNCATE %t", array($table), 'SILENT');
		} catch (Exception $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}

		return false;
	}

	/**
	 * 读取表字段
	 * @param string $table 表名称
	 * @throws dao_exception
	 * @return Ambigous <boolean, multitype:unknown >|boolean
	 */
	protected static function _fetch_all_field($table, $shard_key = array()) {
		try {
			$t = &db_table::factory($table, $shard_key);
			$data = array();
			$query = $t->query("SHOW FIELDS FROM %t", array($table));
			while ($row = $t->fetch($query)) {
				$data[$row['Field']] = $row['Default'];
			}

			return $data ? $data : false;
		} catch (Exception $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}

		return false;
	}

	/**
	 * 优化表
	 * @param string $table 表名称
	 * @throws dao_exception
	 * @return boolean
	 */
	protected static function _optimize($table, $shard_key = array()) {
		try {
			$t = &db_table::factory($table, $shard_key);
			return $t->query("OPTIMIZE TABLE %t", array($table), 'SILENT');
		} catch (Exception $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}

		return false;
	}

	/**
	 * 读取数据
	 * @param string $table 表名称
	 * @param int $resourceid 资源id
	 * @throws dao_exception
	 */
	protected static function _fetch($table, $resourceid, $shard_key = array()) {
		try {
			$t = &db_table::factory($table, $shard_key);
			return $t->fetch($resourceid);
		} catch (Exception $e) {
			throw new dao_exception($e->getMessage(), $e->getCode());
		}

		return false;
	}
}
