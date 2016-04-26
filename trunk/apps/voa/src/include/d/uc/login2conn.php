<?php
/**
 * voa_d_uc_login2conn
 * UC/数据库关联登录表
 * $Author$
 * $Id$
 */
class voa_d_uc_login2conn extends dao_mysql {
	/** 表名 */
	public static $__table = 'uc.login_conn';
	/** 主键 */
	private static $__pk = 'conn_id';
	/** 字段前缀 */
	private static $__prefix = 'conn_';
	/** 所有字段名 */
	private static $__fields = array(
		'conn_id', 'conn_corpid', 'conn_userid', 'conn_mobilephone',
		'conn_status', 'conn_created', 'conn_updated', 'conn_deleted'
	);
	/** 正常 */
	const STATUS_NORMAL = 1;
	/** 已更新过 */
	const STATUS_UPDATE = 2;
	/** 已删除 */
	const STATUS_REMOVE = 3;

	/**********************/

	/** 获取所有列表 */
	public static function fetch_all($start = 0, $limit = 0, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `dp_status`<'%d'
			ORDER BY `dp_id` DESC".db_help::limit($start, $limit), array(
				self::$__table, self::STATUS_REMOVE
			), self::$__pk, $shard_key
		);
	}
	
	/**
	 * 根据 a 记录读列表
	 * @param string $a a记录
	 */
	public static function fetch_by_a($a, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t WHERE dp_data=%s AND dp_status<%d", array(
				self::$__table, $a, self::STATUS_REMOVE
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
		if (empty($data['dp_status'])) {
			$data['dp_status'] = self::STATUS_BANDED;
		}

		if (empty($data['dp_created'])) {
			$data['dp_created'] = startup_env::get('timestamp');
		}

		if (empty($data['dp_updated'])) {
			$data['dp_updated'] = startup_env::get('timestamp');
		}

		return parent::_insert(self::$__table, $data, $return_insert_id, $replace, $silent, $shard_key);
	}
	
	
}
