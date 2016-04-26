<?php
/**
 * voa_uda_cyadmin_base
 * 畅移后台/基类
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_uda_cyadmin_base extends uda {

	const ZHUGUAN = 1; // 主管
	const XITONG = 0; // 系统

	public $_appset_status = array(
		'1' => '已付费',
		'2' => '已付费-即将到期',
		'3' => '已付费-已到期',
		'4' => '未付费',
		'5' => '试用期-即将到期',
		'6' => '试用期-已到期',
		'7' => '试用期'
	);

	// 客户等级
	protected  $_customer_level = array(
		1 => '小客户',
		2 => '中型客户',
		3 => '大型客户',
		4 => 'VIP客户'
	);
	// 客户状态
	protected $_customer_status = array(
		1 => '新增客户',
		2 => '初步沟通',
		3 => '见面拜访',
		4 => '确定意向',
		5 => '正式报价',
		6 => '商务谈判',
		7 => '签约成交',
		8 => '售后服务',
		9 => '停滞',
		10 => '流失'
	);

	/** RPC */
	protected $_rpc = null;

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 初始化企业RPC
	 * @param $ep_id 企业ID
	 * @return bool
	 */
	protected function _rpc_domain($ep_id) {

		// 根据企业ID获取企业域名地址
		$this->_get_domain_by_epid($ep_id, $domain);
		$this->_rpc = voa_h_rpc::phprpc(config::get('voa.oa_http_scheme') . $domain . '/CaRpc/Rpc/PluginGroup');

		return true;
	}

	/**
	 * 判断当前操作人是否有权限
	 * @param            $ep_id 企业ID
	 * @param            $operator_id 当前操作人ID
	 * @param bool|false $skip_leader 允许当前负责人
	 * @return bool
	 */
	protected function _authority($ep_id, $operator_id, $skip_leader = true) {

		// 获取企业信息
		$serv = &service::factory('voa_s_cyadmin_enterprise_newprofile');
		$ep_data = $serv->get_by_conds(array('ep_id' => $ep_id));
		$ca_id_data = $serv->get_by_conds(array('ca_id' => $operator_id));

		// 如果当前操作人 是高层
		if ($ca_id_data['ca_job'] == self::ZHUGUAN || $ca_id_data['ca_job'] == self::XITONG) {
			$ca_job = true;
			// 用于操作人是高层并且 更改自己的客户负责人
			$skip_leader = true;
		} else {
			$ca_job = false;
		}

		// 如果企业没有负责人 并且 操作人是高层
		if (isset($ep_data['ca_id']) && empty($ep_data['ca_id'])) {
			if ($ca_job) {
				return true;
			} else {
				return false;
			}
		} else {
			// 判断负责人是否存在
			$serv_adminer = &service::factory('voa_s_cyadmin_common_adminer');
			$ca_id = $serv_adminer->fetch($ep_data['ca_id']);
			// 为空并且是高层
			if (empty($ca_id) && $ca_job) {
				return true;
			}
		}

		// 如果企业负责人和 操作人ID相同 , 有权
		if ($skip_leader) {
			if ($ep_data['ca_id'] == $operator_id) {
				return true;
			}
		}

		/** 检查是否是上级领导修改 */
		// 获取上下级关联
		$serv_sub = &service::factory('voa_s_cyadmin_common_subordinates');
		$leaders = $serv_sub->list_by_conds(array('un_id' => $ep_data['ca_id']));
		// 如果没有上级
		if (empty($leaders)) {
			$this->errmsg('10000', '没有权限更改');

			return false;
		}

		// 判断当前操作人是不是 负责人的 上级
		foreach($leaders as $k => $v) {
			if ($v['ca_id'] == $operator_id) {
				return true;
			}
		}

		$this->errmsg('10000', '没有权限更改');
		return false;
	}

	/**
	 * 变更负责人后, 记录操作表
	 * @param $in // 企业ID 操作人名称 变更前的负责人id (ca_id_t) 变更后的(ca_id)
	 * @return bool
	 */
	protected function _change_lead_then_record($in) {

		// 匹配管理员名称
		$admin_data = voa_h_cache::get_instance()->get('adminer', 'cyadmin');

		$ca_id_changed = ''; // 更改后的 负责人名称
		$ca_id_name = '(已删除的账号)'; // 更改前的 负责人名称
		foreach ($admin_data as $k => $v) {
			if ($v['ca_id'] == $in['ca_id']) {
				$ca_id_changed = $v['ca_realname'];
			}
			if ($v['ca_id'] == $in['ca_id_t']) {
				$ca_id_name = $v['ca_realname'];
			}
		}
		$remark = '【' . $in['operator'] . '】变更了负责人:由【' . $ca_id_name . '】变为【' . $ca_id_changed . '】';

		// 记录操作
		$serv_record = &service::factory('voa_s_cyadmin_company_operationrecord');
		$serv_record->insert(array(
			'ep_id' => $in['ep_id'],
			'operator' => $in['operator'],
			'remark' => $remark,
			'ca_id_q' => $in['ca_id_t'],
			'ca_id_h' => $in['ca_id'],
		));

		return true;
	}

	/**
	 * 更改状态操作记录
	 * @param $in
	 * @param $error
	 * @return bool
	 */
	protected function _change_customer_status_record($in, &$error) {

		$serv_operationcord = &service::factory('voa_s_cyadmin_company_operationrecord');
		if (!$serv_operationcord->insert(array(
			'ep_id' => $in['ep_id'],
			'operator' => $in['operator'],
			'remark' => $in['remark'],
			'customer_status' => $in['customer_status'],
		))
		) {
			$error = array(
				'errcode' => '20001',
				'errmsg' => '添加操作记录失败',
			);

			return false;
		}

		return true;
	}

	/**
	 * 根据ep_id 获取 企业域名
	 * @param $ep_id
	 * @param $domain
	 * @return mixed
	 */
	protected function _get_domain_by_epid ($ep_id, &$domain) {

		$serv = &service::factory('voa_s_cyadmin_enterprise_newprofile');
		$ep_data = $serv->get_by_conds(array('ep_id' => $ep_id));

		$domain = $ep_data['ep_domain'];
		return $domain;
	}

	/**
	 * 请求指定企业OA站点的内部api接口
	 * @param string $domain 企业OA站点的域名，无https://
	 * @param string $class 需要使用的类名 /src/include/server/oa/[***].php
	 * @param string $method 需要使用的方法 /src/include/server/oa/[***].php 内定义的方法
	 * @param array $args 待发送的数据
	 * @param array $oa_result <strong style="color:red">(引用结果)</strong> 请求返回的结果
	 * @return boolean
	 */
	public function qyoa_api($domain, $classname, $method, $args = array(), &$oa_result) {

		// 构造企业OA站的api url
		$scheme = config::get('voa.oa_http_scheme');
		$api_url = $scheme.$domain.'/api.php';

		// 调用OA
		$rpc_oa = new voa_client_oa(config::get(startup_env::get('app_name').'.rpc.client.auth_key'));
		$method = $classname.'.'.$method;

		// 呼叫方法请求
		$result = $rpc_oa->call($api_url, $method, $args);

		// 构造错误
		$this->errmsg($rpc_oa->errno, $rpc_oa->errmsg);
		if ($rpc_oa->errno) {
			return false;
		} else {
			$oa_result = $result;
			return true;
		}
	}

}
