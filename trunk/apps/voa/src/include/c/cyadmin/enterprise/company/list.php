<?php

class voa_c_cyadmin_enterprise_company_list extends voa_c_cyadmin_enterprise_base {

	/** 职位 */
	const ZHUGUAN = 1;
	const XIAOSHOU = 2;
	/** 锁定or未锁定 */
	const LOCKED = 1;
	const UNLOCKED = 0;
	/** limit */
	const LIMIT = 12;
	const MAX_LIMIT = 500;

	public function execute() {

		// 锁定 解锁
		if ($this->request->get('act') == 'lock' && $this->request->get('id')) {

			$id = (int)$this->request->get('id');
			$this->_save_profile(array(
				'ep_locked' => self::LOCKED,
			), $id);
			$this->redirect($this->cpurl($this->_module, $this->_operation, 'list'));
		} elseif ($this->request->get('act') == 'unlock' && $this->request->get('id')) {
			$id = (int)$this->request->get('id');
			$this->_save_profile(array(
				'ep_locked' => self::UNLOCKED,
			), $id);
			$this->redirect($this->cpurl($this->_module, $this->_operation, 'list'));
		}
		$cust = $this->request->get('cust');
		// 默认搜索数据
		$searchDefaults = array(
			'ep_name' => '', // 企业名称
			'ep_industry' => '', // 所在行业
			'customer_status' => '', // 客户状态
			'ep_customer_level' => '', // 客户等级
			'ep_ref' => '', // 客户来源
			'ep_mobilephone' => '', // 联系人手机
			'id_number' => '', // 代理编号
			'pay_type' => '', // 付费类型
			'pay_status' => '', // 付费状态
			'cpg_id' => '', // 套件ID
			'ep_wxcorpid' => '', // 是否绑定
			'date_start' => '', // 注册时间 区间
			'date_end' => '',
			'operation_date_start' => '', // 最后一次操作 时间区间
			'operation_date_end' => '',
			'ca_id' => '',//负责人
		);

		// 合并条件
		$issearch = $this->request->get('issearch') ? 1 : 0;
		$this->__search_conds($issearch, $searchDefaults, $conds, $searchBy);

		// 导出操作
		if ($this->request->post('export') == 'export' || $this->request->get('export') == 'export') {
			// 权限判断条件
			if ($this->_user['ca_job'] == 2) {
				// 销售人员只能查看自己的客户数据
				$conds['ca_id'] = $this->_user['ca_id'];
			} elseif ($this->_user['ca_job'] == 1) {
				$cust = $this->request->post('cust');
				if (isset($cust) && !empty($cust)) {
					switch ($cust) {
						case 'mine' :
							// 只找自己的
							$conds['ca_id'] = $this->_user['ca_id'];
							break;
						case 'under' :
							// 找出下属
							$this->__find_under($un_ids);
							$conds['ca_id IN (?)'] = $un_ids;
							break;
					}
				}
			}

			$this->__putout($conds);
		}

		// 赋值当前的tabs标签
		$get_tabs = $this->request->get('cust');
		if (isset($get_tabs) && !empty($get_tabs)) {
			$this->view->set('cust', $get_tabs);
		}
		// 每页数量
		$limit = (int)$this->request->get('limit');
		$limit = abs($limit);
		if (empty($limit) || $limit >= self::MAX_LIMIT) {
			$limit = self::LIMIT;
		}

		// 获取搜索条件 结果
		list($total, $multi, $list, $searchBy, $limit) = $this->_search($conds, $searchBy, $limit);

		// 获取应用通知
		if ($this->request->get('get_app_notification')) {
			$results = $this->_get_notification_app();
			echo rjson_encode($results);
			exit();
		}

		// 发送消息,通讯密钥
		$key = config::get('voa.rpc.client.auth_key');
		$timestamp = startup_env::get('timestamp');
		$en_key = authcode(authcode($timestamp, $key, 'ENCODE'), $key, 'ENCODE');
		$this->view->set('en_key', $en_key);

		// 获取消息模板
		$this->_message_list($message);
		$this->view->set('data1', $message[0]);
		$this->view->set('multi1', $message[1]);
		$this->view->set('total1', $message[2]);

		// 当前地址
		$sets = voa_h_cache::get_instance()->get('setting', 'cyadmin');
		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme . $sets['domain'];
		$this->view->set('url', $url);

		// 获取所有代理商信息
		if (!empty($this->_all_agent)) {
			$this->view->set('all_agent', $this->_all_agent);
		}
		// 负责人
		$this->view->set('leader', $this->_adminer_data);
		// 套件信息
		$this->view->set('taojian', $this->_domain_plugin_list);
		$this->view->set('cust', $cust);
		$this->view->set('pay_status', $this->_pay_status); // 付费状态
		$this->view->set('scale', $this->_scale); // 规模
		$this->view->set('industry', $this->_industry); // 行业
		$this->view->set('customer_status', $this->_customer_status); // 客户状态
		$this->view->set('customer_level', $this->_customer_level); // 客户等级
		$this->view->set('operator', $this->_user['ca_realname']); // 操作人
		$this->view->set('ca_id', $this->_user['ca_id']); // 当前管理员ID
		$this->view->set('searchBy', $searchBy); // 搜索條件
		$this->view->set('form_url', $this->cpurl($this->_module, $this->_operation));
		$this->view->set('list', $list);
		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('limit', $limit);
		$this->view->set('controler', $this->controller_name); // 当前模块
		//权限
		$this->view->set('job', $this->_user['ca_job']);
		// 编辑基础链接
		$this->view->set('view_url_base', $this->cpurl($this->_module, $this->_operation, 'view', array(
			'id' => '',
		)));
		$this->view->set('lock_url_base', $this->cpurl($this->_module, $this->_operation, 'list', array(
			'act' => 'lock',
			'id' => '',
		)));
		$this->view->set('unlock_url_base', $this->cpurl($this->_module, $this->_operation, 'list', array(
			'act' => 'unlock',
			'id' => '',
		)));

		$this->output('cyadmin/company/list');

		return true;
	}

	/**
	 * 组合搜索条件
	 * @param $issearch
	 * @param $searchDefaults
	 * @param $conds
	 * @param $searchBy
	 * @return bool
	 */
	private function __search_conds($issearch, $searchDefaults, &$conds, &$searchBy) {

		/** 搜索条件 */
		$searchBy = array();
		$conds = array();
		if ($issearch) {
			//查询条件
			$getx = $this->request->getx();
			foreach ($searchDefaults AS $_k => $_v) {
				if (isset($getx[$_k]) && $getx[$_k] != $_v) {
					if (isset($getx[$_k])) {
						$searchBy[$_k] = $getx[$_k];
					} else {
						$searchBy[$_k] = $_v;
					}
				}
			}
			$searchBy = array_merge($searchDefaults, $searchBy);
		} else {
			$searchBy = $searchDefaults;
		}

		//组合搜索条件
		if (!empty($searchBy)) {
			$this->_search_conds($conds, $searchBy);
		}

		return true;
	}

	/**
	 * 导出文件
	 * @param $conds
	 * @return bool
	 */
	private function __putout($conds) {

		$serv = &service::factory('voa_s_cyadmin_enterprise_newprofile');
		$total = $serv->count_by_conds($conds);
		if ($total == 0) {
			$this->_error_message('没有可以导出的数据');
		}

		$zip = new ZipArchive();
		$path = voa_h_func::get_sitedir() . 'excel/';
		rmkdir($path);
		$zipname = $path . 'enterprise' . date('YmdHis', time());

		// $limit 一个文件 分开下载
		$limit = 1000;

		if (!file_exists($zipname)) {
			$zip->open($zipname . '.zip', ZipArchive::CREATE);

			// 页数
			$times = ceil($total/$limit);

			// 根据页数进行导出 就多少个csv文件
			for ($i = 1; $i <= $times; $i ++) {
				$result = null;
				// 分页查询
				$pagerOptions = array(
					'per_page' => $limit,
					'current_page' => $i,
				);
				pager::resolve_options($pagerOptions);
				$pager_options = array($pagerOptions['start'], $limit);
				$orderby = array('ep_created' => 'DESC');

				// 获取数据
				$list = $serv->list_by_conds($conds, $pager_options, $orderby);

				/** 获取关联的付费记录 */
				// 提取epid
				$pay_conds = array_column($list, 'ep_id');
				$pay_setting = &service::factory('voa_s_cyadmin_company_paysetting');
				$pay_list = $pay_setting->list_by_conds(array('ep_id' => $pay_conds));
				// 合并负责人信息
				$this->_merge_relation_array($list, $this->_adminer_data, 'ca_id', 'ca_id');
				// 合并付费信息
				$this->_merge_pay_list($list, $pay_list);
				// 格式化
				foreach ($list as &$_ca) {
					$_ca = $this->_profile_format($_ca);
				}

				// 生成csv文件
				$result = $this->__create_csv($list, $i, $path);
				if ($result) {
					$zip->addFile($result, $i . '.csv');
				}
			}
			$zip->close();
			$this->__put_header($zipname . '.zip');
			$this->__clear($path);

			return false;
		}

		return true;
	}

	/**
	 * 搜索
	 * @param int   $issearch
	 * @param array $searchDefaults
	 * @param int   $perpage
	 * @return array
	 */
	protected function  _search($conds, $searchBy, $perpage = 12) {

		$list = array();
		$multi = null;

		//获取企业数据
		$serv = &service::factory('voa_s_cyadmin_enterprise_newprofile');
		$pay_setting = &service::factory('voa_s_cyadmin_company_paysetting');
		$total = $serv->count_by_conds($conds);

		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true,
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);

			$page_option = array($pagerOptions['start'], $perpage);
			$orderby['ep_created'] = 'DESC';

			$list = $serv->list_by_conds($conds, $page_option, $orderby);
			// 获取关联的付费记录
			$pay_conds = array();
			if (!empty ($list)) {
				foreach ($list as $k => $v) {
					$pay_conds[] = $v['ep_id'];
				}
			}
			$pay_list = $pay_setting->list_by_conds(array('ep_id' => $pay_conds));
			$this->_merge_relation_array($list, $this->_adminer_data, 'ca_id', 'ca_id');
			$this->_merge_pay_list($list, $pay_list);

			// 处理时间格式
			foreach ($list as $k => &$v) {
				$v['ep_created'] = rgmdate($v['ep_created'], 'Y-m-d H:i');
				$v['ep_updated'] = rgmdate($v['ep_updated'], 'Y-m-d H:i');
				// 匹配负责人名称
				foreach ($this->_adminer_data as $_k => $_v) {
					if ($v['ca_id'] == $_v['ca_id']) {
						$v['ca_realname'] = $_v['ca_realname'];
					}
				}
			}
		}

		return array($total, $multi, $list, $searchBy, $perpage);

	}

	/**
	 * 企业信息合并 企业付费信息
	 * @return bool
	 */
	protected function _merge_pay_list(&$list, $pay_list) {

		if (!empty($list) && !empty($pay_list)) {
			// 加上套件名称
			foreach ($pay_list as $_key => &$_val) {
				foreach ($this->_domain_plugin_list as $__k => $__v) {
					if (in_array($_val['pay_status'], array('1', '2', '3', '5', '6', '7',)) && $_val['cpg_id'] == $__v['cpg_id']) {
						$_val['cpg_name'] = $__v['cpg_name'];
					}
				}
			}
			// 合并
			foreach ($list as $k => &$v) {
				foreach ($pay_list as $_k => $_v) {
					if ($v['ep_id'] == $_v['ep_id']) {
						$v['pay_list'][] = $_v;
					}
				}
			}
		}

		return true;
	}

	/**
	 * 搜索条件
	 * @param $conds
	 * @param $searchBy
	 * @return bool
	 */
	protected function _search_conds(&$conds, $searchBy) {

		//权限判断条件
		if ($this->_user['ca_job'] == self::XIAOSHOU) {
			//销售人员只能查看自己的客户数据
			$conds['ca_id'] = $this->_user['ca_id'];
		} elseif ($this->_user['ca_job'] == self::ZHUGUAN) {
			if ($searchBy['ca_id'] == '0') {
				$conds['ca_id '] = '0';
			} else {
				$cust = $this->request->get('cust');
				if (isset($cust) && !empty($cust)) {
					switch ($cust) {
						case 'mine':
							// 只找自己的
							$conds['ca_id'] = $this->_user['ca_id'];
							break;
						case 'under':
							// 找出下属
							$this->__find_under($un_ids);
							$conds['ca_id IN (?)'] = $un_ids;
							break;
					}
				}
			}
		}

		if (!empty($searchBy['ep_name'])) {//公司名称
			$conds["ep_name like ?"] = "%" . $searchBy['ep_name'] . "%";
		}

		if (!empty($searchBy['ep_industry'])) {//行业
			$conds["ep_industry like ?"] = "%" . $searchBy['ep_industry'] . "%";
		}

		if (!empty($searchBy['ep_mobilephone'])) {//联系人手机
			$conds["ep_mobilephone like ?"] = "%" . $searchBy['ep_mobilephone'] . "%";
		}

		if (!empty($searchBy['id_number'])) {//代理商
			$conds["id_number like ?"] = "%" . $searchBy['id_number'] . "%";
		}

		if (!empty($searchBy['ep_wxcorpid'])) {//是否绑定
			if ($searchBy['ep_wxcorpid'] == 1) {
				$conds["ep_wxcorpid NOT IN (?)"] = array('', 'default'); // 1为绑定 则查询不为空
			} else {
				$conds["ep_wxcorpid IN (?)"] = array('', 'default'); // 反之2 查询为空
			}
		}

		if (!empty($searchBy['ep_ref'])) {//来源
			$conds["ep_ref like ?"] = "%" . $searchBy['ep_ref'] . "%";
		}

		if (!empty($searchBy['ca_id'])) {//销售人员
			$conds["ca_id "] = $searchBy['ca_id'];
		}

		if (!empty($searchBy['customer_status'])) {//客户状态
			$conds["customer_status "] = $searchBy['customer_status'];
		}

		if (!empty($searchBy['pay_type'])) {//付费属性
			$conds["pay_type "] = $searchBy['pay_type'];
		}

		if (!empty($searchBy['ep_customer_level'])) {//客户等级
			$conds["ep_customer_level "] = $searchBy['ep_customer_level'];
		}

		if (!empty($searchBy['date_start'])) {//注册时间开始
			$searchBy['date_start'] = rstrtotime($searchBy['date_start']);
			$conds["ep_created >"] = $searchBy['date_start'];
		}

		if (!empty($searchBy['date_end'])) {//注册时间结束
			$searchBy['date_end'] = rstrtotime($searchBy['date_end']);
			$conds["ep_created <"] = $searchBy['date_end'] + 86399;
		}

		if (!empty($searchBy['operation_date_start'])) {//最后操作时间开始
			$searchBy['operation_date_start'] = rstrtotime($searchBy['operation_date_start']);
			$conds["ep_last_operation >"] = $searchBy['operation_date_start'];
		}

		if (!empty($searchBy['operation_date_end'])) {//最后操作时间结束
			$searchBy['operation_date_end'] = rstrtotime($searchBy['operation_date_end']);
			$conds["ep_last_operation <"] = $searchBy['operation_date_end'] + 86399;
		}

		// 如果 付费状态和 购买套件两种搜索条件
		if (!empty($searchBy['pay_status']) && !empty($searchBy['cpg_id'])) {
			$pay_status_list = $this->_serv_paysetting->list_by_conds(array('pay_status' => $searchBy['pay_status']));
			$cpg_list = $this->_serv_paysetting->list_by_conds(array('cpg_id' => $searchBy['cpg_id']));
			// 判断 根据条件 出来的结果 是否为空, 再计算结果ID
			if (!empty($pay_status_list) && !empty($cpg_list)) {
				$intersect_data = array_intersect($pay_status_list, $cpg_list);
				$ep_ids = array_column($intersect_data, 'ep_id');
			} else {
				$conds["ep_id IN (?)"] = '';
			}

			$conds["ep_id IN (?)"] = $ep_ids;
			// 如果只有 付费状态条件
		} elseif (!empty($searchBy['pay_status'])) {
			$pay_status_list = $this->_serv_paysetting->list_by_conds(array('pay_status' => $searchBy['pay_status']));
			if (!empty ($pay_status_list)) {
				$ep_ids = array_column($pay_status_list, 'ep_id');
				$conds["ep_id IN (?)"] = $ep_ids;
			} else {
				$conds["ep_id IN (?)"] = '';
			}
			// 如果只有 套件 条件
		} elseif (!empty($searchBy['cpg_id'])) {
			$cpg_list = $this->_serv_paysetting->list_by_conds(array('cpg_id' => $searchBy['cpg_id']));
			if (!empty($cpg_list)) {
				$ep_ids = array_column($cpg_list, 'ep_id');
				$conds["ep_id IN (?)"] = $ep_ids;
			} else {
				$conds["ep_id IN (?)"] = '';
			}
		}

		if (!empty($searchBy['ep_email'])) {//注册时间结束
			$conds["ep_email like ?"] = "%" . $searchBy['ep_email'] . "%";
		}

		return true;
	}

	/**
	 * 生成csv文件
	 */
	private function __create_csv($list, $i, $path) {

		if (!is_dir($path)) {
			mkdir($path, '0777');
		}
		$data = array();
		$temp = array();
		$filename = $i . '.csv';
		$data[0] = array(
			'公司名称',
			'联系人',
			'手机号',
			'行业',
			'客户状态',
			'客户等级',
			'企业规模',
			'来源',
			'是否绑定',
			'负责人',
			'付费状态',
			'注册日期',
			'最后更新时间',
		);
		foreach ($list as $val) {
			// 如果是标准产品, 标示付费状态, 否则就是定制服务 或者 私有部署
			if (!empty($val['pay_list'])) {
				$pay_status_temp = '';
				foreach ($val['pay_list'] as $_key => $_val) {
					if ($_val['pay_type'] == 1) {
						$pay_status_temp .= empty($_val['cpg_name']) ? '' : $_val['cpg_name'];
						switch ($_val['pay_status']) {
							case 1:
								$pay_status_temp .= ' 已付费 ';
								break;
							case 2:
								$pay_status_temp .= ' 已付费-即将到期 ';
								break;
							case 3:
								$pay_status_temp .= ' 已付费-已到期 ';
								break;
							case 5:
								$pay_status_temp .= ' 试用期-即将到期 ';
								break;
							case 6:
								$pay_status_temp .= ' 试用期-已到期 ';
								break;
							case 7:
								$pay_status_temp .= ' 试用期 ';
								break;
						}
					} elseif ($_val['pay_type'] == 2) {
						$pay_status_temp .= empty($_val['cpg_name']) ? '' : $_val['cpg_name'];
						$pay_status_temp .= ' 定制服务 ';
					} elseif ($_val['pay_type'] == 3) {
						$pay_status_temp .= empty($_val['cpg_name']) ? '' : $_val['cpg_name'];
						$pay_status_temp .= ' 私有部署 ';
					}
				}
			} else {
				$pay_status_temp = '无';
			}

			$temp = array(
				'ep_name' => $val['ep_name'],
				'ep_adminrealname' => $val['ep_adminrealname'],
				'ep_mobilephone' => $val['ep_mobilephone'],
				'ep_industry' => $val['ep_industry'],
				'customer_status' => $this->_customer_status[$val['customer_status']],
				'ep_customer_level' => $this->_customer_level[$val['ep_customer_level']],
				'ep_companysize' => $val['ep_companysize'],
				'ep_ref' => $val['ep_ref'],
				'ep_wxcorpid' => empty($val['ep_wxcorpid']) ? '未绑定' : '已绑定',
				'ca_id' => isset($this->_adminer_data[$val['ca_id']]['ca_realname']) ? $this->_adminer_data[$val['ca_id']]['ca_realname'] : '无',
				'pay_status' => empty($pay_status_temp) ? ' ' : $pay_status_temp,
				'ep_created' => $val['ep_created'],
				'ep_updated' => $val['ep_updated'],
			);

			$data[] = $temp;
		}

		$csv_data = array2csv($data);
		$fp = fopen($path . $filename, 'w');
		fwrite($fp, $csv_data); // 写入数据
		fclose($fp); // 关闭文件句柄

		return $path . $filename;
	}

	/**
	 * 下载输出至浏览器
	 */
	private function __put_header($zipname) {

		if (!file_exists($zipname)) {
			exit("下载失败");
		}
		$file = fopen($zipname, "r");
		Header("Content-type: application/octet-stream");
		Header("Accept-Ranges: bytes");
		Header("Accept-Length: " . filesize($zipname));
		Header("Content-Disposition: attachment; filename=" . basename($zipname));
		echo fread($file, filesize($zipname));
		$buffer = 1024;
		while (!feof($file)) {
			$file_data = fread($file, $buffer);
			echo $file_data;
		}
		fclose($file);
	}

	/**
	 * 清理产生的临时文件
	 */
	private function __clear($path) {

		$dh = opendir($path);
		while ($file = readdir($dh)) {
			if ($file != "." && $file != "..") {
				unlink($path . $file);
			}
		}
	}

	/**
	 * 找出当前人的下属
	 * @param array $result
	 * @return bool
	 */
	private function __find_under(&$result = array()) {
		$serv_sub = &service::factory('voa_s_cyadmin_common_subordinates');
		$under = $serv_sub->list_by_conds(array('ca_id' => $this->_user['ca_id']));
		$un_ids = array();
		if (!empty($under)) {
			foreach ($under as $k => $v) {
				$un_ids[] = $v['un_id'];
			}
			$result = $un_ids;
		}

		return true;
	}

}
