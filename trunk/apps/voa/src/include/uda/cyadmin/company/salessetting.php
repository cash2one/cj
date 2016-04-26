<?php

/**
 * salessetting.php
 * Created by zhoutao.
 * Created Time: 2015/7/31  15:03
 */
class voa_uda_cyadmin_company_salessetting extends voa_uda_cyadmin_base {

	private $__request = '';

	public function take_sure($in, &$out) {

		$data = '';
		if (!$this->_filter($in, $data)) {
			$out = array(
				'errcode' => $this->errcode,
				'errmsg' => $this->errmsg,
			);

			return false;
		}

		// 判断权限
		if (!$this->_authority($data['ep_id'], $data['op_ca_id'])) {
			$out = array(
				'errcode' => $this->errcode,
				'errmsg' => $this->errmsg,
			);

			return false;
		};

		// 提取操作人 和 变更前负责人ID
		$operator = $data['operator'];
		$ca_id_t = $data['ep_ca_id'];
		// 去除不入库数据
		unset($data['operator']);
		unset($data['ep_ca_id']);
		unset($data['op_ca_id']);

		// 入company_salessetting库
		$serv = &service::factory('voa_s_cyadmin_company_salessetting');
		$data = $serv->insert($data);

		if (empty ($data)) {
			$out = array(
				'errcode' => '30000',
				'errmsg' => '入库失败',
			);

			return false;
		}

		// 查询代理商的编号
		$agent_serv = &service::factory('voa_s_cyadmin_enterprise_account');
		$agent_data = $agent_serv->get_by_conds(array('acid' => $data['ep_agent']));

		// 更新enterprise_profile库
		$serv = &service::factory('voa_s_cyadmin_enterprise_newprofile');
		$serv->update_by_conds(array(
			'ep_id' => $in['ep_id'],
		), array(
			'ca_id' => $in['ca_id'],
			'customer_status' => $in['customer_status'],
			'ep_agent' => $in['ep_agent'],
			'id_number' => isset($agent_data['id_number']) && !empty($agent_data['id_number']) ? $agent_data['id_number'] : '',
		));

		// 变更负责人操作记录
		$record_data = array(
			'ep_id' => $in['ep_id'],
			'operator' => $operator,
			'ca_id' => $in['ca_id'],
			'ca_id_t' => $ca_id_t
		);
		$this->_change_lead_then_record($record_data);

		// 变更状态操作记录
		$start_record_data = array(
			'ep_id' => $in['ep_id'],
			'operator' => $operator,
			'customer_status' => $in['customer_status'],
			'remark' => empty($in['sales_remark']) ? '' : $in['sales_remark'],
		);
		if (!$this->_change_customer_status_record($start_record_data, $out)) {
			return false;
		};

		return true;
	}

	protected function _filter($in, &$out) {

		//获取数据
		if (!empty($in)) {
			$data['have_agent'] = $in['have_agent'];
			$data['ep_agent'] = $in['ep_agent'];
			$data['ca_id'] = $in['ca_id'];
			$data['customer_status'] = $in['customer_status'];
			$data['sales_remark'] = $in['sales_remark'];
			$data['ep_id'] = $in['ep_id'];
			$data['operator'] = $in['operator'];
			$data['ep_ca_id'] = $in['ep_ca_id'];
			$data['op_ca_id'] = $in['op_ca_id'];
		} else {
			$this->errmsg('10000', '内容不能为空');

			return false;
		}

		$fields = array(
			'have_agent' => array('have_agent', parent::VAR_INT, null, null, false),
			'ep_agent' => array('ep_agent', parent::VAR_INT, null, null, false),
			'ca_id' => array('ca_id', parent::VAR_INT, null, null, false),
			'customer_status' => array('customer_status', parent::VAR_INT, null, null, false),
			'sales_remark' => array('sales_remark', parent::VAR_STR, null, null, false),
			'ep_id' => array('ep_id', parent::VAR_INT, null, null, false),
			'operator' => array('operator', parent::VAR_STR, null, null, false),
			'ep_ca_id' => array('ep_ca_id', parent::VAR_INT, null, null, false),
			'op_ca_id' => array('op_ca_id', parent::VAR_INT, null, null, false),
		);

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $data)) {
			return false;
		}

		if (empty($this->__request['have_agent'])) {
			$this->errmsg('20001', '请选择有无代理商');

			return false;
		}
		if ($this->__request['have_agent'] == 2 && $this->__request['ep_agent'] == 0) {
			$this->errmsg('20002', '请选择代理商');

			return false;
		}
		if (empty($this->__request['ca_id'])) {
			$this->errmsg('20003', '请选择销售人员');

			return false;
		}
		if (empty($this->__request['customer_status'])) {
			$this->errmsg('20004', '请选择客户状态');

			return false;
		}
		if (empty($this->__request['ep_id'])) {
			$this->errmsg('20005', '缺少必要参数');

			return false;
		}
		if (empty($this->__request['operator'])) {
			$this->errmsg('20006', '丢失操作人数据');

			return false;
		}
		if (empty($this->__request['op_ca_id'])) {
			$this->errmsg('20007', '丢失操作人数ID');

			return false;
		}

		$out = $this->__request;
		unset($this->__request);

		return true;
	}

}
