<?php
/**
 * voa_uda_uc_base
 * uda/UC
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_uc_base extends uda {

	public $errcode = 0;
	public $errmsg = '';
	public $result = '';

	public $serv_list = array();

	public function __construct() {
		parent::__construct();
	}

	public function s($s_class) {
		if (!isset($this->serv_list[$s_class])) {
			$this->serv_list[$s_class] = &service::factory($s_class);
		}
		return $this->serv_list[$s_class];
	}

	/**
	 * 请求指定企业OA站的api接口
	 * @param string $domain OA企业站的域名
	 * @param string $api_url OA企业站api接口相对路径
	 * @param array $data <strong style="color:red">(引用结果)</strong> 获取的结果
	 * @param array $post 待发送的数据
	 * @param array $http_header 请求的http头字段数组
	 * @param string $http_method 使用的HTTP协议，默认为：GET，可使用 POST、DELETE、PUT
	 * @param array $snoopy_reporting <strong style="color:red">(引用结果)</strong> snoopy链接信息
	 * @return boolean
	 */
	public function api_call($domain, $api_url, &$result, $post = array(), $http_header = array(), $http_method = 'GET', &$snoopy_reporting = array()) {
		// OA 接口地址
		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme.$domain.'/'.$api_url;
		// 返回的数据
		$result = array();
		// snoopy 报告
		$snoopy_reporting = array();
		if (!voa_h_func::get_json_by_post_and_header($result, $url, $post, $http_header, $http_method, $snoopy_reporting)) {
			return false;
		}

		return true;
	}

	/**
	 * 请求指定企业OA站点的内部api接口
	 * @param string $domain 企业OA站点的域名，无https://
	 * @param string $class 需要使用的类名 /src/include/server/oa/[***].php
	 * @param string $method 需要使用的方法 /src/include/server/oa/[***].php 内定义的方法
	 * @param array $args 待发送的数据
	 * @param string $host_ip 企业站所在主机的IP
	 * @param array $oa_result <strong style="color:red">(引用结果)</strong> 请求返回的结果
	 * @return boolean
	 */
	public function rpc_call($domain, $classname, $method, $args = array(), $host_ip = '', &$oa_result) {

		// 构造企业OA站的api url
		$scheme = config::get('voa.oa_http_scheme');
		$api_url = $scheme.$domain.'/api.php';

		// 调用OA
		$rpc_oa = new voa_client_oa(config::get(startup_env::get('app_name').'.rpc.client.auth_key'));
		$method = $classname.'.'.$method;

		// 呼叫方法请求
		$result = $rpc_oa->call($api_url, $method, $args, $host_ip);

		// 构造错误
		$this->errmsg($rpc_oa->errno, $rpc_oa->errmsg);
		if ($rpc_oa->errno) {
			$this->errcode = $rpc_oa->errno;
			$this->errmsg = $rpc_oa->errmsg;
			$this->errmsg($rpc_oa->errno, $rpc_oa->errmsg);
			return false;
		} else {
			$oa_result = $result;
			return true;
		}
	}

	/**
	 * 将错误常量字符串转换为错误变量成员
	 * <p>set $this->errcode<br /> set $this->errmsg</p>
	 * @param string $error_const_string
	 * @return boolean 成功返回true，错误返回false
	 */
	public function error_msg($error_const_string) {
		$this->errcode = -404;
		$this->errmsg = '';
		if (preg_match('/^\s*(\d+)\s*\:\s*(.+)$/', $error_const_string, $match)) {
			// 分离 错误代码 和 错误消息
			$this->errcode = (int)$match[1];
			$this->errmsg = (string)$match[2];
		} else {
			// 错误代码定义出错
			$this->errcode = -404;
			$this->errmsg = '代码定义错误"'.$error_const_string.'"';
		}

		if (!preg_match('/\%\w/i', $this->errmsg, $matches)) {
			// 错误消息描述内未发现变量，则直接输出
			return $this->errcode == 0 ? true : false;
		}

		// 获取给定的参数
		$values = func_get_args();
		// 列出变量值
		unset($values[0]);
		if (empty($values)) {
			// 如果变量值不存在
			return $this->errcode == 0 ? true : false;
		}

		// 变量个数 与 值的个数 相差数
		$count = count(preg_split('/\%\w/i', $this->errmsg)) - count($values);
		if ($count > 0) {
			// 变量个数 多于 给定值个数，则补充值的个数，避免出错
			for ($i = 0; $i < $count; $i++) {
				$values[] = '';
			}
		}
		// 转义变量名
		$this->errmsg = vsprintf($this->errmsg, $values);

		return $this->errcode == 0 ? true : false;
	}

	/**
	 * 将UC企业信息同步至主站数据库(一般只用于开通注册时)
	 * @param number $ep_id 企业ID
	 * @param array $data 企业信息数组
	 * @return boolean
	 */
	public function vchangyi_enterprise_api($ep_id, $data) {

		$serv_cyadmin_enterprise_profile = $this->s('voa_s_cyadmin_enterprise_profile');
		// 检查主站库内是否存在该企业
		$enterprise = $serv_cyadmin_enterprise_profile->fetch($ep_id);

		// 主站企业表 与 UC企业表的字段映射关系
		$field_maps = array(
			'ep_id' => 'ep_id',
			'ep_name' => 'ep_name',
			'ep_industry' => 'epp_industry',
			'ep_city' => 'epp_city',
			'ep_agent' => 'epp_agent',
			'ep_domain' => 'ep_domain',
			'ep_companysize' => 'epp_companysize',
			'ep_contact' => 'epp_adminrealname',
			'ep_ref' => 'epp_ref',
			'ep_ref_domain' => 'epp_ref_domain',
			'ep_mobilephone' => 'ep_adminmobilephone',
			'ep_email' => 'ep_adminemail',
			'ep_adminmobile' => 'ep_adminmobilephone',
			'ep_adminrealname' => 'ep_adminrealname',
		);

		// 构造主站企业数据
		$cyadmin_enterprise = array();
		foreach ($field_maps as $cy => $uc) {
			if (isset($data[$uc])) {
				$cyadmin_enterprise[$cy] = $data[$uc];
			}
		}

		if (empty($cyadmin_enterprise)) {
			return true;
		}

		// 新增
		if (empty($enterprise)) {
			$serv_cyadmin_enterprise_profile->insert($cyadmin_enterprise);
			// 添加企业试用期信息
//新增用户也根据套件开始使用时间计算			$this->__add_probation($cyadmin_enterprise);
		} else { // 更新
			// 需要进行更新的数据
			$update = array();
			foreach ($cyadmin_enterprise as $key => $value) {
				if ($value != $enterprise[$key]) {
					$update[$key] = $value;
				}
			}

			$serv_cyadmin_enterprise_profile->update($update, $ep_id);
		}

		return true;
	}

	/**
	 * 添加企业试用期信息
	 * @param $ep_id 企业ID
	 * @return bool
	 */
	private function __add_probation($enterprise) {

		// 获取套件信息
		$serv_uc_plugin_group = $this->s('voa_s_uc_common_plugin_group');
		$plugin_group = $serv_uc_plugin_group->list_all();

		// 试用期时间
		$probation_time = voa_h_cache::get_instance()->get('probation_time', 'uc');
		// 如果 试用期时间缓存 创建时间 大于1小时 ,那么更新缓存
		if ($probation_time['created'] - startup_env::get('timestamp') > 3600) {
			$probation_time = voa_h_cache::get_instance()->get('probation_time', 'uc', true);
		}
		// 套件试用数据
		$probation_time = $probation_time['trydate']; // 试用期天数
		$probation_data = array();
		foreach ($plugin_group as $k => $v) {
			$probation_data[] = array(
				'ep_id' => $enterprise['ep_id'],
				'pay_type' => 1, // 标准产品
				'cpg_id' => $v['cpg_id'], // 套件ID
				'pay_status' => 7, // 试用期状态 总后台是7
				'date_start' => startup_env::get('timestamp'),
				'date_end' => startup_env::get('timestamp') + $probation_time * 86400 // 试用结束时间 当前时间加上试用时间
			);
		}

		/** 写入总后台数据 */
		$this->__rpc_cy_domain('CompanyPaysetting', $cy_probation);
		$cy_probation->insert_probation_data($probation_data);

		/** 写入企业后台 */
		$cpg_ids = array_column($plugin_group, 'cpg_id');
		$ep_data = array(
			'pay_type' => 1, // 标准产品
			'pay_status' => 2, // 试用期状态 企业后台是2
			'date_start' => startup_env::get('timestamp'),
			'date_end' => startup_env::get('timestamp') + $probation_time * 86400 // 试用结束时间 当前时间加上试用时间
		);

		$rpc = voa_h_rpc::phprpc(config::get('voa.oa_http_scheme') . $enterprise['ep_domain'] . '/UcRpc/Rpc/PluginGroup');
		$rpc->update_cpg($cpg_ids, $ep_data);

		return true;
	}

	/**
	 * 初始化 CY RPC
	 * @param $function RPC用的controller
	 * @param $rpc RPC 变量
	 * @return bool
	 */
	private function __rpc_cy_domain($function, &$rpc) {

		// 获取 总后台 域名
		$domain = config::get('voa.cyadmin_domain.domain_url');
		// 初始化RPC
		$rpc = voa_h_rpc::phprpc(config::get('voa.oa_http_scheme') . $domain . '/UcRpc/Rpc/' . $function);

		return true;
	}

}
