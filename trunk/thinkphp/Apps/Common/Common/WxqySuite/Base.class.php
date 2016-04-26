<?php
/**
 * 企业号套件接口基类
 * Base.php
 * $author$
 */

namespace Common\Common\WxqySuite;
use Think\Log;
use Common\Common\Cache;

abstract class Base extends \Com\WxqySuite {

	public function __construct() {

		parent::__construct();
	}

	/**
	 * 读取 OA suite 记录
	 * @param array $sutie 套件信息
	 * @param string $suiteid 套件id
	 * @param boolean $force 是否强制读取
	 * @return boolean
	 */
	protected function _get_oa_suite(&$suite, $suiteid, $force = false) {

		static $suites = array();
		// 如果套件信息存在
		if (isset($suites[$suiteid]) && !$force) {
			$suite = $suites[$suiteid];
			return true;
		}

		// 初始化 suite
		$serv_suite = D('Common/Suite', 'Service');
		$suite = $serv_suite->get_by_suiteid($suiteid);
		$suites[$suiteid] = $suite;

		return true;
	}

	/**
	 * 更新套件信息
	 * @param array $data 套件信息
	 * @param string $suiteid 套件ID
	 * @return boolean
	 */
	protected function _update_oa_suite($data, $suiteid) {

		// 获取 suite
		$oa_suite = array();
		if (!$this->_get_oa_suite($oa_suite, $suiteid)) {
			return false;
		}

		// 编辑 suite
		$serv_suite = D('Common/Suite', 'Service');
		if ($oa_suite) { // 如果套件信息存在
			$serv_suite->update_by_suiteid($suiteid, $data);
		} else { // 第一次更新
			// 为了兼容之前未安装套件的企业号, 需要初始化插件表
			$plugin = array('cp_agentid' => '', 'cp_suiteid' => '', 'cp_available' => 0);
			$serv_p = D('Common/Plugin', 'Service');
			$serv_p->update($plugin, array('cp_available<?' => 255, 'cp_suiteid=?' => ''));

			// 套件信息入库
			$data['suiteid'] = $suiteid;
			$serv_suite->insert($data);
		}

		return true;
	}

	/**
	 * 根据 suite_id 读取 suite 记录
	 *
	 * @param array $suite 套件信息
	 * @param string $suiteid 套件id
	 * @param boolean $force 是否强制读取
	 * @return boolean
	 */
	public function get_uc_suite(&$suite, $suiteid, $force = false) {

		static $suties;

		// 如果传入了套件信息, 则默认为更新操作
		if (!empty($suite) && !empty($suite['su_suite_id']) && $suiteid == $suite['su_suite_id']) {
			$suites[$suiteid] = $suite;
			return true;
		}

		// 如果套件信息存在
		if (isset($suites[$suiteid]) && !$force) {
			$sutie = $suites[$suiteid];
			return true;
		}

		// 通过 rpc, 获取套件信息
		$url = cfg('UCENTER_RPC_HOST').'/OaRpc/Rpc/Suite';
		$suite = array();
		if (!\Com\Rpc::query($suite, $url, 'get_by_suiteid', $suiteid)) {
			$suite = array();
			return false;
		}

		// 推入缓存
		$suites[$suiteid] = $suite;

		return true;
	}

	/**
	 * 更新套件信息
	 * @param array $suite 待更新套件数据
	 * @param string $suiteid 套件ID
	 * @return boolean
	 */
	protected function _update_uc_suite($suite, $suiteid) {

		// 通过 rpc, 获取套件信息
		$url = cfg('UCENTER_RPC_HOST').'/OaRpc/Rpc/Suite';
		$suite = array();
		if (!\Com\Rpc::query($suite, $url, 'update_by_suiteid', $suite, $suiteid)) {
			return false;
		}

		// 重新获取套件信息
		$this->get_uc_suite($suite, $suiteid, true);
		return true;
	}

	/**
	 * 获取套件令牌
	 * @param string $suiteid 套件令牌
	 * @return boolean
	 */
	public function get_suite_token($suiteid) {

		try {
			// 读取 uc 的缓存
			$options = array(
				'prefix' => 'uc.',
				'auto_create' => false
			);
			$suite = Cache::instance()->get("Common.{$suiteid}", '', $options);

			// 重新取套件信息
			if (empty($suite) || !$suite || $suite['su_access_token_expires'] - 600 < NOW_TIME) {
				$suite = array();
				$url = cfg('UCENTER_RPC_HOST').'/OaRpc/Rpc/Suite';
				if (!\Com\Rpc::query($suite, $url, 'get_suite_token', $suiteid)) {
					\Think\Log::record(var_export($suite, true));
					E('_ERR_SUITE_TOKEN_ERROR');
					return false;
				}
			}
		} catch (\Exception $e) {
			\Think\Log::record($e);
			return false;
		}

		// 如果返回的数据错误
		if (empty($suite['su_suite_id']) || $suiteid != $suite['su_suite_id']) {
			return false;
		}

		$this->_suite_access_token = $suite['su_suite_access_token'];
		$this->_suite_access_token_expires = $suite['su_access_token_expires'];
		return true;
	}

}
