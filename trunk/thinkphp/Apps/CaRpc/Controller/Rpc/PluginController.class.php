<?php
/**
 * PluginController.class.php
 * $author$
 */

namespace CaRpc\Controller\Rpc;

class PluginController extends AbstractController {

	// 获取插件列表
	public function list_all() {

		// 获取插件列表
		$cache = &\Common\Common\Cache::instance();
		$plugins = $cache->get('Common.plugin');

		return $plugins;
	}

}
