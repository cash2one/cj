<?php
/**
 * 名片信息表
 * $Author$
 * $Id$
 */

class voa_d_oa_namecard extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.namecard';
	/** 主键 */
	private static $__pk = 'nc_id';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 获取所有名片信息列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `nc_status`<'%d'
			ORDER BY `nc_updated` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 读取名片信息 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `nc_id`='%d' AND `nc_status`<'%d'
			ORDER BY `nc_updated` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `nc_id` IN (%n) AND `nc_status`<'%d'
			ORDER BY `nc_updated` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据 uid 和 ncf_id 获取名片信息
	 * @param int $uid 用户 uid
	 * @param int|array $ncf_ids 名片群组id
	 * @param int $start
	 * @param int $limit
	 */
	public static function fetch_mine_by_ncf_id($uid, $ncf_ids, $start, $limit, $shard_key = array()) {
		$ncf_ids = (array)$ncf_ids;
		$where = '';
		if (!empty($ncf_ids)) {
			$where = ' AND '.db_help::field('ncf_id', $ncf_ids);
		}

		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid`='%d'{$where} AND `nc_status`<'%d'
			ORDER BY `nc_displayorder` DESC, `nc_updated` DESC".db_help::limit($start, $limit), array(
				self::$__table, $uid, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据用户 uid 读取我的名片信息 */
	public static function fetch_mine_by_uid($uid, $sotext, $start = 0, $limit = 0, $shard_key = array()) {
		$wheres = array('`m_uid`=%d AND `nc_status`<%d');
		$params = array(self::$__table, $uid, self::STATUS_REMOVE);
		if (!empty($sotext)) {
			$wheres[] = 'nc_realname LIKE %s';
			$params[] = "%{$sotext}%";
		}

		$wherestr = implode(' AND ', $wheres);

		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE {$wherestr}
			ORDER BY `nc_displayorder` DESC, `nc_updated` DESC".db_help::limit($start, $limit),
			$params, self::$__pk, $shard_key
		);
	}

	/**
	 * 根据 uid 统计名片数
	 * @param int $uid
	 */
	public static function count_by_uid($uid, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT * FROM %t
			WHERE m_uid=%d AND nc_status<%d", array(
				self::$__table, $uid, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/**
	 * 新增名片信息
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insernc_id = false, $replace = false, $silent = false, $shard_key = array()) {

		if (empty($data['nc_status'])) {
			$data['nc_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['nc_created'])) {
			$data['nc_created'] = startup_env::get('timestamp');
		}

		/** 更新时间在写入的时候和创建时间一致，用来排序（特殊） */
		if (empty($data['nc_updated'])) {
			$data['nc_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insernc_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {

		if (empty($data['nc_status'])) {
			$data['nc_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['nc_updated'])) {
			$data['nc_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据UID删除名片信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_uids($uids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'nc_status' => self::STATUS_REMOVE,
			'nc_deleted' => startup_env::get('timestamp')
		);

		return parent::_update(self::$__table, $data, db_help::field('m_uid', $uids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据id删除名片信息
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'nc_status' => self::STATUS_REMOVE,
			'nc_deleted' => startup_env::get('timestamp')
		);

		return parent::_update(self::$__table, $data, db_help::field(self::$__pk, $ids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 构造查询SQL语句
	 * @param array $conditions
	 * @return string
	 */
// 	public static function search_by_conditions($conditions) {
// 		$where = array();
// 		if (isset($conditions['m_uid']) && $conditions['m_uid']) {
// 			$where[] = db_help::field('m_uid', $conditions['m_uid']);
// 		}
// 		$where[] = db_help::field('nc_status', self::STATUS_REMOVE, '<');
// 		return $where ? implode(' AND ', $where) : 1;
// 	}

	/**
	 * 计算指定条件的名片数量
	 * @param array $conditions
	 * @return number
	 */
	public static function count_by_conditions($conditions, $shard_key = array()) {
		return (int) parent::_result_first(self::$__table, "SELECT COUNT(%i) FROM %t WHERE %i", array(
			self::$__pk, self::$__table, self::parse_conditions($conditions)
		), $shard_key);
	}

	/**
	 * 列出指定条件的名片
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_all_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array) parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i ORDER BY `nc_id` DESC".db_help::limit($start, $limit),array(
				self::$__table, self::parse_conditions($conditions)
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据条件读取用户的名片夹信息
	 * @param int $uid 用户uid
	 * @param array $conditions 条件
	 * @param number $start 开始
	 * @param number $limit 结束
	 * @param array $shard_key 分表
	 */
	public static function list_by_conditions($uid, $conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array)parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i ORDER BY nc_id DESC".db_help::limit($start, $limit), array(
				self::$__table, self::parse_conditions($conditions)
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
// 			if (!in_array($field, self::$__fields)) {
// 				continue;
// 			}

			$f_v = $v;
			$gule = '=';
			/** 如果条件为数组, 则 */
			if (is_array($v)) {
				$f_v = $v[0];
				$gule = empty($v[1]) ? '=' : $v[1];
			}

			$wheres[] = db_help::field($field, $f_v, $gule);
		}

		$wheres[] = db_help::field('nc_status', self::STATUS_REMOVE, '<');

		return empty($wheres) ? 1 : implode(' AND ', $wheres);
	}
}
