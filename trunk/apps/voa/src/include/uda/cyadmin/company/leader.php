<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 15/10/21
 * Time: 下午1:29
 */

class voa_uda_cyadmin_company_leader extends voa_uda_cyadmin_base {

	const XIAOSHOU = 2;

	/**
	 * 验证数据
	 * @param $in 输入数据
	 * @param $error 报错信息
	 * @return bool
	 */
	public function filter(&$in, &$error) {

		//获取数据
		if (!empty($in)) {
			$data['ca_id'] = $in['ca_id'];
			$data['ep_id'] = $in['ep_id'];
			$data['op_ca_id'] = $in['op_ca_id'];
			$data['operator'] = $in['operator'];
		} else {
			$error = array('errcode' => '10000', 'errmsg' => '内容不能为空');

			return false;
		}

		// 验证规则
		$fields = array(
			'ca_id' => array('ca_id', parent::VAR_INT, null, null, false),
			'ep_id' => array('ep_id', parent::VAR_INT, null, null, false),
			'op_ca_id' => array('op_ca_id', parent::VAR_INT, null, null, false),
			'operator' => array('operator', parent::VAR_STR, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($result, $fields, $data)) {
			$error = array('errcode' => '10001', 'errmsg' => '数据不合法');

			return false;
		}
		if (empty($result['ca_id'])) {
			$error = array('errcode' => '10002', 'errmsg' => '丢失负责人ID');

			return false;
		}
		if (empty($result['ep_id'])) {
			$error = array('errcode' => '10003', 'errmsg' => '丢失企业ID');

			return false;
		}
		if (empty($result['op_ca_id'])) {
			$error = array('errcode' => '10004', 'errmsg' => '丢失当前操作人ID');

			return false;
		}
		if (empty($result['operator'])) {
			$error = array('errcode' => '10005', 'errmsg' => '丢失当前操作人名字');

			return false;
		}

		$in = $result;

		return true;
	}

	/**
	 * 更新数据
	 * @param $in 输入数据
	 * @param $error
	 * @return bool
	 */
	public function update_data($in, &$error) {

		// 获取当前企业信息
		$serv = &service::factory('voa_s_cyadmin_enterprise_newprofile');
		$ep_data = $serv->get_by_conds(array('ep_id' => $in['ep_id']));
		if (empty($ep_data)) {
			$error = array('errcode' => '20000', 'errmsg' => '未找到当前企业信息');

			return false;
		}
		// 要变更的负责人是否和当前负责人一样
		if ($ep_data['ca_id'] == $in['ca_id']) {
			return true;
		}

		// 判断当前操作人有没有权限
		if (!$this->_authority($in['ep_id'], $in['op_ca_id'], false)) {
			$error = array('errcode' => '999', 'errmsg' => '没有权限这么做');

			return false;
		};

		// 更改负责人
		$serv->update_by_conds(array('ep_id' => $in['ep_id']), array('ca_id' => $in['ca_id']) );

		// 变更操作记录
		$in['ca_id_t'] = $ep_data['ca_id']; // 变更前的负责人
		$this->_change_lead_then_record($in);

		return true;
	}

	/**
	 * 企业一次添加多个负责人
	 * @param $in
	 * @param $error
	 * @return bool
	 * @throws help_exception
	 */
	public function leaders_filter(&$in, &$error) {

		// 验证规则
		$fields = array(
			'ep_ids' => array('ep_ids', parent::VAR_ARR, null, null, false),
			'leading' => array('leading', parent::VAR_INT, null, null, false),
			'ca_id' => array('ca_id', parent::VAR_INT, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($result, $fields, $in)) {
			$error = array('errcode' => '10001', 'errmsg' => '数据不合法');

			return false;
		}

		if (isset($result['ep_ids']) && empty($result['ep_ids'])) {
			$error = array('errcode' => '10002', 'errmsg' => '丢失企业ID');

			return false;
		}
		if (isset($result['leading']) && empty($result['leading'])) {
			$error = array('errcode' => '10003', 'errmsg' => '丢失负责人ID');

			return false;
		}
		if (isset($result['ca_id']) && empty($result['ep_ids'])) {
			$error = array('errcode' => '10004', 'errmsg' => '丢失操作人ID');

			return false;
		}

		$in = $result;

		return true;
	}

	/**
	 * 判断权限 并且添加负责人
	 * @param $in 输入数据
	 * @param $error 提示
	 * @return bool
	 */
	public function leaders_authority_and_update($in, &$error) {

		// 获取操作人的权限等级
		$serv_adminer = &service::factory('voa_s_cyadmin_common_adminer');
		$adminer = $serv_adminer->fetch($in['ca_id']);
		// 如果是销售, 没有权限
		if ($adminer['ca_job'] == self::XIAOSHOU) {
			$error = array('errcode' => '10000', 'errmsg' => '没有权限');

			return false;
		}

		// 获取企业信息
		$serv_profile = &service::factory('voa_s_cyadmin_enterprise_newprofile');
		$profile_conds = array('ep_id IN (?)' => $in['ep_ids']);
		$ep_data = $serv_profile->list_by_conds($profile_conds);

		// 筛选有无负责人的企业
		$had_lead = array();
		$dt_lead = array();
		foreach ($ep_data as $k => $v) {
			if ($v['ca_id'] == 0) {
				$dt_lead[$k] = $ep_data[$k];
			} else {
				$had_lead[$k] = $ep_data[$k];
			}
		}

		// 初始化提示
		$error['errmsg'] = '操作成功';

		// 没有负责人的企业 加入提交的负责人
		if (!empty($dt_lead)) {
			// 取出企业ID
			$ep_ids = array_column($dt_lead, 'ep_id');
			// 构建更新条件 和 数据
			$conds = array('ep_id IN (?)' => $ep_ids);
			$update_data = array('ca_id' => $in['leading']);
			$serv_profile->update_by_conds($conds, $update_data);
			// 当有添加成功的企业时,改变初始化提示
			$error['errmsg'] = '添加成功';

			// 更新最后操作时间
			$uda = &uda::factory('voa_uda_cyadmin_enterprise_profile');
			$uda->add_last_operation($ep_ids);
		}

		// 提示已经有负责人的企业
		if (!empty($had_lead)) {
			$error['errmsg'] .= ',但以下企业已经有负责人:';
			foreach ($had_lead as $k => $v) {
				$error['errmsg'] .= ' 【' . $v['ep_name'] . '】 ';
			}
		}

		return true;
	}

}
