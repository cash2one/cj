<?php
/**
 * 投票选项表
 * $Author$
 * $Id$
 */

class voa_d_oa_vote_option extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.vote_option';
	/** 主键 */
	private static $__pk = 'vo_id';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 获取所有投票选项列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `vo_status`<'%d'
			ORDER BY `vo_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 读取投票选项 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `vo_id`='%d' AND `vo_status`<'%d'
			ORDER BY `vo_id` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `vo_id` IN (%n) AND `vo_status`<'%d'
			ORDER BY `vo_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 通过 v_id 读取投票选项列表 */
	public static function fetch_by_v_id($ids, $start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `v_id` IN (%n) AND `vo_status`<'%d'
			ORDER BY `vo_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 通过 v_id 和 vo_id 读取投票选项 */
	public static function fetch_by_v_id_vo_ids($v_id, $vo_ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `vo_id` IN (%n) AND `v_id`='%d' AND `vo_status`<'%d'
			ORDER BY `vo_displayorder`", array(
				self::$__table, $vo_ids, $v_id, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 新增投票选项信息
	 *
	 * @param array $data 用户数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['vo_status'])) {
			$data['vo_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['vo_created'])) {
			$data['vo_created'] = startup_env::get('timestamp');
		}

		/** 更新时间在写入的时候和创建时间一致，用来排序（特殊） */
		if (empty($data['vo_updated'])) {
			$data['vo_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['vo_updated'])) {
			$data['vo_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/** 根据 vo_id 进行 +1 操作 */
	public static function choices($ids, $shard_key = array()) {
		return parent::_query(self::$__table, "UPDATE %t SET `vo_votes`=`vo_votes`+1 WHERE `vo_id` IN (%n)", array(
			self::$__table, $ids
		), $shard_key);
	}

	/**
	 * 根据 v_id 删除投票选项
	 * @param int|array $ids 主题id或数组
	 * @param boolean $unbuffered
	 * @param boolean $low_priority
	 */
	public static function delete_by_v_id($ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'vo_status' => self::STATUS_REMOVE,
			'vo_deleted' => startup_env::get('timestamp')
		);
		return parent::_update(self::$__table, $data, db_help::field('v_id', $ids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 根据id删除投票选项
	 * @param int $v_id 投票id
	 * @param int|array $ids 用户 id 或 id 数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_v_id_vo_ids($v_id, $ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
			'vo_status' => self::STATUS_REMOVE,
			'vo_deleted' => startup_env::get('timestamp')
		);

		return parent::_update(self::$__table, $data, "`v_id`='{$v_id}'".db_help::field(self::$__pk, $ids), $unbuffered, $low_priority, $shard_key);
	}

	/**
	 * 删除指定vo_id的投票选项
	 * @param int|array $vo_ids
	 * @param string $unbuffered
	 * @param string $low_priority
	 * @return Ambigous <void, boolean>
	 */
	public static function delete_by_id($vo_ids, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		$data = array(
				'vo_status' => self::STATUS_REMOVE,
				'vo_deleted' => startup_env::get('timestamp')
		);
		return parent::_update(self::$__table, $data, db_help::field(self::$__pk, $vo_ids), $unbuffered, $low_priority, $shard_key);
	}
}
