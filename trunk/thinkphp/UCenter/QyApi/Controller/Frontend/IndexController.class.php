<?php
/**
 * IndexController.class.php
 * $author$
 */

namespace QyApi\Controller\Frontend;

class IndexController extends AbstractController {

	public function Index() {

		$this->show('[IndexController->Index]');
		$this->_output("Frontend/Index/Index");
	}

	// 主页型应用入口
	public function App() {

		$corpid = I('get.corpid');
		$pluginid = I('get.pluginid');
		// 读取企业信息
		$serv_profile = D('Common/EnterpriseProfile', 'Service');
		if (!$profile = $serv_profile->get_by_corpid($corpid)) {
			E('corpid is error, please contact adminer.');
			return false;
		}

		// 读取企业域名
		$serv_enterprise = D('Common/Enterprise', 'Service');
		if (!$enterprise = $serv_enterprise->get($profile['ep_id'])) {
			E('enterprise profile is not exist.');
			return false;
		}

		// 目录
		$dir = I('get.dir', '', 'trim');

		// 转向主页应用
		$url = cfg('PROTOCAL') . $enterprise['ep_domain'] . $dir . '?pluginid=' . $pluginid;

		redirect($url);
		return true;
	}

}
