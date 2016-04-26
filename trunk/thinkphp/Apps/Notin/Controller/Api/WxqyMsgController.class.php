<?php
/**
 * WxqyMsgController.class.php
 * $author$
 */

namespace Notin\Controller\Api;
use Common\Common\WxqyMsg;

class WxqyMsgController extends AbstractController {

	public function before_action($action = '') {

		$this->_require_login = false;
		return parent::before_action($action);
	}

	public function Index() {

		return true;
	}

	// 消息发送
	public function ZxSend() {

		if ('citicdc.vchangyi.com' != I('server.HTTP_HOST')) {
			E('_ERR_DEFAULT');
			return false;
		}

		$msg = I('get.msg');
		$cdids = I('get.cdids');
		$agentid = I('get.agentid');
		$cdids = explode(',', $cdids);

		$serv_cd = D('Common/CommonDepartment', 'Service');
		$dps = $serv_cd->list_by_pks($cdids);

		$serv_plugin = D('Common/CommonPlugin', 'Service');
		$plugins = $serv_plugin->list_by_conds($agentid);

		if (empty($msg)) {
			E('_ERR_MSG_EMPTY');
			return false;
		}

		if (empty($cdids)) {
			E('_ERR_CDID_EMPTY');
			return false;
		}

		if (empty($dps)) {
			E('_ERR_DEPARTMENT_IS_NOT_EXIST');
			return false;
		}

		if (empty($agentid)) {
			E('_ERR_AGENTID_EMPTY');
			return false;
		}

		if (empty($plugins)) {
			E('_ERR_PLUGIN_IS_NOT_EXIST');
			return false;
		}

		$uids = array();
		WxqyMsg::instance()->send_text($msg, $uids, $cdids, $agentid);
		return true;
	}

}
