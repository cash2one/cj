<?php
// 导出数据
class voa_c_cyadmin_enterprise_company_export extends voa_c_cyadmin_enterprise_base {

	public function execute() {

		// 获取页码
		$page = $this->request->get('page', 1);

		// 获取条件
		$post = $this->request->getx();
		// 去除页码
		if (!empty($post['page'])) {
			unset($post['page']);
		}

		// 设置每个文件保存的数据个数
		$limit = 1000;

		// 开始日期
		$date_start = $post['date_start'];
		if ($date_start) {
			$date_start = rstrtotime($date_start);
		}

		// 结束日期
		$date_end = $post['date_end'];
		if ($date_end) {
			$date_end = rstrtotime($date_end);
			if ($post['date_start'] == $post['date_start']) {
				$date_end = $date_end + 86400 - 1;
			}
		}

		unset($post['date_start'], $post['date_end']);

		// 如果条件存在，就开始组合
		// 组合条件
		$condi = array();
		if ($post) {

			// 组合条件
			$this->__parse_conds($condi, $post);
		}
		// 根据条件获取总个数
		$total = $this->_serv_profile->count_by_conditions($condi, $date_start, $date_end);
		if (!$total) {
			$this->message('error', '没有数据！');
			return false;
		}

		// 获取总文件个数
		$num = ceil($total / $limit);

		// 开始导出数据

		// 获取数据
		$conlist = array();
		$conlist = $this->_serv_profile->fetch_by_conditions($condi, ($page - 1), $limit, $date_start, $date_end);

		// 生成表格文件，并把文件压缩

		if($num != $page && $page < $num){
			$this->__addzip($conlist, $page);
		}elseif($num == $page){

			//如果是最后一个文件就下载
			$zipname = '';
			$path = '';
			list($zipname,$path) = $this->__addzip($conlist, $page);
			$this->__put_header($zipname);
			$this->__clear($path);


		}

		//传递变量
		//返回URL
		$list_url = $this->cpurl($this->_module, $this->_operation, 'list',array());
		//传递变量
		$this->view->set('list_url',$list_url);
		$this->view->set('total',$total);
		$this->view->set('num',$num);
		$this->view->set('page',$page);

		//输出模板
		$this->output('cyadmin/company/export');
	}


	/**
	 * @param array $conlist
	 * @param int $page
	 * @return bool
	 * */
	private function __addzip($conlist, $page) {

		$zip = new ZipArchive();
		$path = voa_h_func::get_sitedir() . 'excel/';
		$zipname = $path . 'enterprise';
		// 读取数据
		$data = array();
		$result = null;
		if (!file_exists($zipname)) {
			$zip->open($zipname . '.zip', ZipArchive::CREATE);
		}
		// 格式化列表输出
		if (!empty($conlist) && is_array($conlist)) {
			foreach ($conlist as &$_ca) {
				$_ca = $this->_profile_format($_ca);
			}

			if (isset($_ca)) {
				unset($_ca);
			}
		}
		$result = $this->__create_csv($conlist, $page, $path);
		if ($result) $zip->addFile($result, $page . '.csv');


		$zip->close();
		$zipname = $zipname . '.zip';
		return array($zipname,$path);
	}

	/**
	 * 生成csv文件
	 * @param array $list
	 * @param int $i
	 * @param string $path
	 * @return string
	 */
	private function __create_csv($list, $i, $path) {
		if (!is_dir($path))
			mkdir($path, '0777');
		$data = array();
		$temp = array();
		$filename = $i . '.csv';
		$data[0] = array(
			'注册日期',
			'公司名称',
			'公司域名',
			'行业',
			'注册邮箱',
			'Corpid',
			'联系人',
			'联系电话',
			'来源',
			'来源IP',
			'来源地址',
			'状态'
		);
		foreach ($list as $val) {
			$temp = array(
				'_created' => $val['_created'],
				'ep_name' => $val['ep_name'],
				'ep_domain' => $val['ep_domain'],
				'ep_industry' => $val['ep_industry'],
				'ep_email' => $val['ep_email'],
				'ep_wxcorpid' => $val['ep_wxcorpid'],
				'ep_adminrealname' => $val['ep_adminrealname'],
				'ep_mobilephone' => $val['ep_mobilephone'],
				'ep_ref' => $val['ep_ref'],
				'ep_fromip' => $val['ep_fromip'],
				'ep_fromadress' => $val['ep_fromadress'],
				'ep_locked_text' => $val['ep_locked_text']
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
		if (!file_exists($zipname))
			exit("下载失败");
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
	 * 整合条件
	 *
	 * @param array $conds
	 * @param array $post
	 * @return bool
	 *
	 */
	private function __parse_conds(&$condi, $post) {

		$profile = $post['profile'];

		// 处理公司信息
		foreach ($profile as $k => $v) {
			if ('' != trim($v)) {
				$condi[$k] = array("%$v%", 'like');
			}
		}

		// 处理企业状态
		$finish = $post['finish'];
		if ($finish) {

			$condi[$finish] = 1;
		}

		// 查看企业号ID
		$ep_wxcorpid = $post['ep_wxcorpid'];

		// 企业来源
		$ep_ref = $post['ep_ref'];
		$con['ep_wxcorpid'] = $ep_wxcorpid;
		$con['ep_ref'] = $ep_ref;

		// 查看企业ID是否存在，组合条件
		if ($ep_wxcorpid && $ep_wxcorpid == self::EP_TRUE) {
			$condi['ep_wxcorpid'] = array('', "<>");
		}

		// 查看企业ID是否存在，组合条件
		if ($ep_wxcorpid && $ep_wxcorpid == self::EP_FALSE) {
			$condi['ep_wxcorpid'] = array('', "=");
		}

		// 查看企业来源是否存在，组合条件
		if ($ep_ref && $ep_ref == self::EP_TRUE) {
			$condi['ep_ref'] = array('', "<>");
		}

		// 查看企业来源是否存在，组合条件
		if ($ep_ref && $ep_ref == self::EP_FALSE) {
			$condi['ep_ref'] = array('', "=");
		}

		// 是否开始
		$nobeginning = $post['nobeginning'];
		if ($nobeginning) {

			$condi[$nobeginning] = 0;
		}

		return true;
	}

}
