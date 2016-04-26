<?php

/**
 * voa_c_cyadmin_enterprise_company_view
 * 用户管理
 * Created by zhoutao.
 * Created Time: 2015/7/30  15:05
 */
class voa_c_cyadmin_enterprise_company_view extends voa_c_cyadmin_enterprise_base {

	protected $_pay_type = array(
		'private' => 3, // 私有部署
		'custom' => 2  // 定制产品
	);

	public function execute() {

		// 获取ep_id, 不得为空
		$ep_id = $this->request->get('id');
		if (empty($ep_id)) {
			$this->message('error', '缺少必要参数');
		}

		//权限判断条件
		if ($this->_user['ca_job'] == 2) {
			//销售人员只能查看自己的客户数据
			$serv_per = &service::factory('voa_s_cyadmin_enterprise_profile');
			$conds['ca_id'] = $this->_user['ca_id'];
			$per_list = $serv_per->fetch_by_conditions($conds);
			//判断
			if (!isset($per_list[$ep_id])) {
				$this->message('error', '没有权限查看');
			}
			$this->view->set('ca_job', $this->_user['ca_job']);
		}

		// 赋值当前的tabs标签
		$get_data = $this->request->getx();
		if (isset($get_data['act']) && !empty($get_data['act'])) {
			$this->view->set('act', $get_data['act']);
		}

		// 获取企业信息
		$company_data = array();
		$this->_get_ep_data($ep_id, $company_data);

		// 获取所有代理商信息
		if (!empty($this->_all_agent)) {
			$this->view->set('all_agent', $this->_all_agent);
		}
		$this->view->set('customer_status', $this->_customer_status); // 客户状态
		$this->view->set('customer_level', $this->_customer_level); // 客户等级
		$this->view->set('scale', $this->_scale); // 规模
		$this->view->set('industry', $this->_industry); // 行业

		// 应用列表
		$this->view->set('plugin_list', $this->_domain_plugin_list);
		$this->view->set('plugin', $this->_domain_plugin);

		// 当前地址
		$sets = voa_h_cache::get_instance()->get('setting', 'cyadmin');
		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme . $sets['domain'];

		// 获取企业相关信息
		$this->_get_ep_more_data($ep_id);

		// 获取企业应用安装记录
		$this->_get_ep_plugin($company_data, $post, $json_array_data);
		if(!empty($json_array_data)){
			$ep_enable_plugin = $json_array_data['result'];
		}		
		if (!empty ($ep_enable_plugin)) {
			foreach ($ep_enable_plugin as $k => &$v) {
				if ($v['cp_lastopen'] != 0) {
					$v['cp_lastopen'] = rgmdate($v['cp_lastopen'], 'Y-m-d H:i');
				} else {
					$v['cp_lastopen'] = '无';
				}
			}
			$this->view->set('app_install', $ep_enable_plugin);
		}

		// 消息模板
		$this->_get_message_template($post);

		// 企业信息 模板数据
		$this->view->set('operator', $this->_user['ca_realname']); // 当前操作人
		$this->view->set('op_ca_id', $this->_user['ca_id']); // 当前操作人ID
		$this->view->set('ep_id', $ep_id);
		$this->view->set('url', $url);
		$this->view->set('adminer_data', $this->_adminer_data); // 管理员信息

		// 公司详情 和 公司账号
		$company_data['account'] = substr($company_data['ep_domain'], 0, - 13);
		$this->view->set('basic_information', $company_data);

		$this->output('cyadmin/company/view');

		return true;
	}

	/**
	 * 获取消息模板
	 * @param $post 加密密钥
	 * @return bool
	 */
	protected function _get_message_template($post) {

		$news = &uda::factory('voa_uda_cyadmin_enterprise_news');
		$page1 = $this->request->get('page');
		$multi1 = '';
		$msg_list1 = array();
		$total1 = '';
		$news->getlist($page1, $msg_list1, $multi1, $total1);
		$data1 = array();
		$news->format($msg_list1, $data1);
		$this->view->set('en_key', $post['key']);
		$this->view->set('data1', $data1);
		$this->view->set('multi1', $multi1);
		$this->view->set('total1', $total1);

		return true;
	}

	/**
	 * 获取企业相关信息
	 * @param $ep_id 企业ID
	 * @return bool
	 */
	protected function _get_ep_more_data($ep_id) {

		// 获取付费设置信息
		list($pay_total, $pay_multi, $pay_list) = $this->_search($ep_id, 'pay');
		$this->_deal_time($pay_list);
		$this->_merge_cpgid_cpgname($pay_list);

		// 获取销售设置信息
		list($sales_total, $sales_multi, $sales_list) = $this->_search($ep_id, 'sales');
		$this->_deal_time($sales_list);
		$this->_merge_relation_array($sales_list, $this->_adminer_data, 'ca_id', 'ca_id');

		// 获取试用期延长的操作记录
		list($trial_total, $trial_multi, $trial_list) = $this->_search($ep_id, 'trial');
		$this->_merge_cpgid_cpgname($trial_list);
		$this->_deal_time($trial_list);

		// 获取标准产品付费记录
		list($pay_standard_total, $pay_standard_multi, $pay_standard_list) = $this->_search($ep_id, 'pay_standard');
		$this->_deal_time($pay_standard_list);
		$this->_merge_cpgid_cpgname($pay_standard_list);

		// 获取定制产品付费记录
		list($pay_special_total, $pay_special_multi, $pay_special_list) = $this->_search($ep_id, 'pay_special');
		$this->_deal_time($pay_special_list);

		// 获取私有部署付费记录
		list($pay_special_total_private, $pay_special_multi_private, $pay_special_list_private) = $this->_search($ep_id, 'pay_special_private');
		$this->_deal_time($pay_special_list_private);

		// 获取消息记录
		list($message_total, $message_multi, $message_list) = $this->_search($ep_id, 'message');
		$this->_deal_time($message_list);

		// 获取消息记录关联的消息模板内容
		$this->_message_recode($message_list);

		// 获取已经是定制服务 获取 私有部署的套件
		if (!empty($pay_list)) {
			$sp_ids = $this->_get_special_cpg_id($pay_list);
			$this->view->set('sp_ids', $sp_ids);
		}

		// 总和 付费设置信息 销售设置 消息记录
		$total = array(
			'pay_total' => $pay_total,
			'sales_total' => $sales_total,
			'trial_total' => $trial_total,
			'pay_standard_total' => $pay_standard_total,
			'pay_special_total' => $pay_special_total,
			'pay_special_total_private' => $pay_special_total_private,
			'message_total' => $message_total,
		);
		$multi = array(
			'pay_multi' => $pay_multi,
			'sales_multi' => $sales_multi,
			'trial_multi' => $trial_multi,
			'pay_standard_multi' => $pay_standard_multi,
			'pay_special_multi' => $pay_special_multi,
			'pay_special_multi_private' => $pay_special_multi_private,
			'message_multi' => $message_multi,
		);
		$list = array(
			'pay_list' => $pay_list,
			'sales_list' => $sales_list,
			'trial_list' => $trial_list,
			'pay_standard_list' => $pay_standard_list,
			'pay_special_list' => $pay_special_list,
			'pay_special_list_private' => $pay_special_list_private,
			'message_list' => $message_list,
		);

		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('list', $list);

		// 获取操作记录
		$operations = $this->_serv_operationrecord->list_by_conds(array('ep_id' => $ep_id), '', array('created' => 'DESC'));
		$this->_deal_time($operations);
		// 匹配状态
		$this->_merge_status($operations);
		$this->view->set('operations', $operations);

		return true;
	}

	/**
	 * 获取付费列表里的 特殊付费 套件
	 * @param $pay_list
	 * @return array
	 */
	protected function _get_special_cpg_id($pay_list) {

		$sp_ids = array();
		foreach ($pay_list as $k => $v) {
			if ($v['pay_type'] == 2 || $v['pay_type'] == 3) {
				$sp_ids[] = $v['cpg_id'];
			}
		}

		return $sp_ids;
	}

	/**
	 * 匹配 客户状态
	 * @param $operations
	 * @return bool
	 */
	protected function _merge_status(&$operations) {

		if (!empty($operations)) {
			foreach ($operations as $k => &$v) {
				if (!empty($v['customer_status'])) {
					foreach ($this->_customer_status as $_k => $_v) {
						if ($v['customer_status'] == $_k) {
							$v['customer_status'] = $_v;
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * 套件ID 匹配 套件名称
	 * @return bool
	 */
	protected function _merge_cpgid_cpgname(&$list1) {

		foreach ($this->_domain_plugin_list as $__key => $__val) {
			foreach ($list1 as $__K => &$__V) {
				if ($__V['cpg_id'] == $__val['cpg_id']) {
					$__V['cpg_name'] = $__val['cpg_name'];
				}
			}
		}

		return true;
	}

	/**
	 * 获取企业应用安装记录
	 * @param $company_data 企业基本信息
	 * @param $post 加密通讯密钥
	 * @param $json_array_data 返回的数据
	 * @return bool
	 */
	protected function _get_ep_plugin($company_data, &$post, &$json_array_data) {

		$json_array_data = array();
		$scheme = config::get('voa.oa_http_scheme');
		$json_url = $scheme . $company_data['ep_domain'] . "/api/cyadmin/post/plugin"; // 目标地址
		$key = config::get('voa.rpc.client.auth_key');
		$timestamp = startup_env::get('timestamp');
		$post = array(
			'key' => authcode(authcode($timestamp, $key, 'ENCODE'), $key, 'ENCODE'),
		); // 通讯密钥
		voa_h_func::get_json_by_post($json_array_data, $json_url, $post);

		return true;
	}

	/**
	 * 获取企业信息
	 * @param $ep_id
	 * @param $company_data
	 * @return bool
	 */
	protected function _get_ep_data($ep_id, &$company_data) {

		$company_data = array();
		$this->_uda_profile->get_by_id($ep_id, $company_data);
		if (empty($company_data)) {
			$this->message('error', '不存在该企业');
		}

		$company_data['ep_created'] = rgmdate($company_data['ep_created'], 'Y-m-d H:i');
		$company_data['ep_updated'] = rgmdate($company_data['ep_updated'], 'Y-m-d H:i');
		$this->_ep_pay_status($company_data);

		return true;
	}

	/**
	 * 获取企业付费状态
	 * @param $ep_data
	 * @return bool
	 */
	protected function _ep_pay_status(&$ep_data) {
		$serv = &service::factory('voa_s_cyadmin_company_paysetting');
		// 根据ep_id 获取企业信息
		$data = $serv->list_by_conds(array('ep_id' => $ep_data['ep_id']));
		if (!empty($data)) {
			// 处理时间
			$this->_deal_time($data);
			foreach ($data as $k => &$v) {
				foreach ($this->_domain_plugin_list as $_k => $_v) {
					// 获取套件的名字
					if ($v['cpg_id'] == $_v['cpg_id']) {
						$v['cpg_name'] = $_v['cpg_name'];
					}
				}
			}
		}
		// 付费套件
		$ep_data['pay_setting'] = $data;

		return true;
	}

	/**
	 * 查询数据
	 * @param     $ep_id    关联ID
	 * @param     $which 选择 哪个数据库
	 * @param int $perpage
	 * @return array
	 */
	protected function _search($ep_id, $which, $perpage = 12) {

		$list = array();
		$multi = null;
		//查询条件
		$conds = array('ep_id = ?' => $ep_id);

		// 获取数据数
		switch ($which) {
			case 'pay':
				$total = $this->_serv_paysetting->count_by_conds($conds);
				break;
			case 'sales':
				$total = $this->_serv_salessetting->count_by_conds($conds);
				break;
			case 'message':
				$conds = array('epid = ?' => $ep_id);
				$total = $this->_serv_message_log->count_by_conds($conds);
				break;
			case 'trial':
				$total = $this->_serv_trial->count_by_conds($conds);
				break;
			case 'pay_standard':
				$total = $this->_serv_pay_standard->count_by_conds($conds);
				break;
			case 'pay_special':
				$conds = array(
					'ep_id = ?' => $ep_id,
					'pay_type = ?' => $this->_pay_type['custom'],
				);
				$total = $this->_serv_pay_special->count_by_conds($conds);
				break;
			case 'pay_special_private':
				$conds = array(
					'ep_id = ?' => $ep_id,
					'pay_type = ?' => $this->_pay_type['private'],
				);
				$total = $this->_serv_pay_special->count_by_conds($conds);
				break;
		}

		// 如果有数据
		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true,
			);

			// 为分页列表 更换url地址参数
			switch ($which) {
				case 'pay':
					$this->request->set_params(array('act' => 'pay'));
					break;
				case 'sales':
					$this->request->set_params(array('act' => 'sales'));
					break;
				case 'message':
					$this->request->set_params(array('act' => 'message'));
					break;
				case 'trial':
					$this->request->set_params(array('act' => 'trial'));
					break;
			}

			// 分页条件
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);

			$page_option[0] = $pagerOptions['start'];
			$page_option[1] = $perpage;
			$orderby['updated'] = 'DESC';

			// 查询数据
			switch ($which) {
				case 'pay':
					$list = $this->_serv_paysetting->list_by_conds($conds, $page_option, $orderby);
					break;
				case 'sales':
					$list = $this->_serv_salessetting->list_by_conds($conds, $page_option, $orderby);
					break;
				case 'message':
					$list = $this->_serv_message_log->list_by_conds($conds, $page_option, $orderby);
					break;
				case 'trial':
					$list = $this->_serv_trial->list_by_conds($conds, $page_option, $orderby);
					break;
				case 'pay_standard':
					$list = $this->_serv_pay_standard->list_by_conds($conds, $page_option, $orderby);
					break;
				case 'pay_special':
					$list = $this->_serv_pay_special->list_by_conds($conds, $page_option, $orderby);
					break;
				case 'pay_special_private':
					$list = $this->_serv_pay_special->list_by_conds($conds, $page_option, $orderby);
					break;
			}
		}

		return array($total, $multi, $list);
	}

	/**
	 * 合并消息记录 和 消息记录的内容
	 * @param $message_list
	 * @return bool
	 */
	protected function _message_recode(&$message_list) {

		$meid = array();

		if (is_array($message_list) && !empty($message_list)) {
			// 取出消息id
			foreach ($message_list as $k => $v) {
				if (!empty($v['meid'])) {
					$meid['meid'][] = $v['meid'];
				}
			}
			// 查询消息模板内容
			$meid_content = $this->_serv_message->list_by_conds($meid);
			if (!empty($meid_content)) {
				// 匹配消息模板id 并 合并内容
				foreach ($message_list as $key => &$val) {
					foreach ($meid_content as $key1 => $val1) {
						if ($val['meid'] == $key1) {
							$val['content'] = $val1['content'];
						}
					}
				}
			}
		}

		return true;
	}

	/**
	 * 处理数组时间转换
	 * @param $data
	 * @return bool
	 */
	protected function _deal_time(&$data) {

		if (!empty($data)) {
			foreach ($data as $k => &$v) {
				if (!empty($v['updated'])) {
					$v['updated'] = rgmdate($v['updated'], 'Y-m-d H:i');
				}
				if (!empty($v['date_start'])) {
					$v['date_start'] = rgmdate($v['date_start'], 'Y-m-d');
				}
				if (!empty($v['date_end'])) {
					$v['date_end'] = rgmdate($v['date_end'], 'Y-m-d');
				}
				if (!empty($v['start_time'])) {
					$v['start_time'] = rgmdate($v['start_time'], 'Y-m-d H:i');
				}
				if (!empty($v['end_time'])) {
					$v['end_time'] = rgmdate($v['end_time'], 'Y-m-d H:i');
				}
				if (!empty($v['created'])) {
					$v['created'] = rgmdate($v['created'], 'Y-m-d H:i');
				}
			}
		}
		if (!empty($data['ep_created'])) {
			$data['ep_created'] = rgmdate($data['ep_created'], 'Y-m-d H:i');
		}

		return true;
	}
}
