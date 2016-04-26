<?php
/**
 * 名片中群组信息表
 * $Author$
 * $Id$
 */

class voa_d_oa_namecard_folder extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.namecard_folder';
	/** 主键 */
	private static $__pk = 'ncf_id';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 获取所有群组列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ncf_status`<'%d'
			ORDER BY `ncf_updated` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 读取群组信息 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `ncf_id`='%d' AND `ncf_status`<'%d'
			ORDER BY `ncf_updated` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ncf_id` IN (%n) AND `ncf_status`<'%d'
			ORDER BY `ncf_updated` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据用户 uid 读取群组信息
	 * @param int $uid uid
	 */
	public static function fetch_by_uid($uid, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `m_uid`='%d' AND `ncf_status`<'%d'
			ORDER BY `ncf_displayorder` DESC, `ncf_updated` DESC".db_help::limit($start, $limit), array(
				self::$__table, $uid, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据用户 uid 读取群组信息
	 * @param int $uid uid
	 */
	public static function count_by_uid($uid, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(*) FROM %t
			WHERE m_uid=%d AND ncf_status<%d", array(
				self::$__table, $uid, self::STATUS_REMOVE
			), $shard_key
		);
	}

	/**
	 * 新增名片群组信息
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_inserncf_id = false, $replace = false, $silent = false, $shard_key = array()) {

		if (empty($data['ncf_status'])) {
			$data['ncf_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['ncf_created'])) {
			$data['ncf_created'] = startup_env::get('timestamp');
		}

		/** 更新时间在写入的时候和创建时间一致，用来排序（特殊） */
		if (empty($data['ncf_updated'])) {
			$data['ncf_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_inserncf_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['ncf_status'])) {
			$data['ncf_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['ncf_updated'])) {
			$data['ncf_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/** 更新名片群组数值 */
	public static function update_num($ncf_ids, $gule = '+', $num = 1, $shard_key = array()) {
		$gule = '+' == $gule ? '+' : '-';
		$num = intval($num);
		$num = 1 > $num ? 1 : $num;
		return parent::_query(self::$__table, "UPDATE %t SET `ncf_num`=`ncf_num`{$gule}{$num}, `ncf_updated`='%d' WHERE `ncf_id` IN (%n)", array(
			self::$__table, startup_env::get('timestamp'), $ncf_ids
		), $shard_key);
	}

	/**
	 * 根据UID删除名片群组信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_uids($uids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'ncf_status' => self::STATUS_REMOVE,
			'ncf_deleted' => startup_env::get('timestamp')
		);

		return parent::_update(self::$__table, $data, db_help::field('m_uid', $uids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据id删除名片群组信息
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'ncf_status' => self::STATUS_REMOVE,
			'ncf_deleted' => startup_env::get('timestamp')
		);

		return parent::_update(self::$__table, $data, db_help::field(self::$__pk, $ids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 构造查询SQL语句
	 * @param array $conditions
	 * @return string
	 */
	public static function search_by_conditions($conditions) {
		$where = array();
		if (isset($conditions['m_uid']) && $conditions['m_uid']) {
			$where[] = db_help::field('m_uid', $conditions['m_uid']);
		}

		$where[] = db_help::field('ncf_status', self::STATUS_REMOVE, '<');
		if (isset($conditions['ncf_name']) && $conditions['ncf_name']) {
			$where[] = db_help::field('ncf_name', '%'.addcslashes($conditions['ncf_name'], '%_').'%', 'like');
		}

		return $where ? implode(' AND ', $where) : 1;
	}

	/**
	 * 计算指定条件的群组数量
	 * @param array $conditions
	 * @return number
	 */
	public static function count_by_conditions($conditions, $shard_key = array()) {
		return (int) parent::_result_first(self::$__table, "SELECT COUNT(%i) FROM %t WHERE %i", array(
			self::$__pk, self::$__table, self::search_by_conditions($conditions)
		), $shard_key);
	}

	/**
	 * 列出指定条件的群组
	 * @param array $conditions
	 * @param number $start
	 * @param number $limit
	 * @return array
	 */
	public static function fetch_all_by_conditions($conditions, $start = 0, $limit = 0, $shard_key = array()) {
		return (array) parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE %i ORDER BY `ncf_id` DESC".db_help::limit($start, $limit),array(
				self::$__table, self::search_by_conditions($conditions)
			), self::$__pk, $shard_key
		);
	}
}
