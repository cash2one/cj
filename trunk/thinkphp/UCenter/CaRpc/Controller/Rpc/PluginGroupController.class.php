<?php
/**
 * PluginGroupController.class.php
 * $author$
 */

namespace CaRpc\Controller\Rpc;

class PluginGroupController extends AbstractController {

	// 获取插件分组列表
	public function list_all() {

		// 获取插件分组列表
		$serv_group = D('Common/CommonPluginGroup', 'Service');
		$groups = $serv_group->list_all();

		foreach ($groups as $k => &$v) {
			if ($v['cpg_id'] == 6 && $v['cpg_name'] == '销售管理') {
				$v['cpg_name'] = '新销售管理';
			}
		}

		return $groups;
	}

}
