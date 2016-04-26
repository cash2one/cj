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

	/**
	 * 更新套件信息
	 * @param $cpg_id 套件ID
	 * @param $data 更新信息
	 * @return bool
	 */
	public function update_cpg($cpg_id, $data) {

		$serv = D('Common/CommonPluginGroup', 'Service');
		$serv->update_cpg($cpg_id, $data);

		clear_cache();

		return true;
	}

}
