<?php
/**
 * SuiteController.class.php
 * $author$
 */

namespace OaRpc\Controller\Rpc;
use Common\Common\Cache;

class SuiteController extends AbstractController {

	/**
	 * 套件安装
	 * @param array $params 用户提交的参数
	 * @return boolean
	 */
	public function install($params) {

		$serv_ep = D('Common/Enterprise', 'Service');
		if (!$serv_ep->check_install_params($params)) {
			E($serv_ep->get_errcode() . ':' . $serv_ep->get_errmsg());
			return false;
		}

		// 启动注册过程
		$enterprise = array();
		$params = array(
			'realname' => $params['realname'],
			'mobilephone' => $params['mobilephone'],
			'password' => $params['password'],
			'email' => $params['email'],
			'ename' => $params['ename'],
			'enumber' => $params['enumber'],
			'password' => $params['password'],
			'industry' => $params['industry'],
			'companysize' => $params['companysize'],
			'smsauth' => rbase64_encode($params['mobilephone'])
		);
		if (!$serv_ep->open($enterprise, $params)) {
			E($serv_ep->get_errcode() . ':' . $serv_ep->get_errmsg());
			return false;
		}

		return true;
	}

	/**
	 * 根据 $suiteid 读取套件信息
	 * @param string $suiteid 套件ID
	 */
	public function get_by_suiteid($suiteid) {

		// 如果参数是一个非标量的值
		if (!is_scalar($suiteid)) {
			return false;
		}

		$suiteid = (string)$suiteid;
		$serv_suite = D('Common/Suite', 'Service');
		return $serv_suite->get_by_suiteid($suiteid);
	}

	/**
	 * 更新套件信息
	 * @param array $suite 套件信息
	 * @param string $suiteid 套件ID
	 * @param boolean $force true: 强制更新; false: 先判断记录是否存在
	 * @return multitype:
	 */
	public function update_by_suiteid($suite, $suiteid, $force = true) {

		// 强制更新或者套件id信息已存在
		if ($force || $sutie = $this->get_by_suiteid($suiteid)) {
			// 更新数据
			$serv_suite = D('Common/Suite', 'Service');
			return $serv_suite->update_by_suiteid($suite, $suiteid);
		}

		// 新增
		$suite['su_suite_id'] = $suiteid;
		return $this->add($suite);
	}

	/**
	 * 新增套件信息
	 * @param array $suite 套件信息
	 * @param boolean $force true: 强制插入; false: 先判断记录是否存在
	 */
	public function add($suite, $force = true) {

		// 强制插入或者套件信息不存在
		if ($force || !$sutie = $this->get_by_suiteid($suite['su_suite_id'])) {
			$serv_suite = D('Common/Suite', 'Service');
			return $serv_suite->insert($suite);
		}

		// 更新
		return $this->update_by_suiteid($suite, $suite['su_suite_id']);
	}

	/**
	 * 获取套件令牌
	 * @param string $suiteid 套件令牌
	 * @return boolean
	 */
	public function get_suite_token($suiteid) {

		// 从缓存读取套件信息
		$suite = Cache::instance()->get("Common.{$suiteid}");
		if (empty($suite)) {
			E('_ERR_SUITE_IS_NOT_EXIST');
			return false;
		}

		// 读取套件 token
		$serv_suite = \Common\Common\WxqySuite\Service::instance();
		$serv_suite->get_suite_token($suiteid);
		$suite = array();
		if (!$serv_suite->get_uc_suite($suite, $suiteid, true)) {
			E('_ERR_SUITE_IS_NOT_EXIST');
			return false;
		}

		// 更新套件缓存
		Cache::instance()->set("Common.{$suiteid}", $suite);
		return $suite;
	}
}
