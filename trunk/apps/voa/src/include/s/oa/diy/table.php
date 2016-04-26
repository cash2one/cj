<?php
/**
 * voa_s_oa_diy_table
 * 产品数据表
 *
 * $Author$
 * $Id$
 */

class voa_s_oa_diy_table extends voa_s_abstract {


	/**
	 * __construct
	 *
	 * @return void
	 */
	public function __construct() {

		parent::__construct();
	}

	/**
	 * 检查插件的唯一标识
	 * @param string $identifier 插件的唯一标识
	 * @param string $err 错误提示
	 * @return boolean
	 */
	public function chk_cp_identifier($identifier, $err = null) {

		return true;
		// 如果插件唯一标识为空
		if (empty($identifier)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::CP_IDENTIFIER_IS_EMPTY);
			return false;
		}

		// 判断插件为标识是否存在
		$plugins = voa_h_cache::get_instance()->get('plugin', 'oa');
		$is_exists = false;
		foreach ($plugins as $_p) {
			if ($identifier == $_p['cp_identifier']) {
				$is_exists = true;
				break;
			}
		}

		// 如果该插件标识不存在
		if (false == $is_exists) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::CP_IDENTIFIER_IS_NOT_EXIST);
			return false;
		}

		return true;
	}

	/**
	 * 检查表格唯一标识
	 * @param string $unique 表格唯一标识
	 * @param string $err
	 * @return boolean
	 */
	public function chk_tunique($unique, $err = null) {

		// 如果表格唯一标识为空
		if (empty($unique)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::TUNIQUE_IS_EMPTY);
			return false;
		}

		// 判断唯一标识是否存在
		$tables = voa_h_cache::get_instance()->get('diytable', 'oa');
		if (array_key_exists($unique, $tables)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::TUNIQUE_DUPLICATE);
			return false;
		}

		return true;
	}

	/**
	 * 检查表格名称
	 * @param string $name 表格名称
	 * @param string $err 错误信息
	 * @return boolean
	 */
	public function chk_tname($name, $err = null) {

		// 如果表名为空
		if (empty($name)) {
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::TABLENAME_IS_EMPTY);
			return false;
		}

		return true;
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
			voa_h_func::throw_errmsg(voa_errcode_oa_diy::TABLE_IS_NOT_EXIST);
			return false;
		}

		return true;
	}

}
