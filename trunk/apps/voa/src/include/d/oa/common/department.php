<?php

/**
 * department.php
 * 部门管理数据控制
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_d_oa_common_department extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.common_department';
	/** 主键 */
	private static $__pk = 'cd_id';
	/** 字段前缀 */
	private static $__prefix = 'cd_';

	/** @var array 表字段 */
	private static $__fields = array();

	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 全公司 */
	const PURVIEW_AllCOMPANY = 1;
	/** 仅本部门 */
	const PURVIEW_OLNYOWNSECTION = 2;
	/** 仅子部门 */
	const PURVIEW_OLNYCHILDSECTION = 3;


	/**********************/

	/** 最大允许添加的部门个数 */
	const COUNT_MAX = 1500;

	/**********************/

	/**
	 * <p><strong style="color:blue">【D】获取带前缀的字段名</strong></p>
	 * @author Deepseath
	 * @param string $field 无前缀的字段名
	 * @return string 带前缀的字段名
	 */
	public static function fieldname($field) {
		return self::$__prefix . $field;
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键值获取单条数据</strong></p>
	 * @author Deepseath
	 * @param int $value 主键值
	 */
	public static function fetch($value, $shard_key = array()) {
		return parent::_fetch_first(self::$__table,
			"SELECT * FROM %t WHERE %i='%d' AND %i<'%d' LIMIT 1",
			array(self::$__table, self::$__pk, $value, self::fieldname('status'), self::STATUS_REMOVE), $shard_key
		);
	}

	/**
	 * 根据用户id获取最大的部门查看权限
	 * @param uid $uid
	 * @return array
	 */
	public static function fetch_purview($uid, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT MIN(`cd_purview`)  as purview FROM  %t WHERE `%i` IN (SELECT  %i
                 FROM  `oa_member_department`WHERE m_uid =$uid AND md_status<%d)", array(
			self::$__table, self::$__pk, self::$__pk, self::STATUS_REMOVE), $shard_key
		);
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键更新</strong></p>
	 * @author Deepseath
	 * @param array $data 需要更新的数据数组
	 * @param string|number $value 主键值
	 */
	public static function update($data, $value, $shard_key = array()) {
		if (empty($data[self::fieldname('status')])) {
			$data[self::fieldname('status')] = self::STATUS_UPDATE;
		}

		if (empty($data[self::fieldname('update')])) {
			$data[self::fieldname('updated')] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, array(self::$__pk => $value), false, false, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】根据主键删除</strong></p>
	 * @author Deepseath
	 * @param array|number $value 主键值
	 */
	public static function delete($value, $shard_key = array()) {
		return self::delete_by_conditions(array(self::$__pk => $value), $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】获取表字段默认数据</strong></p>
	 * @author Deepseath
	 * @return array
	 */
	public static function fetch_all_field($shard_key = array()) {
		return parent::_fetch_all_field(self::$__table, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】读取所有数据</strong></p>
	 * @author Deepseath
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i<'%d' ORDER BY %i" . db_help::limit($start, $limit),
			array(self::$__table, self::fieldname('status'), self::STATUS_REMOVE, self::fieldname('displayorder')), self::$__pk, $shard_key
		);
	}

	/**
	 * <p><strong style="color:blue">【D】读取所有指定主键值数据</strong></p>
	 * @author Deepseath
	 * @param int|array $value
	 * @param
	 * @param int $start
	 * @param int $limit
	 * @return array
	 */
	public static function fetch_all_by_key($value, $orderby = '', $sort = 'DESC', $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i AND %i ORDER BY %i %i", array(
			self::$__table,
			db_help::field(self::$__pk, $value),
			db_help::field(self::fieldname('status'), self::STATUS_REMOVE, '<'),
			db_help::order(($orderby ? $orderby : self::$__pk), $sort),
			db_help::limit($start, $limit)
		), self::$__pk, $shard_key
		);
	}

	/**
	 * <p><strong style="color:blue">【D】统计所有未删除的记录总数</strong></p>
	 * @author Deepseath
	 * @return number
	 */
	public static function count_all($shard_key = array()) {
		return (int)parent::_result_first(self::$__table,
			"SELECT COUNT(%i) FROM %t WHERE %i<'%d'",
			array(self::$__pk, self::$__table, self::fieldname('status'), self::STATUS_REMOVE), $shard_key
		);
	}

	/**
	 * <p><strong style="color:blue">【D】数据入库</strong></p>
	 * @author Deepseath
	 * @param array $data 入库数据数组
	 * @param boolean $return_insert_id
	 * @param boolean $replace
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $shard_key = array()) {
		if (empty($data[self::fieldname('status')])) {
			$data[self::fieldname('status')] = self::STATUS_NORMAL;
		}

		if (empty($data[self::fieldname('created')])) {
			$data[self::fieldname('created')] = startup_env::get('timestamp');
		}

		if (empty($data[self::fieldname('updated')])) {
			$data[self::fieldname('updated')] = $data[self::fieldname('created')];
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, false, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】根据条件更新</strong></p>
	 * @author Deepseath
	 * @param array $data 需要更新的数据数组
	 * @param array|string $conditions 更新条件
	 */
	public static function update_by_conditions($data, $conditions, $shard_key = array()) {
		if (empty($data[self::fieldname('status')])) {
			$data[self::fieldname('status')] = self::STATUS_UPDATE;
		}

		if (empty($data[self::fieldname('update')])) {
			$data[self::fieldname('updated')] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $conditions, false, false, $shard_key);
	}

	/**
	 * <p><strong style="color:blue">【D】根据条件删除 </strong></p>
	 * @author Deepseath
	 * @param array $conditions 删除条件
	 * @return void
	 */
	public static function delete_by_conditions($conditions, $shard_key = array()) {
		return self::update_by_conditions(array(
			self::fieldname('status') => self::STATUS_REMOVE,
			self::fieldname('deleted') => startup_env::get('timestamp')
		), $conditions, $shard_key);
	}

	/**
	 * 根据查询条件拼凑 sql 条件
	 * @param array $conditions 查询条件
	 *  $conditions = array(
	 *    'field1' => '查询条件', // 运算符为 =
	 *    'field2' => array('查询条件', '查询运算符'),
	 *    'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *    ...
	 *  );
	 */
	public static function parse_conditions($conditions = array()) {
		$wheres = array();
		/** 遍历条件 */
		foreach ($conditions as $field => $v) {
			/** 非当前表字段 */
			if (!empty(self::$__fields) && !in_array($field, self::$__fields)) {
				continue;
			}

			$f_v = $v;
			$gule = '=';
			/** 如果条件为数组, 则 */
			if (is_array($v)) {
				$f_v = $v[0];
				$gule = empty($v[1]) ? '=' : $v[1];
			}

			$wheres[] = db_help::field($field, $f_v, $gule);
		}

		return empty($wheres) ? 1 : implode(' AND ', $wheres);
	}

	/**
	 * 根据条件计算总数
	 * @param array $conditions
	 * @return number
	 */
	public static function count_by_conditions($conditions, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND cd_status<%d", array(
			self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE
		), $shard_key);
	}

	/**********************************************/

	/**
	 * (D) 统计某个名字的部门个数
	 * @author Deepseath
	 * @param string $name 管理组名称
	 * @param number $cd_id 除此ID外
	 * @return number
	 */
	public static function count_by_name_notid($name, $cd_id = 0, $shard_key = array()) {
		return parent::_result_first(self::$__table,
			"SELECT COUNT(`cd_name`) FROM %t WHERE `cd_name`=%s AND `cd_status`<%d AND `cd_id`<>%d",
			array(self::$__table, $name, self::STATUS_REMOVE, $cd_id), $shard_key
		);
	}

	/**
	 * (D) 为指定部门 cd_id 增加单位 unit_value 成员计数值
	 * <p style="color:red">不推荐直接使用该方法，请调用voa_uda_frontend_department_update->update_usernum()方法</p>
	 * @param number $cd_id
	 * @param number $unit_value 增加的数值，默认为：1
	 * @param array $shard_key
	 * @return boolean
	 */
	public static function increase_usernum_by_cd_id($cd_id, $unit_value = 1, $shard_key = array()) {
		return parent::_incr(self::$__table, 'cd_usernum', db_help::field('cd_id', $cd_id), array(), $unit_value, $shard_key);
	}

	/**
	 * (D) 为指定部门 cd_id 减少单位 unit_value 成员计数值
	 * <p style="color:red">不推荐直接使用该方法，请调用voa_uda_frontend_department_update->update_usernum()方法</p>
	 * @param number $cd_id
	 * @param number $unit_value 减少的数值，默认为：1
	 * @param array $shard_key
	 * @return boolean
	 */
	public static function decrease_usernum_by_cd_id($cd_id, $unit_value = 1, $shard_key = array()) {
		return parent::_decr(self::$__table, 'cd_usernum', db_help::field('cd_id', $cd_id), array(), $unit_value, $shard_key);
	}

	/**
	 * 根据部门名称找到部门信息
	 * @param string $cd_name
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_by_cd_name($cd_name, $shard_key = array()) {
		return (array)parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i LIMIT 1", array(
			self::$__table, db_help::field('cd_name', $cd_name), db_help::field('cd_status', self::STATUS_REMOVE, '<')
		), $shard_key);
	}

	/**
	 * 根据企业微信的部门id获取本地部门信息
	 * @param string $cd_qywxid
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_by_qywxid($cd_qywxid, $shard_key = array()) {
		return (array)parent::_fetch_first(self::$__table, "SELECT * FROM %t WHERE %i AND %i LIMIT 1", array(
			self::$__table, db_help::field('cd_qywxid', $cd_qywxid), db_help::field('cd_status', self::STATUS_REMOVE, '<')
		), $shard_key);
	}

	/**
	 * 根据部门名称和上级id来读取部门信息
	 * @param string $cd_name 部门名称
	 * @param int $cd_upid 该部门的上级id
	 * @throws service_exception
	 */
	public static function fetch_by_cd_name_upid($cd_name, $cd_upid, $shard_key = array()) {
		return (array)parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE %i AND %i AND %i LIMIT 1", array(
			self::$__table, db_help::field('cd_name', $cd_name), db_help::field('cd_upid', $cd_upid), db_help::field('cd_status', self::STATUS_REMOVE, '<')
		), $shard_key);
	}

	/**
	 * 读取指定父级ID的部门
	 * @param number $cd_upid
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_all_by_upid($cd_upid, $shard_key = array()) {

		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t
				WHERE cd_upid=%d AND cd_status<%d ORDER BY cd_id ASC", array(
			self::$__table, $cd_upid, self::STATUS_REMOVE
		), $shard_key);
	}

	public static function fetch_all_by_cd_ids($cd_ids, $shard_key = array()) {

		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t
				WHERE cd_id IN(%i) AND cd_status<%d ORDER BY cd_id ASC", array(self::$__table, implode(',', $cd_ids), self::STATUS_REMOVE), $shard_key);
	}

}
