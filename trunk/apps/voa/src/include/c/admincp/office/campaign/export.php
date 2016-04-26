<?php

/**
 * 企业后台/微办公管理/活动推广/统计数据/导出表格
 * voa_c_admincp_office_campaign_export
 * Create By XiaoDingchen
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_campaign_export extends voa_c_admincp_office_campaign_base {

	public function execute() {
		// 实例化uda层的数据处理
		$uda = new voa_uda_frontend_campaign_total();
		// 获取所导出数据的类型
		$type = $this->request->get('type', $uda::TYPE_TOTAL);
		// 获取数据条件
		$request = array();
		$request['typeid'] = $this->request->get('typeid', $uda::SEARCH_DEF);
		$request['actid'] = $this->request->get('actid', $uda::SEARCH_DEF_ACT);
		$request['page'] = $this->request->get('page', 0);
		// 第一步设置每个文件的数据数目

		$limit = $uda::DOWN_LIMIT;
		$result = array();
		$uda->list_count($request, $type, $result, true);
		// 跳转的URL
		$url = $this->cpurl($this->_module, $this->_operation, 'total', $this->_module_plugin_id);
		if (!$result) {
			// 注入变量
			$total = 0;
			$this->view->set('url', $url);
			$this->view->set('total', $total);
			$this->output('export');
		}
		// 第二步获取总数据的数目
		$total = 0;
		$list = array();
		list($total, $list) = $result;
		// 如果有数据
		if ($total > 0) {
			// 第三步计算文件个数
			$num = ceil($total / $limit);
			// 第四步导出文件
			// 如果只有一个文件就单个导出
			// 生成表头
			$excel_fields = $this->__create_execel_header($uda, $type, $request);
			if ($num == $request['page'] && $request['page'] == 1) {
				$this->_putout_excel($excel_fields, $list);
				exit();
			} elseif ($num > 1 && $num != $request['page']) {
				$this->_create_zip($excel_fields, $list, $request['page']);
			}
			// 如果是最后一个文件就下载压缩包
			if ($num > 1 && $request['page'] == $num) {
				$zipname = '';
				$path = '';
				list($path, $zipname) = $this->_create_zip($excel_fields, $list, $request['page']);
				$this->_put_header($zipname);
				$this->_clear($path);
			}
			// 注入变量
			$this->view->set('num', $num);
			$this->view->set('offest', $request['page']);
		}
		// 注入变量
		$this->view->set('url', $url);
		$this->view->set('total', $total);
		// 输出模板
		$this->output('export');
	}

	/**
	 * 生成表头
	 *
	 * @param object $uda
	 * @param int $type
	 * @param array $request
	 * @return array
	 *
	 */
	private function __create_execel_header($uda, $type, $request) {

		if ($type == $uda::TYPE_TOTAL) {
			$excel_fields = array('_top' => array('name' => '排名', 'width' => 10), 'type_name' => array('name' => '类别', 'width' => 20), 'hits' => array('name' => '总阅读数', 'width' => 10), 'share' => array('name' => '总转发数', 'width' => 10), 'regs' => array('name' => '总报名数', 'width' => 10), 'signs' => array('name' => '签到人数', 'width' => 10));
			if ($request['actid'] > $uda::SEARCH_DEF_ACT) {
				$excel_fields = array('_top' => array('name' => '排名', 'width' => 10), 'act_name' => array('name' => '活动主题', 'width' => 60), 'hits' => array('name' => '总阅读数', 'width' => 10), 'share' => array('name' => '总转发数', 'width' => 10), 'regs' => array('name' => '总报名数', 'width' => 10), 'signs' => array('name' => '签到人数', 'width' => 10));
			}
		} else {
			$excel_fields = array('_salename' => array('name' => '自媒体', 'width' => 20), '_top' => array('name' => '排名', 'width' => 10), 'effect' => array('name' => '影响力', 'width' => 10), 'hits' => array('name' => '阅读数', 'width' => 10), 'share' => array('name' => '转发数', 'width' => 10), 'regs' => array('name' => '报名数', 'width' => 10), 'signs' => array('name' => '签到人数', 'width' => 10));
		}

		return $excel_fields;
	}

	/**
	 * 导出excel
	 *
	 * @param array $excel_fields
	 * @param array $list
	 */
	protected function _putout_excel($excel_fields, $list) {

		$options = array();
		$attrs = array();
		list($title_string, $title_width, $row_data) = $this->_excel_data($excel_fields, $list);
		$title = date('YmdHis', time());
		excel::make_excel_download($title, $title_string, $title_width, $row_data, $options, $attrs);
	}

	/**
	 * 生成压缩文件
	 *
	 * @param array $excel_fields
	 * @param array $list
	 * @param int $offest
	 * @return array
	 *
	 */
	protected function _create_zip($excel_fields, $list, $offest) {

		$zip = new ZipArchive();
		$path = voa_h_func::get_sitedir() . 'excel/';
		$zipname = $path . 'campaign';
		if (!file_exists($zipname)) {
			$zip->open($zipname . '.zip', ZipArchive::CREATE);
		}
		// 生成excel文件
		$result = $this->_create_excel($excel_fields, $list, $offest, $path);
		// 将生成的excel文件写入zip文件
		if ($result) $zip->addFile($result, $offest . '.xls');
		$zip->close();
		return array($path, $zipname . '.zip');
	}

	/**
	 * 生成execel文件
	 *
	 * @param array $excel_fields
	 * @param array $list
	 * @param int $i
	 * @param string $tmppath
	 * @return string
	 *
	 */
	protected function _create_excel($excel_fields, $list, $i, $tmppath) {

		if (!is_dir($tmppath)) mkdir($tmppath, '0777');
		$options = array();
		$attrs = array();
		list($title_string, $title_width, $row_data) = $this->_excel_data($excel_fields, $list);
		excel::make_tmp_excel_download('数据列表', $tmppath . $i . '.xls', $title_string, $title_width, $row_data, $options, $attrs);
		return $tmppath . $i . '.xls';
	}

}