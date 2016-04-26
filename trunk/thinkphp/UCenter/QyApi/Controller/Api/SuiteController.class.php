<?php
/**
 * SuiteController.class.php
 * $author$
 */

namespace QyApi\Controller\Api;
use Com\ApiSig;

class SuiteController extends AbstractController {

	// 套件 ticket 消息
	const SUITE_TICKET = 'suite_ticket';
	// 套件授权变更消息
	const CHANGE_AUTH = 'change_auth';
	// 套件授权取消
	const CANCEL_AUTH = 'cancel_auth';

	// 套件消息处理入口
	public function Index() {

		// 判断是啥消息
		switch ($this->_serv_suite->info_type) {
			case self::SUITE_TICKET: // 套件 ticket 消息
				$this->_suite_ticket();
				break;
			case self::CHANGE_AUTH: // 授权变更消息
			case self::CANCEL_AUTH: // 授权取消
				$this->_auth();
				break;
			default:break;
		}

		return $this->_response();
	}

	// 套件 ticket 消息
	protected function _suite_ticket() {

		try {
			// 所有参数
			$args = $this->_serv_suite->recv();
			$suiteid = $args['suite_id'];
			// 更新 ticket
			$serv = D('Common/Suite', 'Service');
			$serv->update_by_suiteid($suiteid, array('su_ticket' => $args['suite_ticket']));

			// 获取套件信息
			$serv_suite = \Common\Common\WxqySuite\Service::instance();
			$suite = array();
			if ($serv_suite->get_uc_suite($suite, $suiteid, true)) {
				if (!empty($suite['su_suite_access_token']) && NOW_TIME + cfg('SUITE_TOKEN_EXPIRE_AHEAD') > $suite['su_access_token_expires']) {
					// 更新套件 token 信息
					$serv_suite->get_suite_token($suiteid);
					// 重新更新缓存
					$suite = $serv->get_by_suiteid($suiteid);
					\Common\Common\Cache::instance()->set($suiteid, $suite);
				}
			}
		} catch (\Exception $e) {
			\Think\Log::record(var_export($e, true));
			return false;
		}

		return true;
	}

	// 套件授权取消/更新操作
	protected function _auth() {

		// 所有参数
		$args = $this->_serv_suite->recv();
		$corpid = $args['auth_corp_id'];

		// 读取记录企业列表
		$url = cfg('CYADMIN_RPC_HOST') . '/OaRpc/Rpc/Suite';
		$list = array();
		if (!\Com\Rpc::query($list, $url, 'list_by_corpid', $corpid) || empty($list)) {
			\Think\Log::record(L('_ERR_ENTERPRISE_NOT_EXISTS', array('corpid' => $corpid)));
			return false;
		}

		// 取第一个
		$enterprise = reset($list);
		// 生成签名
		$api_sig = &ApiSig::instance();
		$sig = $api_sig->create($args, NOW_TIME);

		// 获取请求协议
		$protocal = cfg('PROTOCAL');
		// api url
		$url = sprintf('%s%s/api/wxqysuite/post/auth?sig=%s&ts=%d', $protocal, $enterprise['ep_domain'], $sig, NOW_TIME);

		// 发送请求
		$data = array();
		if (!rfopen($data, $url, $args)) {
			\Think\Log::record('url:' . $url . '=>' . var_export($args, true));
		}

		return true;
	}

}
