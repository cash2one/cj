<?php

/**
 * list.php
 * 帐号管理列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_enterprise_account_list extends voa_c_cyadmin_enterprise_base {


	public function execute() {

		/** 搜索默认值 */
		$searchDefaults = array(
			'id_number' => '',
			'co_name' => '',
			'link_name' => '',
			'date_start' => '',
			'date_end' => '',
			'link_phone' => '',
			'ca_id' => '',
			'pay_status' => '',
		);
		$issearch = $this->request->get('issearch') ? 1 : 0;

		list($total, $multi, $list, $searchBy) = $this->_search($issearch, $searchDefaults);

		// 导出CSV文件
		if ($this->request->get('export') == 'export') {
			$this->_search_conds($conds, $searchBy);
			$this->__putout($total, $conds);
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

		$this->view->set('users', $this->_adminer_data);
		$this->view->set('url', $url);
		$this->view->set('list', $list);
		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('searchBy', $searchBy);
		$this->view->set('issearch', $issearch);
		$this->view->set('form_url', $this->cpurl($this->_module, $this->_operation, $this->_subop));
		$this->view->set('view_url_base', $this->cpurl($this->_module, $this->_operation, 'view', array('acid' => '')));
		$this->view->set('list_url_base', $this->cpurl($this->_module, $this->_operation, 'list'));

		return $this->output('cyadmin/enterprise/account/list');

	}

	/**
	 * 导出CSV文件
	 * @param $total
	 * @param $out
	 * @return bool
	 */
	private function __putout($total, $conds) {

		$serv = &service::factory('voa_s_cyadmin_enterprise_account');
		$list = $serv->list_by_conds($conds);

		// 获取关联的 销售人员名字
		$this->_merge_relation_array($list, $this->_adminer_data, 'ca_id', 'ca_id');

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
			'代理编号',
			'公司名称',
			'代理区域',
			'联系人姓名',
			'联系人电话',
			'邮箱',
			'跟进销售',
			'付费状态',
			'代理成交额',
			'提交时间',
		);

		foreach ($list as $k => $val) {
			$temp = array(
				'id_number' => $val['id_number'],
				'co_name' => $val['co_name'],
				'area' => $val['area'],
				'link_name' => $val['link_name'],
				'link_phone' => $val['link_phone'],
				'email' => $val['email'],
				'ca_id' => empty($val['ca_realname']) ? '无' : $val['ca_realname'],
				'pay_status' => $val['pay_status'] == 1 ? '未付费' : '已付费',
				'money' => $val['money'],
				'updated' => rgmdate($val['updated'], 'Y-m-d H:i'),
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
	 * 搜索
	 * @param int   $issearch
	 * @param array $searchDefaults
	 * @param int   $perpage
	 * @return array
	 */
	protected function _search($issearch = 0, $searchDefaults = array(), $perpage = 12) {
		/** 搜索条件 */
		$searchBy = array();
		$conds = array();
		if ($issearch) {
			//查询条件
			$getx = $this->request->getx();
			if (!empty($searchDefaults)) {
				foreach ($searchDefaults AS $_k => $_v) {
					if (isset($getx[$_k]) && $getx[$_k] != $_v) {
						if ($getx[$_k] != null) {
							$searchBy[$_k] = $getx[$_k];
						} else {
							$searchBy[$_k] = $_v;
						}
					}
				}
				$searchBy = array_merge($searchDefaults, $searchBy);
			}
		} else {
			$searchBy = $searchDefaults;
		}

		//组合搜索条件
		if (!empty($searchBy)) {
			$this->_search_conds($conds, $searchBy);
		}

		$list = array();
		$multi = null;
		//获取数据

		$serv = &service::factory('voa_s_cyadmin_enterprise_account');
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

			$page_option[0] = $pagerOptions['start'];
			$page_option[1] = $perpage;
			$orderby['updated'] = 'DESC';

			$list = $serv->list_by_conds($conds, $page_option, $orderby);

			$this->_merge_relation_array($list, $this->_adminer_data, 'ca_id', 'ca_id');

			// 处理时间格式
			foreach ($list as $k => &$v) {
				$v['updated'] = rgmdate($v['updated'], 'Y-m-d H:i');
			}
		}

		return array($total, $multi, $list, $searchBy);

	}

	/**
	 * 搜索条件
	 * @param int conds
	 * @param array searchBy
	 */
	protected function _search_conds(&$conds, $searchBy) {

		if (!empty($searchBy['id_number'])) {//代理编号
			$conds["id_number like ?"] = "%" . $searchBy['id_number'] . "%";
		}
		if (!empty($searchBy['co_name'])) {//公司名称
			$conds["co_name like ?"] = "%" . $searchBy['co_name'] . "%";
		}
		if (!empty($searchBy['link_name'])) {//联系人姓名
			$conds["link_name like ?"] = "%" . $searchBy['link_name'] . "%";
		}
		if (!empty($searchBy['link_phone'])) {//联系人手机
			$conds["link_phone like ?"] = "%" . $searchBy['link_phone'] . "%";
		}
		if (!empty($searchBy['ca_id'])) {//跟进销售
			$conds["ca_id "] = $searchBy['ca_id'];
		}
		if (!empty($searchBy['pay_status'])) {//付费状态
			$conds["pay_status like ?"] = "%" . $searchBy['pay_status'] . "%";
		}
		if (!empty($searchBy['date_start'])) {//提交时间的开始
			$searchBy['date_start'] = rstrtotime($searchBy['date_start']);
			$conds["updated >"] = $searchBy['date_start'];
		}
		if (!empty($searchBy['date_end'])) {//提交时间的结束
			$searchBy['date_end'] = rstrtotime($searchBy['date_end']);
			$conds["updated <"] = $searchBy['date_end'];
		}

		return true;
	}

}
