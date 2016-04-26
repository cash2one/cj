<?php

/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/10/20
 * Time: 上午10:13
 */
class voa_uda_cyadmin_company_add extends voa_uda_cyadmin_base {

	/**
	 * 验证数据
	 * @param $in 输入数据
	 * @param $error 报错信息
	 * @return bool
	 */
	public function filter(&$in, &$error) {

		//获取数据
		if (!empty($in)) {
			$data['ep_name'] = $in['ep_name'];
			$data['ca_id'] = $in['ca_id'];
			$data['ep_domain'] = $in['ep_domain'];
			$data['ep_password'] = $in['ep_password'];
			$data['ep_city'] = $in['ep_city'];
			$data['ep_companysize'] = $in['ep_companysize'];
			$data['ep_industry'] = $in['ep_industry'];
			$data['ep_ref'] = $in['ep_ref'];
			$data['customer_status'] = $in['customer_status'];
			$data['ep_customer_level'] = $in['ep_customer_level'];
			$data['ep_contact'] = $in['ep_contact'];
			$data['ep_contactposition'] = $in['ep_contactposition'];
			$data['ep_mobilephone'] = $in['ep_mobilephone'];
			$data['ep_email'] = $in['ep_email'];
			$data['bank_account'] = $in['bank_account'];
			$data['ep_url'] = $in['ep_url'];
			$data['ep_wxcorpid'] = $in['ep_wxcorpid'];
			$data['id_number'] = $in['id_number'];
			$data['ep_agent'] = $in['ep_agent'];
			$data['operator'] = $in['operator'];

			// 账号添加 域名
			if (isset($in['ep_domain']) && !empty($in['ep_domain'])) {
				$data['ep_domain'] = $in['ep_domain'] . '.vchangyi.com';
			}
		} else {
			$error = array('errcode' => '10000', 'errmsg' => '内容不能为空');

			return false;
		}

		// 验证规则
		$fields = array(
			'ep_name' => array('ep_name', parent::VAR_STR, null, null, false),
			'ca_id' => array('ca_id', parent::VAR_INT, null, null, false),
			'ep_domain' => array('ep_domain', parent::VAR_STR, null, null, false),
			'ep_password' => array('ep_password', parent::VAR_STR, null, null, false),
			'ep_city' => array('ep_city', parent::VAR_STR, null, null, false),
			'ep_companysize' => array('ep_companysize', parent::VAR_STR, null, null, false),
			'ep_industry' => array('ep_industry', parent::VAR_STR, null, null, false),
			'ep_ref' => array('ep_ref', parent::VAR_STR, null, null, false),
			'customer_status' => array('customer_status', parent::VAR_INT, null, null, false),
			'ep_customer_level' => array('ep_customer_level', parent::VAR_INT, null, null, false),
			'ep_contact' => array('ep_contact', parent::VAR_STR, null, null, false),
			'ep_contactposition' => array('ep_contactposition', parent::VAR_STR, null, null, false),
			'ep_mobilephone' => array('ep_mobilephone', parent::VAR_STR, null, null, false),
			'ep_email' => array('ep_email', parent::VAR_STR, null, null, false),
			'bank_account' => array('bank_account', parent::VAR_STR, null, null, false),
			'ep_url' => array('ep_url', parent::VAR_STR, null, null, false),
			'ep_wxcorpid' => array('ep_wxcorpid', parent::VAR_STR, null, null, false),
			'id_number' => array('id_number', parent::VAR_STR, null, null, false),
			'ep_agent' => array('ep_agent', parent::VAR_INT, null, null, false),
			'operator' => array('operator', parent::VAR_STR, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($result, $fields, $data)) {
			$error = array('errcode' => '10001', 'errmsg' => '数据不合法');

			return false;
		}
		if (empty($result['ep_name'])) {
			$error = array('errcode' => '10002', 'errmsg' => '企业名称不得为空');

			return false;
		}
		if (empty($result['ep_domain'])) {
			$error = array('errcode' => '10003', 'errmsg' => '企业账号不得为空');

			return false;
		}
		if (empty($result['ep_password'])) {
			$error = array('errcode' => '10010', 'errmsg' => '企业密码不得为空');

			return false;
		}
		if (empty($result['ep_companysize'])) {
			$error = array('errcode' => '10004', 'errmsg' => '企业规模不得为空');

			return false;
		}
		if (empty($result['ep_industry'])) {
			$error = array('errcode' => '10005', 'errmsg' => '所在行业不得为空');

			return false;
		}
		if (empty($result['ep_contact'])) {
			$error = array('errcode' => '10006', 'errmsg' => '联系人不得为空');

			return false;
		}
		if (empty($result['ep_mobilephone'])) {
			$error = array('errcode' => '10007', 'errmsg' => '手机号不得为空');

			return false;
		}
		if (empty($result['operator'])) {
			$error = array('errcode' => '10008', 'errmsg' => '丢失操作人信息');

			return false;
		}
		if (empty($result['ca_id'])) {
			$error = array('errcode' => '10009', 'errmsg' => '丢失管理员ID');

			return false;
		}

		$result['act'] = $in['act'];
		$in = $result;

		return true;
	}

	/**
	 * 信息入库
	 * @param $in
	 * @param $result
	 * @return bool
	 */
	public function insert($in, &$result) {

		// 去掉多余参数
		unset($in['act']);
		// 获取操作人信息
		$operator = $in['operator'];
		unset($in['operator']);

		$serv_profile = &service::factory('voa_s_cyadmin_enterprise_newprofile');


		// 添加操作记录
		$serv_operationcord = &service::factory('voa_s_cyadmin_company_operationrecord');
		if (!$serv_operationcord->insert(array(
			//			'ep_id' => $ep_id,
			'operator' => $operator,
			'customer_status' => $in['customer_status'],
		))
		) {
			$result = array('errcode' => '40001', 'errmsg' => '添加操作记录失败');

			return false;
		}

		return true;
	}

	/**
	 * 验证数据(编辑)
	 * @param $in 输入数据
	 * @param $error 报错信息
	 * @return bool
	 * @throws help_exception
	 */
	public function filter_edit(&$in, &$error) {

		//获取数据
		if (!empty($in)) {
			$data['ep_city'] = $in['ep_city'];
			$data['ep_companysize'] = $in['ep_companysize'];
			$data['ep_industry'] = $in['ep_industry'];
			$data['ep_ref'] = $in['ep_ref'];
			$data['customer_status'] = $in['customer_status'];
			$data['ep_customer_level'] = $in['ep_customer_level'];
			$data['ep_contact'] = $in['ep_contact'];
			$data['ep_contactposition'] = $in['ep_contactposition'];
			$data['ep_mobilephone'] = $in['ep_mobilephone'];
			$data['ep_email'] = $in['ep_email'];
			$data['bank_account'] = $in['bank_account'];
			$data['ep_url'] = $in['ep_url'];
			$data['operator'] = $in['operator'];
			$data['ep_id'] = $in['ep_id'];

			// 账号添加 域名
			if (isset($in['ep_domain']) && !empty($in['ep_domain'])) {
				$data['ep_domain'] = $in['ep_domain'] . '.vchangyi.com';
			}
		} else {
			$error = array('errcode' => '10000', 'errmsg' => '内容不能为空');

			return false;
		}

		// 验证规则
		$fields = array(
			'ep_city' => array('ep_city', parent::VAR_STR, null, null, false),
			'ep_companysize' => array('ep_companysize', parent::VAR_STR, null, null, false),
			'ep_industry' => array('ep_industry', parent::VAR_STR, null, null, false),
			'ep_ref' => array('ep_ref', parent::VAR_STR, null, null, false),
			'customer_status' => array('customer_status', parent::VAR_INT, null, null, false),
			'ep_customer_level' => array('ep_customer_level', parent::VAR_INT, null, null, false),
			'ep_contact' => array('ep_contact', parent::VAR_STR, null, null, false),
			'ep_contactposition' => array('ep_contactposition', parent::VAR_STR, null, null, false),
			'ep_mobilephone' => array('ep_mobilephone', parent::VAR_STR, null, null, false),
			'ep_email' => array('ep_email', parent::VAR_STR, null, null, false),
			'bank_account' => array('bank_account', parent::VAR_STR, null, null, false),
			'ep_url' => array('ep_url', parent::VAR_STR, null, null, false),
			'operator' => array('operator', parent::VAR_STR, null, null, false),
			'ep_id' => array('ep_id', parent::VAR_INT, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($result, $fields, $data)) {
			$error = array('errcode' => '10001', 'errmsg' => '数据不合法');

			return false;
		}

		if (count($result['ep_contact']) > 12) {
			$error = array('errcode' => '10010', 'errmsg' => '联系人长度不得超过12个字符');

			return false;
		}
		if (empty($result['ep_companysize'])) {
			$error = array('errcode' => '10004', 'errmsg' => '企业规模不得为空');

			return false;
		}
		if (empty($result['ep_industry'])) {
			$error = array('errcode' => '10005', 'errmsg' => '所在行业不得为空');

			return false;
		}
		if (empty($result['ep_contact'])) {
			$error = array('errcode' => '10006', 'errmsg' => '联系人不得为空');

			return false;
		}
		if (empty($result['ep_mobilephone'])) {
			$error = array('errcode' => '10007', 'errmsg' => '手机号不得为空');

			return false;
		}
		if (empty($result['operator'])) {
			$error = array('errcode' => '10008', 'errmsg' => '丢失操作人信息');

			return false;
		}
		if (empty($result['ep_id'])) {
			$error = array('errcode' => '10009', 'errmsg' => '丢失企业ID');

			return false;
		}

		$in = $result;

		return true;
	}

	/**
	 * 更新数据
	 * @param $in
	 * @param $result
	 * @return bool
	 */
	public function update_edit($in, &$result) {

		// 提取操作人 和 企业ID , 然后从更新数据中去掉
		$operator = $in['operator'];
		$ep_id = $in['ep_id'];
		unset($in['operator']);
		unset($in['ep_id']);

		// 更新数据
		$serv_profile = &service::factory('voa_s_cyadmin_enterprise_newprofile');

		// 找出更新的数据
		$old_data = $serv_profile->get_by_conds(array('ep_id' => $ep_id));
		$new_data = array(); // 更新数据库的数据
		$new_operation_data = array(); // 旧数据变更新数据的记录
		// 判断 相同 字段的数据 是否有改动
		foreach ($old_data as $k => $v) {
			foreach ($in as $_k => $_v) {

				if ($k == $_k && $v != $_v) {
					// 存入数据库的
					$new_data[$k] = $_v;

					// 判断是否有 客户状态 或者 客户等级 那么 匹配数据
					if (isset($new_data['customer_status']) || isset($new_data['ep_customer_level'])) {
						// 匹配字段信息
						if (isset($in['customer_status'])) {
							foreach ($this->_customer_status as $key => $val) {
								if ($v == $key) {
									$v = $val;
								}
								if ($_v == $key) {
									$_v = $val;
								}
								$new_operation_data[$k] = '由【' . $v . '】变更为【' . $_v . '】';
							}
						}
						if (isset($in['ep_customer_level'])) {
							foreach ($this->_customer_level as $key => $val) {
								if ($v == $key) {
									$v = $val;
								}
								if ($_v == $key) {
									$_v = $val;
								}
								$new_operation_data[$k] = '由【' . $v . '】变更为【' . $_v . '】';
							}
						}
					} else {

						// 其他不需要匹配的数据
						$new_operation_data[$k] = '由【' . $v . '】变更为【' . $_v . '】';
					}

					continue;
				}

			}
		}

		// 如果没有更改的
		if (empty($new_data)) {
			$result['errcode'] = '999';
			$result['errmsg'] = '没有更新的数据';

			return false;
		}

		// 更新数据
		if (!$serv_profile->update_by_conds(array('ep_id' => $ep_id), $new_data)) {
			$result = array('errcode' => '40000', 'errmsg' => '更新失败');
		}

		// 匹配更改的字段名称
		$qiye_ziduan = array(
			'ep_city' => '企业地址',
			'ep_companysize' => '企业规模',
			'ep_industry' => '所在行业',
			'ep_ref' => '客户来源',
			'customer_status' => '客户状态',
			'ep_customer_level' => '客户等级',
			'ep_contact' => '联系人',
			'ep_contactposition' => '客户职位',
			'ep_mobilephone' => '手机号',
			'ep_email' => '电子邮箱',
			'bank_account' => '银行账号',
			'ep_url' => '公司域名',
		);
		// 获取键值相同 匹配字段数据
		$operation_data = '';
		foreach ($new_operation_data as $k => $v) {
			foreach ($qiye_ziduan as $_k => $_v) {
				if ($k == $_k) {
					$operation_data .= $_v . $v . "<br>";
				}
			}
		}

		// 添加操作记录
		$update_data = array(
			'ep_id' => $ep_id,
			'operator' => $operator,
			'remark' => $operation_data,
		);
		// 判断 客户状态 需不要 写入
		if (isset($new_data['customer_status'])) {
			$update_data['customer_status'] = $new_data['customer_status'];
		}
		// 写入操作记录
		$serv_operationcord = &service::factory('voa_s_cyadmin_company_operationrecord');
		if (!$serv_operationcord->insert($update_data)) {
			$result = array('errcode' => '40001', 'errmsg' => '添加操作记录失败');

			return false;
		}

		return true;
	}

	/**
	 * 更新企业数据
	 * @param $in
	 * @param $result
	 * @return bool
	 */
	public function insert_other_data($in, &$result) {

		$serv_profile = &service::factory('voa_s_cyadmin_enterprise_newprofile');

		$conds = array(
			'ep_mobilephone' => $in['ep_mobilephone'],
		);
		$data = array(
			'ca_id' => $in['ca_id'],
			'ep_ref' => $in['ep_ref'],
			'ep_city' => $in['ep_city'],
			'ep_agent' => $in['ep_agent'],
			'id_number' => $in['id_number'],
			'ep_contact' => $in['ep_contact'],
			'ep_contactposition' => $in['ep_contactposition'],
			'customer_status' => $in['customer_status'],
			'ep_customer_level' => $in['ep_customer_level'],
			'bank_account' => $in['bank_account'],
			'ep_url' => $in['ep_url'],
		);
		$serv_profile->update_by_conds($conds, $data);

		return true;
	}
}
