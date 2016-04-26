<?php
/**
 * voa_uda_frontend_transaction_abstract
 * 统一数据访问/事务
 *
 * $Author$
 * $Id$
 */

class voa_uda_frontend_transaction_abstract extends voa_uda_frontend_base {
	// service
	protected static $_s_service = null;

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	// 开始事务
	public static function s_begin() {

		// 如果 service 未初始化
		if (null === self::$_s_service) {
			self::$_s_service = new voa_s_oa_diy_table();
		}

		// 事务开始
		self::$_s_service->begin();
	}

	// 提交
	public static function s_commit() {

		self::$_s_service->commit();
	}

	// 回滚
	public static function s_rollback() {

		self::$_s_service->rollback();
	}

}
