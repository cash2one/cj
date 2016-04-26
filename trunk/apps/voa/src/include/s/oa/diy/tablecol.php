<?php
/**
 * voa_s_oa_diy_tablecol
 * 产品属性
 *
 * $Author$
 * $Id$
 */

class voa_s_oa_diy_tablecol extends voa_s_abstract {


	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 检查正则格式
	 * @param string $regexp 是否必填
	 * @param string $err 错误信息
	 * @return boolean
	 */
	public function chk_reg_exp($regexp, $err = null) {

		if (!empty($regexp) && preg_match("/\/(.*?)\/\w*/", $subject)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::TABLECOL_REGEXP_ERR);
			return false;
		}

		return true;
	}

	/**
	 * 检查字段名称
	 * @param string $fname 字段名称
	 * @param string $err
	 * @return boolean
	 */
	public function chk_fieldname($fname, $err = null) {

		// 如果字段名称为空
		if (empty($fname)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::FIELDNAME_IS_EMPTY);
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

		// 如果字段类型信息不存在
		$columntypes = voa_h_cache::get_instance()->get('columntype', 'oa');
		if (!array_key_exists($type, $columntypes)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::CT_TYPE_IS_NOT_EXIST);
			return false;
		}

		return true;
	}

}
