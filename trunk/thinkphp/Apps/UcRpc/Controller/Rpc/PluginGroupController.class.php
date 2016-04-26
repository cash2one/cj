<?php
/**
 * PluginGroupController.class.php
 * $author$
 */

namespace UcRpc\Controller\Rpc;

class PluginGroupController extends AbstractController {

	/**
	 * 更新套件信息
	 * @param $cpg_ids 套件ID
	 * @param $ep_data 更新数据
	 * @return bool
	 */
	public function update_cpg($cpg_ids, $ep_data) {

		$serv = D('Common/CommonPluginGroup', 'Service');
		$serv->update_cpg($cpg_ids, $ep_data);

		return true;
	}



}
