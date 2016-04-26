<?php
/**
 * 信鸽模板消息发送队列表
 * $Author$
 * $Id$
 */

class voa_d_oa_xinge_queue extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.xinge_queue';
	/** 主键 */
	private static $__pk = 'xgq_id';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 发送成功 */
	const STATUS_SUCCEED = 2;
	/** 发送失败 */
	const STATUS_FAILED = 3;
	/** 已删除 */
	const STATUS_REMOVE = 4;

	/** 获取所有模板消息发送队列 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `xgq_status`<'%d'
			ORDER BY `xgq_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 根据 id 值, 读取模板消息发送队列 */
	public static function fetch_by_id($id, $shard_key = array()) {
		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `xgq_id`='%d' AND `xgq_status`<'%d'
			ORDER BY `xgq_id` DESC", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `xgq_id` IN (%n) AND `xgq_status`<'%d'
			ORDER BY `xgq_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 获取待发送列表 */
	public static function fetch_send_list($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `xgq_status`='%d'
			ORDER BY `xgq_failtimes` ASC, `xgq_id` ASC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_NORMAL
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 根据id获取未发送队列信息
	 * @param array $ids id数组
	 * @param array $shard_key 分库参数
	 * @return Ambigous <void, boolean>
	 */
	public static function fetch_unsend_by_ids($ids, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE xgq_id IN (%n) AND xgq_status=%d", array(
				self::$__table, $ids, self::STATUS_NORMAL
			), self::$__pk, $shard_key
		);
	}

	/**
	 * 新增模板消息发送队列
	 *
	 * @param array $data 数据数组, 下标为字段名, 值为对应的信息
	 * @param boolean $return_insert_id 是否返回自增ID
	 * @param boolean $replace 是否使用 replace into
	 * @param boolean $silent 忽略警告信息
	 * @return boolean|int
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $silent = false, $shard_key = array()) {
		if (empty($data['xgq_status'])) {
			$data['xgq_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['xgq_created'])) {
			$data['xgq_created'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	/** 更新 */
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {
		if (empty($data['xgq_updated'])) {
			$data['xgq_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, $unbuffered, $low_priority, $shard_key);
	}

	/** 更新状态 */
	public static function update_by_ids($data, $ids, $shard_key = array()) {
		return parent::_update(self::$__table, $data, db_help::field(self::$__pk, $ids), false, false, $shard_key);
	}

	/** 更新失败次数 */
	public static function increase_times_by_ids($ids, $shard_key = array()) {
		return parent::_incr(self::$__table, 'xgq_failtimes', db_help::field(self::$__pk, $ids), array(), 1, $shard_key);
	}

	/**
	 * 根据ID删除模板消息发送队列
	 *
	 * @param int|array $ids ID或ID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_ids($ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'xgq_status' => self::STATUS_REMOVE,
			'xgq_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

	/**
	 * 获取未发送消息
	 * @param int $sendtime 发送时间戳
	 * @param array $shard_key
	 */
	public static function fetch_unsend_by_sendtime($sendtime, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE xgq_status=%d AND xgq_sendtime<%d", array(
				self::$__table, self::STATUS_NORMAL, $sendtime
			), self::$__pk, $shard_key
		);
	}
}
