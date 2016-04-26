<?php
class voa_c_cyadmin_content_train_view extends voa_c_cyadmin_content_train_base {

	public function execute() {
		$tid = $this->request->get('tid');
		$uda = &uda::factory('voa_uda_cyadmin_content_train_list');
		$view = $uda->get_view($tid);
		$view['start_time'] = rgmdate($view['start_time']);
		$view['end_time'] = rgmdate($view['end_time']);
		$view['tags'] = $this->_pro_tags($view['tags']);
		$view['url'] = $this->_pro_url($view['face_atid']);
		$this->view->set('view', $view);
		$list = array();
		$total = 0;
		$multi = null;
		list($total, $multi, $list) = $this->_get_sign($tid);
		if ($this->request->get('export') == 'export') {
			$this->__export($list, $view);
			return false;
		}
		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		
		$this->view->set('train_sign', $this->_formart_sign($list));
		$this->_render('view');
		
	}

	protected function _get_sign($id) {
		$service = &service::factory('voa_s_cyadmin_content_train_sign');
		// 统计数量
		$conds = array();
		$conds['tid=?'] = $id;
		// 显示数量
		$perpage = 10;
		// $conds['publishtime <= ?'] = time();
		$total = 0;
		$list = array();
		$multi = null;
		$total = $service->count_by_conds($conds);
		// print_r($total);
		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $perpage,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true 
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);
			$page_option[0] = $pagerOptions['start'];
			$page_option[1] = $perpage;
			$orderby['tsid'] = 'DESC';
			$list = $service->list_by_conds($conds, $page_option, $orderby);
			// print_r($list);
		}
		
		return array(
			$total,
			$multi,
			$list 
		);
	}

	protected function _formart_sign($data) {
		if (empty($data)) {
			return $data;
		}
		$sign = array();
		foreach ($data as $val) {
			
			$val['time'] = rgmdate($val['signtime']);
			$sign[] = $val;
		}
		
		return $sign;
	}

	/**
	 * 导出csv文件
	 */
	private function __export($list, $view) {
		$data = array();
		$fields = array();
		$sign_info = $view['sign_fields_info'];
		$filename = $view['title'] . '报名名单.csv';
		$sign = array(
			'报名时间',
			'报名IP' 
		);
		array_splice($view['sign_fields_info'], 3, 0, $sign);
		$data[0] = $view['sign_fields_info'];
		unset($sign_info[0], $sign_info[1], $sign_info[2]);
		if(!empty($sign_info)){
			foreach ($sign_info as $val) {
				$fields[] = $val;
			}
		}
		
		foreach ($list as $k => $v) {
			$list[$k]['signtime'] = rgmdate($list[$k]['signtime']);
			$list[$k]['signremark'] = rhtmlspecialchars($list[$k]['signremark']);
			$list[$k]['signother'] = unserialize($list[$k]['signother']);
			foreach ($list[$k]['signother'] as $key => $val) {
				$val = rhtmlspecialchars($val);
			}
		}
		
		foreach ($list as $val) {
			$temp = array(
				'signname' => $val['signname'],
				'signphone' => $val['signphone'],
				'signremark' => $val['signremark'],
				'signtime' => $val['signtime'],
				'signip' => $val['signip'] 
			);
			if (!empty($fields)) {
				foreach ($fields as $field) {
					$temp[$field] = isset($val['signother'][$field]) ? $val['signother'][$field] : '';
				}
			}
			
			$data[] = $temp;
		}
		$csv_data = array2csv($data);
		
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
		header("Content-Type: text/csv");
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header('Content-Disposition: attachment;filename="' . $filename . '"');
		header('Coentent_Length: ' . strlen($csv_data));
		echo $csv_data;
		
		exit();
	}
}
