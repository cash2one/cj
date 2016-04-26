<?php
/**
 * ApiTestService.class.php
 * $author$
 */

namespace Cli\Service;
use Common\Common\Login;
use Com\Cookie;

class ApiTestService extends AbstractService {

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_d = D("Cli/Interface");
	}

	// 运行接口
	public function run() {

		// 取所有测试流程
		$serv_flow = D('Cli/InterfaceFlow');
		$flows = $serv_flow->list_not_complete();

		// 执行测试流程
		foreach ($flows as $_val) {
			$this->run_flow($_val);
			$serv_flow->update($_val['f_id'], array('f_exec' => $serv_flow->get_exec_complete()));
		}

		return true;
	}

	/**
	 * 执行指定流程
	 * @param array $flow 流程信息
	 */
	public function run_flow($flow) {

		// 读取所有待测试步骤
		$serv_step = D('Cli/InterfaceStep');
		$ifs = $serv_step->list_by_fid($flow['f_id']);

		// 执行接口
		foreach ($ifs as $_val) {
			$this->run_interface($_val);
			$serv_step->update($_val['s_id'], array('s_executed' => $serv_step->get_executed_y()));
		}

		return true;
	}

	/**
	 * 运行指定接口
	 * @param array $if 接口信息
	 * @return boolean
	 */
	public function run_interface($if) {

		// 读取参数
		$serv_if = D('Cli/InterfaceParameter');
		$params = $serv_if->list_by_nid($if['n_id']);

		// 拼接正确参数
		$gps = array();
		$requires = array();
		foreach ($params as $_val) {
			// 如果是数组
			if ($serv_if->get_type_arr() == $_val['type']) {
				$gps[$_val['name']] = unserialize($_val['val']);
			} else {
				$gps[$_val['name']] = $_val['val'];
			}

			// 如果是必填参数
			if ($serv_if->get_required_y() == $_val['required']) {
				$requires[] = $_val['name'];
			}
		}

		// 执行日志
		$serv_log = D('Cli/InterfaceLog');
		// 入库数据
		$log = array(
			'f_id' => $if['f_id'],
			's_id' => $if['s_id'],
			'n_id' => $if['n_id']
		);

		// 整理 url
		$url = $if['url'];
		$this->_get_api_url($url);
		// header 信息
		$headers = array();
		// 如果登录用户信息错误
		if (!$this->_get_api_header($headers, $if['login_uid'])) {
			$log['code'] = '-1';
			$log['msg'] = L('API_LOGIN_UID_INVALID', array('uid' => $if['login_uid']));
			$log['params'] = '';
			$serv_log->insert($log);
			return true;
		}

		// 循环请求
		while (true) {
			try {
				// 接口调用
				$snoopy = null; // 返回值
				rfopen($snoopy, $url, $gps, $headers, $if['method'], true);
				$result = $snoopy->results;
				$log['code'] = $result['errcode'];
				$log['msg'] = serialize($result);
			} catch (\Think\Exception $e) {
				$log['code'] = '-1';
				$log['msg'] = $snoopy->results;
			}

			// 数据结果入库
			$log['params'] = serialize($gps);
			$serv_log->insert($log);

			// 如果还有参数未清除, 则
			if ($this->_del_param($gps, $requires)) {
				continue;
			}

			break;
		}

		return true;
	}

	/**
	 * 获取 api header
	 * @param array $headers 头信息
	 * @param int $uid 用户uid
	 * @return boolean
	 */
	protected function _get_api_header(&$headers, $uid) {

		// 如果不需要登录
		if (empty($uid)) {
			return true;
		}

		// 获取用户信息
		$serv_mem = D('Common/Member');
		if (!$member = $serv_mem->get($uid)) {
			return false;
		}

		// 刷新登录信息
		$login = &Login::instance();
		$login->flush_auth($member['m_uid'], $member['m_password']);

		// 取 cookie 信息
		$cookie = &Cookie::instance();
		$cdata = $cookie->get_cookie_data();
		$login_cookies = array();
		foreach ($cdata as $_k => $_v) {
			$login_cookies[] = $_k . '=' . urlencode($_v['value']);
		}

		$headers['Cookie'] = implode('; ', $login_cookies);
		return true;
	}

	/**
	 * 获取 api url
	 * @param string $url URL 地址
	 * @return boolean
	 */
	protected function _get_api_url(&$url) {

		$url = trim($url);
		// 如果指定了域名
		if (preg_match("/^https?\:\/\//i", $url)) {
			return true;
		}

		// 如果 $url 不是以 / 开头
		if ('/' != $url{0}) {
			$url = '/' . $url;
		}

		// 拼接 $url
		$url = cfg('PROTOCAL') . I('server.HTTP_HOST') . $url;
		return true;
	}

	/**
	 * 删除参数
	 * @param array $gps GET/POST参数
	 * @param array $requires 必填参数
	 */
	protected function _del_param(&$gps, $requires) {

		// 剔除可选参数重新测试
		foreach ($gps as $_k => $_v) {
			// 如果是非必填字段, 则剔除并重新发请求
			if (!in_array($_k, $requires)) {
				unset($gps[$_k]);
				return true;
			}
		}

		// 剔除必填字段, 重新测试
		foreach ($gps as $_k => $_v) {
			unset($gps[$_k]);
			return true;
		}

		return false;
	}

}
