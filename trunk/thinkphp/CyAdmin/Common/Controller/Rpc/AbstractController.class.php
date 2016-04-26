<?php
/**
 * AbstractController.class.php
 * $author$
 */

namespace Common\Controller\Rpc;
use Think\Controller\RpcController;
use Com\Cookie;

class AbstractController extends RpcController {

	// 允许的IP列表
	protected $_allow_ips = null;

	// 前置操作
	public function before_action($action = '') {

		// 记录请求日志
		\Think\Log::record('Rpc.action: ' . $action . "\nRpc.arguments: " . var_export($this->get_arguments(), true));
		// 检查IP权限
		$this->_check_ip_privileges();

		return true;
	}

	// 后置操作
	public function after_action($action = '') {

		return true;
	}

	// 检查IP权限
	protected function _check_ip_privileges() {

		$ips = $this->_get_allow_ips();
		$clientip = get_client_ip();
		if (!empty($ips) && !in_array($clientip, $ips)) {
			E('_ERR_IP_DENIED');
			return false;
		}

		return true;
	}

	// 获取允许访问的IP列表
	protected function _get_allow_ips() {

		// 如果已经获取了IP了
		if (null !== $this->_allow_ips) {
			return $this->_allow_ips;
		}

		$this->_allow_ips = cfg('RPC_ALLOW_IPS');
		// 如果配置允许的IP列表
		if (empty($this->_allow_ips)) {
			$this->_allow_ips = array();
			return $this->_allow_ips;
		}

		// 如果配置非数组, 则
		if (!is_array($this->_allow_ips)) {
			$this->_allow_ips = array();
		}

		return $this->_allow_ips;
	}
}
