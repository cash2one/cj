<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/1/15
 * Time: 上午12:02
 */

class voa_uda_cyadmin_company_transfer extends voa_uda_cyadmin_base {

	/**
	 * 验证数据
	 * @param $in 输入数据
	 * @param $error 报错信息
	 * @return bool
	 */
	public function filter(&$in, &$error) {

		//获取数据
		if (!empty($in)) {
			$data['ca_id_form'] = $in['ca_id_form'];
			$data['ca_id_to'] = $in['ca_id_to'];
			$data['operator'] = $in['operator'];
			$data['op_ca_id'] = $in['op_ca_id'];
		} else {
			$error = array('errcode' => '10000', 'errmsg' => '内容不能为空');

			return false;
		}

		// 验证规则
		$fields = array(
			'ca_id_form' => array('ca_id_form', parent::VAR_INT, null, null, false),
			'ca_id_to' => array('ca_id_to', parent::VAR_INT, null, null, false),
			'operator' => array('operator', parent::VAR_STR, null, null, false),
			'op_ca_id' => array('op_ca_id', parent::VAR_INT, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($result, $fields, $data)) {
			$error = array('errcode' => '10001', 'errmsg' => '数据不合法');

			return false;
		}
		if ($result['ca_id_form'] == $result['ca_id_to']) {
			$error = array('errcode' => '10006', 'errmsg' => '被迁移的负责人和迁移的目标负责人不能一致');
		}
		if (empty($result['ca_id_form'])) {
			$error = array('errcode' => '10002', 'errmsg' => '丢失被迁移负责人ID');

			return false;
		}
		if (empty($result['ca_id_to'])) {
			$error = array('errcode' => '10003', 'errmsg' => '丢失迁移目标负责人ID');

			return false;
		}
		if (empty($result['operator'])) {
			$error = array('errcode' => '10004', 'errmsg' => '丢失当前操作人名字');

			return false;
		}
		if (empty($result['op_ca_id'])) {
			$error = array('errcode' => '10005', 'errmsg' => '丢失当前操作人ID');

			return false;
		}

		$in = $result;

		return true;
	}

	/**
	 * 迁移负责人
	 * @param $post
	 * @param $error
	 * @return bool
	 */
	public function update_data($post, &$error) {

		// 获取被迁移人的数据
		$serv_adminer = &service::factory('voa_s_cyadmin_common_adminer');
		$user_data = $serv_adminer->fetch($post['ca_id_form']);
		// 获取操作人数据
		$op_data = $serv_adminer->fetch($post['op_ca_id']);
		// 获取迁移目标负责人数据
		$to_data = $serv_adminer->fetch($post['ca_id_to']);

		// 如果不是主管职位
		if ($op_data['ca_job'] != self::ZHUGUAN) {
			$error = array('errcode' => '10000', 'errmsg' => '没有权限!');

			return false;
		}

		// 找出被迁移人的客户
		$serv_profile = &service::factory('voa_s_cyadmin_enterprise_newprofile');
		$profile_list = $serv_profile->list_by_conds(array('ca_id' => $post['ca_id_form']));
		if (empty($profile_list)) {
			$error = array('errcode' => '10001', 'errmsg' => '被迁移人没有可移动的客户!');

			return false;
		}
		$ep_ids = array_column($profile_list, 'ep_id');

		// 更换负责人
		$serv_profile->update_by_conds(array('ca_id' => $post['ca_id_form']), array('ca_id' => $post['ca_id_to']));
		// 写入操作记录表的数据
		$in = array(
			'ca_id_t' => $post['ca_id_form'],
			'ca_id_h' => $post['ca_id_to'],
			'operator' => $post['operator'],
			'ep_ids' => $ep_ids,
		);
		$this->__change_lead_then_record($in);

		return true;
	}

	/**
	 * 变更负责人后, 记录操作表
	 * @param $in // 企业ID 操作人名称 变更前的负责人id (ca_id_t) 变更后的(ca_id)
	 * @return bool
	 */
	private function __change_lead_then_record($in) {

		// 匹配管理员名称
		$admin_data = voa_h_cache::get_instance()->get('adminer', 'cyadmin');

		$ca_id_changed = ''; // 更改后的 负责人名称
		$ca_id_name = ''; // 更改前的 负责人名称
		foreach ($admin_data as $k => $v) {
			if ($v['ca_id'] == $in['ca_id_h']) {
				$ca_id_changed = $v['ca_realname'];
			}
			if ($v['ca_id'] == $in['ca_id_t']) {
				$ca_id_name = $v['ca_realname'];
			}
		}
		$remark = '【' . $in['operator'] . '】迁移了负责人:由【' . $ca_id_name . '】变为【' . $ca_id_changed . '】';

		// 记录操作
		$serv_record = &service::factory('voa_s_cyadmin_company_operationrecord');
		foreach ($in['ep_ids'] as $_ep_id) {
			$temp[] = array(
				'ep_id' => $_ep_id,
				'operator' => $in['operator'],
				'remark' => $remark,
				'ca_id_q' => $in['ca_id_t'],
				'ca_id_h' => $in['ca_id_h'],
			);
		}
		if (!empty($temp)) {
			$serv_record->insert_multi($temp);
		}

		return true;
	}

}
