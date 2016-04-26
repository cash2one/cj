<?php
/**
 * 审批模板表
 * $Author$
 * $Id$
 */

class voa_d_oa_askfor_customcols extends dao_mysql {
	/** 表名 */
	public static $__table = 'oa.askfor_customcols';
	/** 主键 */
	private static $__pk = 'afcc_id';
	/** 正常状态 */
	const STATUS_NORMAL = 1;
	/** 已更新 */
	const STATUS_UPDATE = 2;
	/** 删除 */
	const STATUS_REMOVE = 3;


	/** 根据 模板id 读取信息 */
	public static function fetch_by_aft_id($aft_id, $shard_key = array()) {
		return parent::_fetch_all(self::$__table, "SELECT * FROM %t
			WHERE `aft_id`='%d' AND `afcc_status`<'%d'
			ORDER BY `afcc_id` ASC", array(
				self::$__table, $aft_id, self::STATUS_REMOVE
			), $shard_key
		);
	}



	/**
	 * 批量新增信息
	 * @param array $data 数据数组
	 * @param array $shard_key 分库参数
	 */
	public static function insert_multi($data, $shard_key = array()) {
		/** 附件信息入库 */
		$sql_ats = array();
		foreach ($data as $_at) {
			$sql_ats[] = "(".implode(',', array(
				$_at['aft_id'], '"'.$_at['field'].'"','"'. $_at['name'].'"', $_at['required'], $_at['type'],
				self::STATUS_NORMAL, startup_env::get('timestamp'), startup_env::get('timestamp')
			)).")";
		}

		if (empty($sql_ats)) {
			return true;
		}

		return parent::_query(self::$__table, "INSERT INTO %t(aft_id, field, name, required, type, afcc_status, afcc_created, afcc_updated) VALUES".implode(',', $sql_ats), array(self::$__table), $shard_key);
	}




	/**
	 * 根据模板ID删除信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_aft_id($aft_id, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'afcc_status' => self::STATUS_REMOVE,
			'afcc_deleted' => startup_env::get('timestamp')
		), db_help::field('aft_id', $aft_id), $unbuffered, false, $shard_key);
	}

	/**
	 * 根据模板ID删除信息
	 *
	 * @param int|array $uids 用户UID或UID数组
	 * @param boolean $unbuffered
	 * @return boolean
	 */
	public static function delete_by_aft_ids($aft_ids, $unbuffered = false, $shard_key = array()) {
		return parent::_update(self::$__table, array(
			'afcc_status' => self::STATUS_REMOVE,
			'afcc_deleted' => startup_env::get('timestamp')
		), db_help::field('aft_id', $aft_ids), $unbuffered, false, $shard_key);
	}

}
