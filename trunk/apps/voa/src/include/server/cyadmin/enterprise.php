<?php
/**
 * class voa_server_cyadmin_enterprise {
 * 畅移主站企业接口
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_server_cyadmin_enterprise {

	/** 企业表字段 */
	protected $_profile_fields = null;

	/**
	 * 构造函数
	 */
	public function __construct() {
		$this->_profile_fields = array(
			'ep_id',// 企业ID
			'ep_wxqy',// 是否开启了微信企业号服务
			'ep_wxcorpid',// 微信企业号corpid
			'ep_wxcorpsecret',// 微信企业号corpsecret

			//'ca_id',// 跟进该企业的管理人员ID
			//'ep_name',// 企业名称
			//'ep_industry',// 行业
			//'ep_city',// 城市
			//'ep_agent',// 代理商
			//'ep_domain',// 域名
			//'ep_contact',// 联系人
			//'ep_contactposition',// 联系人职位
			//'ep_mobilephone',// 联系人手机号
			//'ep_email',// 邮箱
			//'ep_wxname',// 微信企业号名称
			//'ep_wxuname',// 微信企业号用户名
			//'ep_wxpasswd',// 微信企业号密码加密
			//'ep_adminmobile',// 管理员手机号
			//'ep_adminrealname',// 管理员姓名
			//'ep_admindepartment',// 管理员部门
		);
	}

	/**
	 * 更新企业资料信息
	 * @param array $params
	 * @return boolean
	 */
	public function update_profile($params) {

		// 过滤字段
		$this->_field_filter($params);

		if (empty($params)) {
			return $this->_set_errmsg(voa_errcode_cyadmin_enterprise::RPC_SERVER_UPDATE_PROFILE_DATA_EMPTY);
		}

		if (empty($params['ep_id'])) {
			return $this->_set_errmsg(voa_errcode_cyadmin_enterprise::RPC_SERVER_UPDATE_PROFILE_ID_NULL);
		}
		$ep_id = intval($params['ep_id']);

		// 需要更新的数据
		$update = array();

		// 检查“是否开启微信企业号服务”的设置
		if (isset($params['ep_wxqy'])) {
			$update['ep_wxqy'] = $params['ep_wxqy'];// by Deepseath@20141210
		}

		// 检查是否设置了微信企业号的corpid
		if (isset($params['ep_wxcorpid'])) {
			$update['ep_wxcorpid'] = $params['ep_wxcorpid'];
		}

		// 检查是否设置了微信企业号的corpsecret
		if (isset($params['ep_wxcorpsecret'])) {
			$update['ep_wxcorpsecret'] = $params['ep_wxcorpsecret'];
		}

		$uda_enterprise_profile = &uda::factory('voa_uda_cyadmin_enterprise_profile');

		// 检查企业是否存在
		$enterprise_profile = array();
		if (!$uda_enterprise_profile->get_by_id($ep_id, $enterprise_profile)) {
			return $this->_set_errmsg(voa_errcode_cyadmin_enterprise::RPC_SERVER_UPDATE_PROFILE_NOT_EXISTS, $ep_id);
		}

		// 更新
		if (!$uda_enterprise_profile->update_profile($ep_id, $update)) {
			return $this->_set_errmsg(voa_errcode_cyadmin_enterprise::RPC_SERVER_UPDATE_PROFILE_ERROR);
		}

		return true;
	}

	/**
	 * 过滤不需要的字段
	 * @param array $params
	 * @return boolean
	 */
	protected function _field_filter(&$params) {
		if (!is_array($params)) {
			$params = array();
			return false;
		}
		foreach ($params as $k => $v) {
			if (!in_array($k, $this->_profile_fields)) {
				unset($params[$k]);
			}
		}

		return true;
	}

	/**
	 * 解析错误编码常量并输出到uda错误信息变量内
	 * @param string $const_string
	 * @throws rpc_exception
	 * @return boolean
	 */
	protected function _set_errmsg($const_string) {

		// 设置当前错误信息
		$func = new voa_h_func();
		call_user_func_array(array($func, 'set_errmsg'), func_get_args());

		throw new rpc_exception(voa_h_func::$errmsg, voa_h_func::$errcode);

		return false;
	}

}
