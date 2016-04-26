<?php
/**
 * voa_d_oa_notice_read
 * 通知公告已读表
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_d_oa_notice_read extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.notice_read';
	/** 主键 */
	private static $__pk = 'ntr_id';
	/** 所有字段名 */
	private static $__fields = array(
		'ntr_id', 'nt_id', 'cd_id',
		'ntr_status', 'ntr_created', 'ntr_updated', 'ntr_deleted'
	);
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 获取所有列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ntr_status`<'%d'
			ORDER BY `ntr_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据主键值读取数据 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `ntr_id`='%d' AND `ntr_status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ntr_id` IN (%n) AND `ntr_status`<'%d'
			ORDER BY `ntr_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据查询条件拼凑 sql 条件
	 * @param array $conditions 查询条件
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 */
	public static function parse_conditions($conditions = array()) {
		$wheres = array();
		/** 遍历条件 */
		foreach ($conditions as $field => $v) {
			/** 非当前表字段 */
			if (!in_array($field, self::$__fields)) {
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
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 * @return number
	 */
	public static function count_by_conditions($conditions, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t WHERE %i AND ntr_status<%d", array(
			self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE
		), $shard_key);
	}

	/**
	 * 列出指定条件的数据
	 * @param array $conditions
	 *  $conditions = array(
	 *  	'field1' => '查询条件', // 运算符为 =
	 *  	'field2' => array('查询条件', '查询运算符'),
	 *  	'field3' => array(array('查询条件1', '查询条件2', ...), '查询运算符'),
	 *  	...
	 *  );
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i AND ntr_status<%d ORDER BY %i DESC".db_help::limit($start, $limit), array(
				self::$__table, self::parse_conditions($conditions), self::STATUS_REMOVE, self::$__pk
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 新增信息
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['ntr_status'])) {
			$data['ntr_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['ntr_created'])) {
			$data['ntr_created'] = startup_env::get('timestamp');
		}

		if (empty($data['ntr_updated'])) {
			$data['ntr_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['ntr_updated'])) {
			$data['ntr_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据ID删除信息
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'ntr_status' => self::STATUS_REMOVE,
			'ntr_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
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
	 * 根据nt_id找到指定的m_uid是否已读
	 * @param number $nt_id
	 * @param array $m_uid
	 * @param array $shard_key
	 * @return array
	 */
	public static function fetch_all_by_nt_id_m_uid($nt_id, $m_uid, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE %i AND %i AND %i", array(
			self::$__table, db_help::field('nt_id', $nt_id), db_help::field('m_uid', $m_uid),
			db_help::field('ntr_status', self::STATUS_REMOVE, '<')
		), self::$__pk, $shard_key);
	}

	/**
	 * 计算指定公告nt_id的已阅读数
	 * @param number $nt_id
	 * @param array $shard_key
	 * @return number
	 */
	public static function count_all_by_nt_id($nt_id, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(%i) FROM %t WHERE %i AND %i", array(
			self::$__pk, db_help::field('nt_id', $nt_id), db_help::field('ntr_status', self::STATUS_REMOVE, '<')
		), $shard_key);
	}

	/**
	 * 找到指定公告nt_id的所有已阅读用户
	 * @param number $nt_id
	 * @param array $shard_key
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_all_by_nt_id($nt_id, $start = 0, $limit = 0, $shard_key = array()) {
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM % WHERE %i AND %i ORDER BY `ntr_created` DESC %i", array(
			db_help::field('nt_id', $nt_id), db_help::field('ntr_status', self::STATUS_REMOVE, '<'), db_help::limit($start, $limit)
		), self::$__pk, $shard_key);
	}

}

