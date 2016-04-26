<?php
/**
 * 开通体验账号 的接口
 * $Author$
 * $Id$
 */

class voa_server_oa_experience {

	/**
	 * __construct
	 * 构造函数
	 *
	 * @return void
	 */
	public function __construct() {
		if (!voa_h_conf::init_db()) {
			exit('config file is missing.');
			return false;
		}
	}

	/**
	 * 体验号 开通操作
	 * @param array $args 用户信息
	 */
	public function open($args) {
		/** 参数为空判断 */
		if (empty($args) || !is_array($args)) {
			throw new rpc_exception('args is empty.', 100);
		}

		/** 分配部及职位 */
		/** 获取部门信息 */
		$departments = voa_h_cache::get_instance()->get('department', 'oa');
		/** 获取职位信息 */
		$jobs = voa_h_cache::get_instance()->get('job', 'oa');

		/** 随机下数组 取一组 */
		shuffle($departments);
		shuffle($jobs);
		$args['cd_id'] = $departments[0]['cd_id'];
		$args['cj_id'] = $jobs[0]['cj_id'];

		/** 调用 uda 生成体验账号 */
		$exprience_member_insert = &uda::factory('voa_uda_frontend_member_insert');

		/** 生成账号返回值处理 */
		$m = null;
		if (!$exprience_member_insert->add($args, $m, false)) {
			/** 定义返错 数组 */
			$return = array();
			$return['codeno'] = $exprience_member_insert->errno;
			$return['error'] = $exprience_member_insert->error;
			return $return;
		}

		// 插入对应关系
		$serv = &service::factory('voa_s_oa_member_department');
		$serv->insert(array('m_uid' => $m['m_uid'], 'cd_id' => $args['cd_id'], 'mp_id' => 0));
		return true;
	}

}
