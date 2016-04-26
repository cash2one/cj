<?php
/**
 * voa_d_uc_common_syscache
 * 系统缓存数据表
 *
 * $Author$
 * $Id$
 */

class voa_d_uc_common_syscache extends dao_mysql {
	/** 表名 */
	public static $__table = 'uc.common_syscache';
	/** 主键 */
	private static $__pk = 'csc_name';
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/** 数组 */
	const TYPE_ARRAY = 1;
	const TYPE_NORMAL = 0;

	/**
	 * 根据缓存名称读取缓存
	 * @param string $param 缓存名
	 */
	public static function fetch($name, $shard_key = array()) {
		$data = self::fetch_by_names(array($name), $shard_key);
		return isset($data[$name]) ? $data[$name] : false;
	}

	/**
	 * 根据缓存名称读取
	 * @param array $names 缓存名数组
	 */
	public static function fetch_by_names($names, $shard_key = array()) {
		return parent::_fetch_all(self::$__table,
			"SELECT * FROM %t WHERE csc_name IN (%n) AND csc_status<'%d'",
			array(self::$__table, $names, self::STATUS_REMOVE), self::$__pk, $shard_key
		);
	}

	/**
	 * 数据入库
	 * @param array $data 入库数据数组
	 */
	public static function insert($data, $return_insert_id = false, $replace = false, $shard_key = array()) {
		if (empty($data['csc_status'])) {
			$data['csc_status'] = self::STATUS_NORMAL;
		}

		if (empty($data['csc_created'])) {
			$data['csc_created'] = startup_env::get('timestamp');
		}

		if (empty($data['csc_updated'])) {
			$data['csc_updated'] = $data['csc_created'];
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, false, $shard_key);
	}

	/**
	 * 更新
	 * @param array $data 需要更新的数据
	 * @param array|string $conditions 更新条件
	 */
	public static function update($data, $conditions, $shard_key = array()) {
		if (empty($data['csc_status'])) {
			$data['csc_status'] = self::STATUS_UPDATE;
		}

		if (empty($data['csc_updated'])) {
			$data['csc_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $conditions, false, false, $shard_key);
	}

	/**
	 * 根据条件删除
	 * @param array|string $conditions 删除条件
	 */
	public static function delete($conditions, $shard_key = array()) {
		return self::update(array(
			'csc_status' => self::STATUS_REMOVE,
			'csc_deleted' => startup_env::get('timestamp')
		), $conditions, $shard_key);
	}
}
