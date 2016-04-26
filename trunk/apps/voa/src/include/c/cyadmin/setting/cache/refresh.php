<?php
/**
 * voa_c_cyadmin_setting_cache_refresh
 * 主站后台/系统设置/更新缓存
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_setting_cache_refresh extends voa_c_cyadmin_setting_base {

	public function execute() {

		// 所有表缓存
		$caches = array(
			'common_setting', 'common_cpmenu', 'domain_applist', 'adminer'
		);

		// 遍历, 更新所有
		foreach ($caches as $c) {
			if ('common_setting' == $c) {
				// 更新系统设置
				voa_h_cache::get_instance()->get('setting', 'cyadmin', true);
			} elseif ('common_cpmenu' == $c) {
				// 更新后台菜单
				voa_h_cache::get_instance()->get('cpmenu', 'cyadmin', true);
			} elseif('_setting' == substr($c, -8)) {
				// 更新指定表设置
				voa_h_cache::get_instance()->get(str_replace('_', '.', $c), 'cyadmin', true);
			}
			if (substr($_SERVER['HTTP_HOST'], -3) != 'net') { // 本地调试的时候 没有RPC 就不会报错
				if ('domain_applist' == $c) {
					// 删除应用列表缓存
					voa_h_cache::get_instance()->remove('domain_applist', 'cyadmin');
				} elseif ('adminer' == $c) {
					// 删除管理員缓存
					voa_h_cache::get_instance()->remove('adminer', 'cyadmin');
				}
			}
		}

		$this->message('success', '缓存更新操作完毕', false, false);

	}

}
