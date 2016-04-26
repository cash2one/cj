<?php
/**
 * Created by PhpStorm.
 * 试用期导出
 * User: zhoutao
 * Date: 15/12/11
 * Time: 下午6:36
 */

class voa_c_cyadmin_enterprise_export_trial extends voa_c_cyadmin_enterprise_base {

	// 套件名称
	protected $_cpg_name = array(
		1 => '微信OA',
		2 => '销售管理',
		3 => '门店管理',
		4 => '团队协作',
		5 => '企业文化',
		6 => '新销售管理',
		7 => '企业消息',
	);

	public function execute() {

		if ($this->_is_post()) {

			$post = $this->request->postx();

			// 查询条件整理
			$conds = $this->_export_conds($post);

			$serv_trial = &service::factory('voa_s_cyadmin_company_trial');
			$serv_profile = &service::factory('voa_s_cyadmin_enterprise_newprofile');

			// 数据数量
			$total = $serv_trial->count_by_conds($conds);
			if (empty($total)) {
				$this->_error_message('没有可以导出的数据');
			}
			$limit = 500; // 导出的每页数量
			$times = ceil($total / $limit); // 导出的文件数

			$zip = new ZipArchive();
			$path = voa_h_func::get_sitedir() . 'excel/';
			rmkdir($path);
			$zipname = $path . 'enterprise' . date('YmdHis', time());

			if (!file_exists($zipname)) {
				$zip->open($zipname . '.zip', ZipArchive::CREATE);

				// 分文件导出
				$list = array();
				for($i = 1; $i <= $times; $i ++) {

					$pagerOptions = array(
						'total_items' => $total,
						'per_page' => $limit,
						'current_page' => $i,
					);
					pager::resolve_options($pagerOptions);

					// 查询延期记录
					$list = $serv_trial->list_by_conds($conds, array($pagerOptions['start'], $limit));
					// 相关的企业ID
					$ep_ids = array_column($list, 'ep_id');
					$ep_data = $serv_profile->list_by_conds(array('ep_id IN (?)' => $ep_ids));

					// 匹配名称
					foreach ($list as $_key => $_trial_data) {
						foreach ($ep_data as $_data) {
							if ($_trial_data['ep_id'] == $_data['ep_id']) {
								$list[$_key]['ep_name'] = $_data['ep_name'];
								break;
							}
						}
					}
				}

				// 生成csv文件
				$result = $this->__create_csv($list, $i, $path);
				if ($result) {
					$zip->addFile($result, $i . '.csv');
				}
			}

			// 下载
			$zip->close();
			$this->__put_header($zipname . '.zip');
			$this->__clear($path);

		}


		$this->view->set('form_url', $this->cpurl($this->_module, $this->_operation));

		$this->output('cyadmin/company/trialexport');

		return true;
	}

	/**
	 *
	 * 查询条件整理
	 * @param array $post 提交的条件
	 * @return array $conds 查询条件
	 */
	protected function _export_conds($post) {

		$conds = array();
		if (!empty($post['date_start']) && !empty($post['date_end'])) {
			$conds['created > ?'] = rstrtotime($post['date_start']);
			$conds['created < ?'] = rstrtotime($post['date_end']);
		} elseif (!empty($post['date_start']) && empty($post['date_end'])) {
			$conds['created > ?'] = rstrtotime($post['date_start']);
		} elseif (!empty($post['date_end']) && empty($post['date_start'])) {
			$conds['created < ?'] = rstrtotime($post['date_end']);
		} elseif (empty($post['date_end']) && empty($post['date_start'])) {
			$this->_error_message('试用期延期操作时间条件不得全为空');
		}

		return $conds;
	}

	/**
	 * 生成csv文件
	 * @param $list
	 * @param $i
	 * @param $path
	 * @return string
	 */
	private function __create_csv($list, $i, $path) {

		if (!is_dir($path)) {
			rmkdir($path, '0777');
		}
		$data = array();
		$filename = $i . '.csv';
		$data[0] = array(
			'企业ID',
			'企业名称',
			'套件ID',
			'套件名称',
			'开始时间',
			'截止时间',
			'延期天数',
			'操作人',
			'操作时间',
		);
		// 内容赋值
		foreach ($list as $val) {

			$temp = array();
			$temp['ep_id'] = $val['ep_id'];
			$temp['ep_name'] = !empty($val['ep_name']) ? $val['ep_name'] : '空';
			$temp['cpg_id'] = $val['cpg_id'];
			$temp['cpg_name'] = $this->_cpg_name[$val['cpg_id']];
			$temp['start'] = rgmdate($val['start_time'], 'Y-m-d H:i');
			$temp['end'] = rgmdate($val['end_time'], 'Y-m-d H:i');
			$temp['extended'] = $val['extended'] . '天';
			$temp['operator'] = $val['operator'];
			$temp['operat_time'] = rgmdate($val['created']);

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

}
