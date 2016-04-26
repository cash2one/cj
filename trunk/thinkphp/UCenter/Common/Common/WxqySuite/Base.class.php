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

	// 重定向
	protected function _redirect() {

		$xml = (string)file_get_contents("php://input");
		// 取 corpid
		$corpid = I('get.corpid');
		// 读取记录企业列表
		$url = cfg('CYADMIN_RPC_HOST') . '/OaRpc/Rpc/Suite';
		$list = array();
		if (!\Com\Rpc::query($list, $url, 'list_by_corpid', $corpid) || empty($list)) {
			Log::record(var_export($_GET, true)."\n".$xml);
			return false;
		}

		$ep = reset($list);
		// 记录调试日志
		Log::record(var_export($_GET, true)."\n".$xml, Log::DEBUG);
		// 消息转发 url
		$url = cfg('PROTOCAL').$ep['ep_domain'].'/qywx.php?'.http_build_query(I('get.'));

		exit($result);
	}

	/**
	 * 读取 OA suite 记录
	 * @param array $sutie 套件信息
	 * @param string $suiteid 套件id
	 * @param boolean $force 是否强制读取
	 * @return boolean
	 */
	protected function _get_oa_suite(&$suite, $suiteid, $force = false) {

		\Think\Log::record('Ucenter._get_oa_suite');
		static $suites = array();
		// 如果套件信息存在
		if (isset($suites[$suiteid]) && !$force) {
			$sutie = $suites[$suiteid];
			return true;
		}

		// 通过 rpc, 获取套件信息
		$url = cfg('OA_RPC_HOST') . '/UcRpc/Rpc/Suite';
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
	 * @param array $data 套件信息
	 * @param string $suiteid 套件ID
	 * @return boolean
	 */
	protected function _update_oa_suite($suite, $suiteid) {

		\Think\Log::record('Ucenter._update_oa_suite');
		// 通过 rpc, 获取套件信息
		$url = cfg('OA_RPC_HOST') . '/UcRpc/Rpc/Suite';
		if (!\Com\Rpc::query($suite, $url, 'update_by_suiteid', $suite, $suiteid)) {
			$suite = array();
			return false;
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
	 * @param array $suite 待更新套件数据
	 * @param string $suiteid 套件ID
	 * @return boolean
	 */
	protected function _update_uc_suite($suite, $suiteid) {

		// 获取 suite
		$uc_suite = array();
		if (!$this->get_uc_suite($uc_suite, $suiteid)) {
			return false;
		}

		// 编辑 suite
		$serv_suite = D('Common/Suite', 'Service');
		if ($uc_suite) { // 如果套件信息存在
			$serv_suite->update_by_suiteid($suite, $suiteid);
		} else { // 第一次更新
			// 套件信息入库
			$suite['suiteid'] = $suiteid;
			$serv_suite->insert($suite);
		}

		return true;
	}

}
