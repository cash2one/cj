<?php
/**
 * 项目配置配置表
 * $Author$
 * $Id$
 */

class voa_d_oa_project_setting extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.project_setting';
	/** 主键 */
	private static $__pk = 'ps_key';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	public static function fetch_all($shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `ps_status`<'%d'
			ORDER BY `ps_key` ASC", array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}

	/** 插入新的数据 */
	public static function insert($data, $shard_key = array()) {
		if (empty($data['ps_status'])) {
			$data['ps_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['ps_created'])) {
			$data['ps_created'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, false, true, false, $shard_key);
	}

	/** 更新数据 */
	public static function update($data, $shard_key = array()) {
		if (empty($data['ps_status'])) {
			$data['ps_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['ps_updated'])) {
			$data['ps_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, db_help::field(self::$__pk, $data[self::$__pk]), false, false, $shard_key);
	}

	/** 删除数据 */
	public static function delete($cachenames, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'ps_status' => self::STATUS_REMOVE,
			'ps_deleted' => startup_env::get('timestamp')
		), db_help::field(self::$__pk, $cachenames), false, false, $shard_key);
	}

	/**
	 * 以变量名为键名输出所有变量信息
	 * @return array
	 */
	public static function fetch_all_setting($shard_key = array()) {
		$list = array();
		$prefix = 'ps_';
		$query = parent::_query(self::$__table, "SELECT * FROM %t WHERE %i ORDER BY `{$prefix}key` ASC", array(
			self::$__table, db_help::field($prefix.'status', self::STATUS_REMOVE, '<')
		), $shard_key);
		while ($row = parent::_fetch(self::$__table, $query)) {
			$list[$row[$prefix.'key']] = array(
				'key' => $row[$prefix.'key'],
				'value' => $row[$prefix.'value'],
				'type' => $row[$prefix.'type'],
				'comment' => $row[$prefix.'comment']
			);
		}

		return $list;
	}

	/**
	 * 更新多个变量值
	 * @param array $data array(key=>value, key2=>value2, ...)
	 * @return boolean
	 */
	public static function update_setting($data, $shard_key = array()) {
		$prefix = 'ps_';
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
