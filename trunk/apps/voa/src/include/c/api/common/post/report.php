<?php
/**
 * report.php
 * 前端环境报告发送接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */

class voa_c_api_common_post_report extends voa_c_api_common_abstract {

	/** 当前系统相关环境变量 */
	private $__variables = array();

	public function execute() {

		// 需要传入的参数
		$fields = array(
			// 微信js的返回结果
			'res' => array('type' => 'array', 'required' => true),
			// 微信js报错使用的方法
			'type' => array('type' => 'string_trim', 'required' => true),
			// 报告页面的url
			'url' => array('type' => 'string_trim', 'required' => true),
			// 浏览器代理头
			'useragent' => array('type' => 'string_trim', 'required' => true),
			// 报告类型
			'rtype' => array('type' => 'string_trim', 'required' => false)
		);

		// 参数数值基本检查验证
		$this->_check_params($fields);
		// 默认发布微信接口报告
		if (empty($this->_params['rtype'])) {
			$this->_params['rtype'] = 'wxjs';
		}
		// 获取环境变量
		if (!$this->__get_variables()) {
			return false;
		}

		switch ($this->_params['rtype']) {
			case 'wxjs':
				$this->__wxjs();
			break;
		}

		return true;
	}

	/**
	 * 根据参数值构造报告的唯一值
	 * @return string
	 */
	private function __hash() {

		return md5(rstrtolower(serialize($this->_params)));
	}

	/**
	 * 写入微信JS接口日志
	 * @return boolean
	 */
	private function __wxjs() {
		// 获取数据唯一字符串
		$hash = $this->__hash();
		// 载入service
		$serv = &service::factory('voa_s_cyadmin_report_wxjs');
		// 尝试获取该日志最后的发送时间
		$last = $serv->get_last($hash, $this->__variables['ip']);
		// 如果存在此日志
		if (!empty($last)) {
			// 上次报告时间距离现在超过1个小时
			if (startup_env::get('timestamp') - $last['updated'] > 3600) {
				// 增加报告次数
				$serv->update(array('count' => $last['count'] + 1), $last['id']);
			}

			return true;
		}

		// 新增日志
		$serv->insert(array(
			'hash' => $hash,
			'ip' => $this->__variables['ip'],
			'res' => serialize($this->_params['res']),
			'type' => $this->_params['type'],
			'url' => $this->_params['url'],
			'useragent' => $this->_params['useragent'],
			'ep_id' => $this->__variables['ep_id'],
			'domain' => $this->__variables['domain'],
			'uid' => $this->__variables['uid'],
			'wxversion' => $this->__variables['wxversion'],
			'mobile' => $this->__variables['mobile']
		));
	}

	/**
	 * 写入访问日志
	 */
	private function __log() {

	}

	/**
	 * 获取当前访问的环境变量
	 * @return boolean
	 */
	private function __get_variables() {

		$this->__variables = array(
			// 当前IP地址
			'ip' => controller_request::get_instance()->get_client_ip(),
			// 用户UID
			'uid' => $this->_member['m_uid'],
			// 用户手机号
			'mobile' => $this->_member['m_mobilephone'],
			// 企业ID
			'ep_id' => $this->_setting['ep_id'],
			// 企业域名
			'domain' => $this->_setting['domain'],
			// 微信版本号
			'wxversion' => '',
			// 浏览器代理头
			'useragent' => ''
		);

		// 浏览器代理头信息
		$useragent = $this->_params['useragent'];
		if (empty($useragent)) {
			if (!isset($_SERVER['HTTP_USER_AGENT'])) {
				return false;
			}
			$useragent = $_SERVER['HTTP_USER_AGENT'];
		}
		$this->__variables['useragent'] = $useragent;

		// 获取微信版本信息
		if (preg_match('/MicroMessenger\s*\/\s*([^ ]+)/is', $useragent, $match)) {
			$this->__variables['wxversion'] = trim($match[1]);
		}

		return true;
	}

}
