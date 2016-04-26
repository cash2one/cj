<?php
/**
 * Class voa_d_oa_common_signature
 * 新东方登录签名信息
 * @create-time: 2015-06-17
 * @author: huanw
 * @email: wanghuan@vchangyi.com
 */

class voa_d_oa_common_signature extends dao_mysql {

	/** 表名 */
	public static $__table = 'oa.common_signature';
	/** 主键 */
	private static $__pk = 'sig_id';
	/** 所有字段名 */
	private static $__fields = array(
		'sig_id', 'sig_m_uid', 'sig_code', 'sig_login_status', 'sig_login_time',
		'sig_status', 'sig_created', 'sig_updated', 'sig_deleted'
	);

	/** 已登录 */
	const STATUS_LOGIN = 1;
	/** 未登录 */
	const STATUS_LOGOUT = 0;

	/**
	 * 签名数据入库
	 * @param $data
	 * @param bool $return_insert_id
	 * @return mixed
	 * @throws dao_exception
	 */
	public static function insert($data, $return_insert_id = false) {

		//创建时间
		$create_time = startup_env::get('timestamp');

		if (empty($data['sig_created'])) {
			$data['sig_created'] = $create_time;
		}

		//数据入库
		return parent::_insert(self::$__table, $data, $return_insert_id);
	}

	/**
	 * 根据签名获取签名信息
	 * @param $code 签名字串
	 * @return bool
	 * @throws dao_exception
	 */
	public static function fetch_by_code($code) {

		return parent::_fetch_first(self::$__table, "SELECT * FROM %t
			WHERE `sig_code`= %c
			ORDER BY `sig_id` DESC", array(
			self::$__table, $code
		));
	}

	/**
	 * 根据条件更新签名信息
	 * @param $data 待更新签名信息
	 * @param $condition 更新条件
	 * @throws dao_exception
	 */
	public static function update($data, $condition) {

		if (empty($data['sig_status'])) {
			$data['sig_status'] = 2;
		}

		if (empty($data['sig_updated'])) {
			$data['sig_updated'] = startup_env::get('timestamp');
		}

		return parent::_update(self::$__table, $data, $condition);
	}

	/**
	 * 根据条件删除登录记录
	 * @param $conditions 删除条件
	 * @param bool $unbuffered
	 * @param array $shard_key
	 * @throws dao_exception
	 */
	public static function delete_by_conditions($conditions) {

		return parent::_delete(self::$__table, $conditions);
	}

}
