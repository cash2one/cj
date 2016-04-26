<?php
/**
 * installed.php
 * 计算已安装过的应用数
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_frontend_common_plugin_installed extends voa_uda_frontend_common_plugin_abstract {

	/**
	 * 计算已安装过的应用数
	 * @param array $in 无额外请求参数
	 * @param array $out
	 * + count 已安装过应用个数
	 * + installed 是否安装过
	 * @return boolean
	 */
	public function doit($in = array(), &$out = array()) {

		$serv = &service::factory('voa_s_oa_common_plugin');
		$count = $serv->installed_count();
		$out = array(
			'count' => $count,
			'installed' => $count ? 1 : 0
		);

		return true;
	}

}
