<?php
/**
 * 微信配置表
 * $Author$
 * $Id$
 */

class voa_d_oa_weixin_setting extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.weixin_setting';
	/** 主键 */
	private static $__pk = 'ws_key';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 数据类型 */
	const TYPE_ARRAY = 1;
	const TYPE_NORMAL = 0;

	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ws_status`<'%d'
			ORDER BY `ws_key` ASC", array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 插入新的数据 */
	public static function insert($data, $shard_key = array()) {
		if (empty($data['ws_status'])) {
			$data['ws_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['ws_created'])) {
			$data['ws_created'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, false, true, false, $shard_key);
	}

	/** 更新数据 */
	public static function update($data, $condition, $shard_key = array()) {
		if (empty($data['ws_status'])) {
			$data['ws_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['ws_updated'])) {
			$data['ws_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition, false, false, $shard_key);
	}

	/** 删除数据 */
	public static function delete($cachenames, $shard_key = array()) {
		return self::update(array(
			'ws_key' => $cachenames,
			'ws_status' => self::STATUS_REMOVE,
			'ws_deleted' => startup_env::get('timestamp')
		), db_help::field('ws_key', $cachenames), $shard_key);
	}

	/**
	 * 更新多个变量值
	 * @param array $data array(key=>value, key2=>value2, ...)
	 * @return boolean
	 */
	public static function update_setting($data, $shard_key = array()) {
		$prefix = 'ws_';
		/** 确定需要进行更新还是插入 */
		$is_update_keys = array();
		$tmp = parent::_fetch_all(self::$__table, "SELECT `{$prefix}key` FROM %t WHERE %i", array(
			self::$__table, db_help::field($prefix.'key', array_keys($data)), db_help::field($prefix.'status', self::STATUS_REMOVE, '<')
		), self::$__pk, $shard_key);
		foreach ($tmp AS $row) {
			$is_update_keys[$row[$prefix.'key']] = $row[$prefix.'key'];
		}

		foreach ($data as $key => $value) {
			$data = array(
				$prefix.'value' => $value,
				$prefix.'status' => self::STATUS_UPDATE,
				$prefix.'updated' => startup_env::get('timestamp')
			);
			if (isset($is_update_keys[$key])) {
				parent::_update(self::$__table, $data, array($prefix.'key' => $key), false, false, $shard_key);
			} else {
				$data[$prefix.'key'] = $key;
				$data[$prefix.'type'] = @unserialize($value) === false ? 0 : 1;
				$data[$prefix.'status'] = self::STATUS_NORMAL;
				$data[$prefix.'created'] = startup_env::get('timestamp');
				parent::_insert(self::$__table, $data, false, false, false, $shard_key);
			}
		}

		return true;
	}

}
