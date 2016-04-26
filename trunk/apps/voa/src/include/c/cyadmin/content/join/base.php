<?php
/**
 * voa_c_cyadmin_content_join_base
 * 主站后台/后台管理/内容管理/人才招聘/基本控制器
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_content_join_base extends voa_c_cyadmin_content_base {

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}
		return true;
	}

	protected function _list_by_conds($conds = array()) {
		$service = &service::factory('voa_s_cyadmin_content_join_list');
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
			$orderby['jsort'] = 'DESC';
			$orderby['jid'] = 'DESC';
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
}
