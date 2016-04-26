<?php
/**
 * voa_upgrade_index
* $Author$
* $Id$
*/

class voa_upgrade_index {

	// 升级检查
	public static function check_upgrade() {

		$setting = voa_h_cache::get_instance()->get('setting', 'oa');
		// 读取数据库中的版本
		if (empty($setting['version'])) {
			$ver = 0;
		} else {
			$ver = (int)$setting['version'];
		}

		// 判断是否需要升级
		if ($ver >= config::get('voa.upgrade.latest_version')) {
			return true;
		}

		// 读取所有版本
		$versions = config::get('voa.upgrade.versions');
		$upgrade_vers = array();
		// 遍历所有版本
		foreach ($versions as $_ver) {
			// 如果当前版本小于该版本
			if ($ver < $_ver) {
				$upgrade_vers[] = $_ver;
			}
		}

		// 升级
		self::upgrade($upgrade_vers);
		// 更新缓存
		$uda = &uda::factory('voa_uda_frontend_base');
		$uda->update_cache();

		return true;
	}

	/**
	 * 升级对应的版本
	 * @param array $vers 待升级的版本
	 * @return boolean
	 */
	public static function upgrade($vers) {

		// 所有版本
		$vers = (array)$vers;
		// 遍历所有版本
		foreach ($vers as $_ver) {
			// 升级类名
			$class = 'voa_upgrade_ver' . $_ver;
			// 初始化
			if (!class_exists($class)) {
				logger::error('class ' . $class . ' is not exists.');
				continue;
			}

			$instance = new $class();
			// 升级
			$instance->upgrade($_ver);
			$instance->update_version();
		}

		return true;
	}

}
