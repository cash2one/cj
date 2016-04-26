<?php
/**
 * voa_c_cyadmin_article_base
 * 主站后台/后台管理/基本控制器
 * $Author$
 * $Id$
 */
class voa_c_cyadmin_content_article_base extends voa_c_cyadmin_content_base {
	protected $_serv_cate = null;

	protected function _before_action($action) {
		if (!parent::_before_action($action)) {
			return false;
		}
		$this->_serv_cate = &service::factory('voa_s_cyadmin_content_article_category');
		return true;
	}

	protected function _list_by_conds($conds = array()) {
		$service = &service::factory('voa_s_cyadmin_content_article_list');
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
			$orderby['asort'] = 'DESC';
			$orderby['aid'] = 'DESC';
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
		$_data = array();
		$_catlist = array();
		$_catlist = $this->_serv_cate->list_all();
		$cdata = array();
		foreach ($_catlist as $val) {
			$cdata[$val['acid']] = $val['acname'];
		}
		foreach ($data as $val) {
			
			$val['time'] = rgmdate($val['updated'], 'Y年m月d日 H:i');
			if (array_key_exists($val['acid'], $cdata)) {
				$val['cname'] = $cdata[$val['acid']];
			} else {
				$val['cname'] = '未分类';
			}
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
	 * 获取分类列表
	 * 
	 * @param array $data        	
	 * @param int $pid        	
	 * @param int $lev        	
	 * @param string $html        	
	 * @return array
	 *
	 */
	protected function _get_tree($data, $pid = 0, $lev = 0, $html = '|--') {
		$subs = array();
		foreach ($data as $v) {
			if ($v['pid'] == $pid) {
				if (!empty($html)) {
					$v['html'] = str_repeat($html, $lev);
				}
				
				$subs[] = $v;
				$subs = array_merge($subs, $this->_get_tree($data, $v['cid'], $lev + 1, $html));
			}
		}
		return $subs;
	}

	protected function _get_catlist($conds = array()) {
		// 根据条件查出相关分类
		$data = $this->_serv_cate->list_by_conds($conds);
		return $data;
	}
}
