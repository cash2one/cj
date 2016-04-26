<?php
/**
 * voa_d_sqlabstruct
 * dao_mysql 的扩展
 * $Author$
 * $Id$
 */

class voa_d_sqlabstruct extends dao_mysql {
	// 表名
	public static $__table = '';
	// 主键
	private static $__pk = '';
	// 所有字段名
	private static $__fields = array();
	// 初始化
	const STATUS_NORMAL = 1;
	// 已验证完毕
	const STATUS_UPDATE = 2;
	// 已删除
	const STATUS_REMOVE = 3;

	// 获取所有列表
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {

		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `status`<'%d'
			ORDER BY `su_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	// 根据主键值读取数据
	public static function fetch($id, $shard_key = array()) {

		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `su_id`='%d' AND `status`<'%d'", array(
				self::$__table, $id, self::STATUS_REMOVE
			), $shard_key
		);
	}

	public static function fetch_by_pks($ids, $shard_key = array()) {

		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `su_id` IN (%n) AND `status`<'%d'
			ORDER BY `su_id` DESC", array(
				self::$__table, $ids, self::STATUS_REMOVE
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

		if (empty($data['status'])) {
			$data['status'] = self::STATUS_NORMAL;
		}

		if (empty($data['created'])) {
			$data['created'] = startup_env::get('timestamp');
		}

		if (empty($data['updated'])) {
			$data['updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}

	// 更新
	public static function update($data, $condition, $unbuffered = false, $low_priority = false, $shard_key = array()) {

		if (empty($data['updated'])) {
			$data['updated'] = startup_env::get('timestamp');
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
	public static function delete_by_pks($ids, $unbuffered = false, $shard_key = array()) {

		return parent::_update(self::$__table, array(
			'status' => self::STATUS_REMOVE,
			'deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $ids), $unbuffered, false, $shard_key);
	}

}
