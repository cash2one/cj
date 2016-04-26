<?php

/**
 * 后台管理/线下陪训/基类控制器
 * Create By liyongjian
 */
class voa_c_cyadmin_content_train_base extends voa_c_cyadmin_content_base {
	protected $__viewPath = 'cyadmin/content/train';

	/**
	 * 用Layout形式,输出模板
	 */
	protected function _render($view) {
		$view = sprintf('%s/%s', $this->__viewPath, $view);
		
		$html = $this->output($view, true);
		$this->view->set('html', $html);
		$this->output(sprintf('%s/main', $this->__viewPath));
	}

	protected function _list_by_conds($conds = array()) {
		$service = &service::factory('voa_s_cyadmin_content_train_list');
		// 统计数量
		
		// 显示数量
		$perpage = 10;
		// $conds['publishtime <= ?'] = time();
		$total = null;
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
			$orderby['tsort'] = 'DESC';
			$orderby['tid'] = 'DESC';
			$list = $service->list_by_conds($conds, $page_option, $orderby);
			// print_r($list);
		}
		
		return array(
			$total,
			$multi,
			$list 
		);
	}

	/**
	 * 处理数据
	 * 
	 * @param array() $data        	
	 */
	protected function _listformat($data) {
		if (empty($data)) {
			return $data;
		}
		foreach ($data as $val) {
			
			$val['time'] = rgmdate($val['updated'], 'Y年m月d日 H:i');
			
			if ($val['is_publish'] == 1) {
				
				$val['status'] = '已发布';
			} else {
				$val['status'] = '草稿';
			}
			$_data[] = $val;
		}
		return $_data;
	}

	/**
	 * 处理时间
	 */
	protected function _pro_time($time) {
		$time = rgmdate($time);
		$data_time = array();
		$date_time = explode(' ', $time);
		$data_time['data'] = $date_time[0];
		$data_time['time'] = $date_time[1];
		
		return $data_time;
	}

	/**
	 * 处理图片地址
	 */
	protected function _pro_url($atid) {
		if ($atid != 0) {
			$url = $this->_get_img_url($atid);
			return $url;
		}
		return false;
	}

	/**
	 * 处理报名信息
	 */
	protected function _pro_sign($sign_fields) {
		$serv_sign = &service::factory('voa_s_cyadmin_content_train_setting');
		$sign_all = $serv_sign->list_all();
		$fields = explode(',', $sign_fields);
		$fields = array_flip($fields);
		$sign = array();
		foreach ($sign_all as $val) {
			if (array_key_exists($val['sid'], $fields)) {
				$val['selected'] = 1;
			}
			$sign[] = $val;
		}
		
		return $sign;
	}
}
