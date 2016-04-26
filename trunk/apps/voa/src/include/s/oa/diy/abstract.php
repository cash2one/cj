<?php
/**
 * voa_s_oa_diy_abstract
 * 产品服务基类
 *
 * $Author$
 * $Id$
 */

class voa_s_oa_diy_abstract extends voa_s_abstract {


	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 检查表格是否存在
	 * @param int $tid 表格id
	 * @return boolean
	 */
	public function chk_tid($tid, $err = null) {

		// 取表格缓存
		$tables = voa_h_cache::get_instance()->get('diytable', 'oa');
		// 如果表格不存在
		if (!array_key_exists($tid, $tables['tid2tunique'])) {
			throw new Exception(voa_errcode_oa_diy::TABLE_IS_NOT_EXIST);
			return false;
		}

		return true;
	}

	/**
	 * 检查字段类型是否存在
	 * @param string $type 类型标识
	 * @param string $err
	 * @return boolean
	 */
	public function chk_ct_type($type, $err = null) {

		// 取字段类型配置
		$columntypes = voa_h_cache::get_instance()->get('columntype', 'oa');
		// 如果字段类型信息不存在
		if (!array_key_exists($type, $columntypes)) {
			throw new Exception(voa_errcode_oa_diy::CT_TYPE_IS_NOT_EXIST);
			return false;
		}

		return true;
	}

}
