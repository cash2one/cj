<?php
/**
 * 参与投票用户记录表
 * $Author$
 * $Id$
 */

class voa_d_oa_vote_mem extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.vote_mem';
	/** 主键 */
	private static $__pk = 'vm_id';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 获取所有参与投票用户列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `vm_status`<'%d'
			ORDER BY `vm_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 读取参与投票用户 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `vm_id`='%d' AND `vm_status`<'%d'
			ORDER BY `vm_id` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `vm_id` IN (%n) AND `vm_status`<'%d'
			ORDER BY `vm_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 通过 v_id 读取参与投票用户列表 */
	public static function fetch_by_v_id($ids, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `v_id` IN (%n) AND `vm_status`<'%d'
			ORDER BY `vm_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 v_id 和 uid 读取投票记录 */
	public static function fetch_by_v_id_uid($v_id, $uid, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `v_id`='%d' AND `m_uid`='%d' AND `vm_status`<'%d'
			ORDER BY `vm_id` DESC", array(
				self::$__table, $v_id, $uid, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 新增
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['vm_status'])) {
			$data['vm_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['vm_created'])) {
			$data['vm_created'] = startup_env::get('timestamp');
		}

		/** 更新时间在写入的时候和创建时间一致，用来排序（特殊） */
		if (empty($data['vm_updated'])) {
			$data['vm_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['vm_updated'])) {
			$data['vm_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据投票 id 和 uid 删除参与投票用户
	 * @param int $v_id 投票id
	 * @param int|array $uids 用户 uid
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 */
	public static function delete_by_v_id_uids($v_id, $uids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'vm_status' => self::STATUS_REMOVE,
			'vm_deleted' => startup_env::get('timestamp')
		);

		return parent::_update(self::$__table, $data, "`v_id`='{$v_id}' AND ".db_help::field('m_uid', $uids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据 v_id 删除参与投票用户
	 * @param int|array $ids 主题id或数组
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 */
	public static function delete_by_v_id($ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'vm_status' => self::STATUS_REMOVE,
			'vm_deleted' => startup_env::get('timestamp')
		);
		return parent::_update(self::$__table, $data, db_help::field('v_id', $ids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据id删除参与投票用户
	 *
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {

		$data = array(
			'vm_status' => self::STATUS_REMOVE,
			'vm_deleted' => startup_env::get('timestamp')
		);

		return parent::_update(self::$__table, $data, db_help::field(self::$__pk, $ids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 统计指定投票的投票总数
	 * @param number $v_id
	 * @return number
	 */
	public static function count_by_v_id($v_id, $shard_key = array()) {
		return (int)parent::_result_first(self::$__table, "SELECT COUNT(`vm_id`) FROM %t WHERE %i AND %i", array(
			self::$__table, db_help::field('v_id', $v_id), db_help::field('vm_status', self::STATUS_REMOVE, '<')
		), $shard_key);
	}

	/**
	 * 删除指定选项的记录
	 * @param int|array $vo_id
	 * @param string $unbuffered
	 * @param string $low_priority
	 * @return Ambigous <void, boolean>
	 */
	public static function delete_by_vo_id($vo_id, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'vm_status' => self::STATUS_REMOVE,
			'vm_deleted' => startup_env::get('timestamp')
		);
		return parent::_update(self::$__table, $data, db_help::field('vo_id', $vo_id), $unbuffered, $low_priority, $shard_key);
	}
}
