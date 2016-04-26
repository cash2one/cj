<?php

/**
 * voa_c_cyadmin_enterprise_account_view
 * 帐号详情查看
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_enterprise_account_view extends voa_c_cyadmin_enterprise_base {

	public function execute() {

		// 获取数据
		$serv = &service::factory('voa_s_cyadmin_enterprise_account');
		$acid = $this->request->get('acid');
		if (empty($acid)) {
			$this->message('error', '没有获取到详情数据');
		}

		// 赋值当前的tabs标签
		$get_data = $this->request->getx();
		if (isset($get_data['act']) && !empty($get_data['act'])) {
			$this->view->set('act', $get_data['act']);
		}

		// 获取数据
		$activity = $serv->get($acid);
		if (empty($activity)) {
			$this->message('error', '没有获取到详情数据');
		}
		$activity['updated'] = rgmdate($activity['updated'], 'Y-m-d H:i');
		if ($activity['pay_time'] != 0) {
			$activity['pay_time'] = rgmdate($activity['pay_time'], 'Y-m-d H:i');
		}

		// 获取关联的代理设置
		$is_client = false;
		list($total, $multi, $list) = $this->_search($acid, $is_client);
		foreach ($list as $k => &$v) {
			$v['updated'] = rgmdate($v['updated'], 'Y-m-d H:i');
		}
		$this->_merge_relation_array($list, $this->_adminer_data, 'ca_id', 'ca_id');
		$this->view->set('agant_list', $list);
		$this->view->set('agant_total', $total);
		$this->view->set('agant_multi', $multi);

		// 获取关联的代理客户
		$is_client = true;
		list($client_total, $client_multi, $client_list) = $this->_search($acid, $is_client);
		foreach ($client_list as $k => &$v) {
			$v['ep_created'] = rgmdate($v['ep_created'], 'Y-m-d H:i');
		}
		$this->view->set('client_list', $client_list);
		$this->view->set('client_total', $client_total);
		$this->view->set('client_multi', $client_multi);

		// 导出CSV文件
		if ($this->request->get('export') == 'export') {
			$serv = &service::factory('voa_s_cyadmin_enterprise_newprofile');
			$list = $serv->list_by_conds(array('ep_agent' => $acid));
			$total = $serv->count_by_conds(array('ep_agent' => $acid));
			if (empty($list)) {
				$this->message('error', '没有获取到详情数据');
			}
			$this->__putout($total, $list);
		}

		// 当前地址
		$sets = voa_h_cache::get_instance()->get('setting', 'cyadmin');
		$scheme = config::get('voa.oa_http_scheme');
		$url = $scheme . $sets['domain'];

		// 生成通讯密钥
		$key = config::get('voa.rpc.client.auth_key');
		$timestamp = startup_env::get('timestamp');
		$en_key = authcode(authcode($timestamp, $key, 'ENCODE'), $key, 'ENCODE');
		$this->view->set('en_key', $en_key);

		//展示数据
		$this->view->set('users', $this->_adminer_data); // 管理员ID
		$this->view->set('url', $url);
		$this->view->set('acid', $acid);
		$this->view->set('activity', $activity);
		$this->output('cyadmin/enterprise/account/view');
	}

	/**
	 * 搜索
	 * @param     $acid      关联ID
	 * @param int $perpage
	 * @param     $is_client 是否是代理客户 用
	 * @return array
	 */
	protected function _search($acid, $is_client, $perpage = 12) {
		$list = array();
		$multi = null;
		//查询条件
		if ($is_client) {
			$conds = array('ep_agent = ?' => $acid);
		} else {
			$conds = array('acid = ?' => $acid);
		}
		//获取数据
		if ($is_client) {
			$serv = &service::factory('voa_s_cyadmin_enterprise_newprofile');
			$pay_setting = &service::factory('voa_s_cyadmin_company_paysetting');
		} else {
			$serv = &service::factory('voa_s_cyadmin_enterprise_agant');
		}
		$total = $serv->count_by_conds($conds);
		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true,
			);
			if ($is_client) {
				$this->request->set_params(array('act' => 'acting'));
			} else {
				$this->request->set_params(array('act' => 'proxy'));
			}
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);

			$page_option[0] = $pagerOptions['start'];
			$page_option[1] = $perpage;
			if (!$is_client) {
				$orderby['updated'] = 'DESC';
			}

			if ($is_client) {
				$list = $serv->list_by_conds($conds, $page_option);
				/*
				 * 获取代理客户的套件使用情况
				 */
				if (!empty($list)) {
					$list_conds = array();
					foreach ($list as $k => $v) {
						$list_conds[] = $v['ep_id'];
					}
					$pay_list = $pay_setting->list_by_conds(array('ep_id' => $list_conds));
					// 加上套件名称
					if (!empty($pay_list)) {
						foreach ($pay_list as $_key => &$_val) {
							foreach ($this->_domain_plugin_list as $__k => $__v) {
								// 排除 试用.定制.部署 的命名
								if (in_array($_val['pay_status'], array(
										'1',
										'2',
										'3'
									)) && $_val['pay_type'] == 1 && $_val['cpg_id'] == $__v['cpg_id']
								) {
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
				}
			} else {
				$list = $serv->list_by_conds($conds, $page_option, $orderby);
			}
		}

		return array($total, $multi, $list);
	}

	/**
	 * 导出CSV文件
	 * @param $total
	 * @param $out
	 * @return bool
	 */
	private function __putout($total, $list) {
		if (!$total) {
			$this->message('error', '没有数据！');
		}
		$limit = 1000;
		$zip = new ZipArchive();
		$path = voa_h_func::get_sitedir() . 'excel/';
		$zipname = $path . 'enterprise' . rgmdate('YmdHis', startup_env::get('timestamp'));
		// 读取数据
		$page = ceil($total / $limit);
		$data = array();
		$result = null;
		if (!file_exists($zipname)) {
			$zip->open($zipname . '.zip', ZipArchive::CREATE);
			for ($i = 1; $i <= $page; $i ++) {
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
			'联系人姓名',
			'联系人电话',
			'邮箱',
			'是否绑定',
			'状态',
			'付费金额',
			'注册时间'
		);

		// 获取关联的付费记录
		$pay_setting = &service::factory('voa_s_cyadmin_company_paysetting');
		$pay_conds = array();
		if (!empty ($list)) {
			foreach ($list as $k => $v) {
				$pay_conds[] = $v['ep_id'];
			}
		}
		$pay_list = $pay_setting->list_by_conds(array('ep_id' => $pay_conds));
		$this->_merge_pay_list($list, $pay_list);

		foreach ($list as $k => $val) {
			$pay_status_temp = array();
			// 如果是标准产品, 标示付费状态, 否则就是定制服务 或者 私有部署
			if (isset($val['pay_list']) && $val['pay_type'] == 1 && !empty($val['pay_list'])) {
				foreach ($val['pay_list'] as $_k => $_v) {
					$pay_status_temp[] = empty($_v['cpg_name']) ? '' : $_v['cpg_name'];
					switch ($_v['pay_status']) {
						case 1:
							$pay_status_temp[] = '已付费';
							break;
						case 2:
							$pay_status_temp[] = '已付费-即将到期';
							break;
						case 3:
							$pay_status_temp[] = '已付费-已到期';
							break;
						case 5:
							$pay_status_temp[] = '试用期-即将到期';
							break;
						case 6:
							$pay_status_temp[] = '试用期-已到期';
							break;
						case 7:
							$pay_status_temp[] = '试用期';
							break;
					}
				}
				$pay_status_temp = implode(" ", $pay_status_temp);
			} elseif (isset($val['pay_list']) && $val['pay_type'] == 2 && !empty($val['pay_list'])) {
				$pay_status_temp = '定制服务 已付费';
			} elseif (isset($val['pay_list']) && $val['pay_type'] == 3 && !empty($val['pay_list'])) {
				$pay_status_temp = '私有部署 已付费';
			}

			$temp = array(
				'ep_name' => $val['ep_name'],
				'ep_contact' => $val['ep_contact'],
				'ep_mobilephone' => $val['ep_mobilephone'],
				'ep_email' => $val['ep_email'],
				'ep_wxcorpid' => $val['ep_wxcorpid'] == '' ? '未绑定' : '已绑定',
				'pay_status' => empty($pay_status_temp) ? ' ' : $pay_status_temp,
				'ep_money' => $val['ep_money'],
				'ep_created' => rgmdate($val['ep_created'], 'Y-m-d H:i')
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
	 * 企业信息合并 企业付费信息
	 * @return bool
	 */
	protected function _merge_pay_list(&$list, $pay_list) {
		if (!empty($list) && !empty($pay_list)) {
			// 加上套件名称
			foreach ($pay_list as $_key => &$_val) {
				foreach ($this->_domain_plugin_list as $__k => $__v) {
					// 排除 试用.定制.部署 的命名
					if (in_array($_val['pay_status'], array(
							'1',
							'2',
							'3'
						)) && $_val['pay_type'] == 1 && $_val['cpg_id'] == $__v['cpg_id']
					) {
						$_val['cpg_name'] = $__v['cpg_name'];
					}
				}
			}
			// 合并
			foreach ($list as $k => &$v) {
				foreach ($pay_list as $_k => $_v) {
					if ($v['ep_id'] == $_v['ep_id'] && $v['pay_type'] == $_v['pay_type']) {
						$v['pay_list'][] = $_v;
					}
				}
			}
		}

		return true;
	}


}
