<?php

/**
 * 企业后台 - 活动 - 查看报名详情
 * Create By
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_campaign_reg extends voa_c_admincp_office_campaign_base {

	public function execute() {

		// 获取当前活动ID
		$acid = $this->request->get('acid');

		// 获取活动主题
		$uda_get = &uda::factory('voa_uda_frontend_campaign_get');
		$acts = $uda_get->get_act($acid);

		// 获取活动类型
		$uda_total = new voa_uda_frontend_campaign_total();
		$cats = $uda_total->list_type();
		$type_name = $cats[$acts['typeid']];
		$subject = $acts['subject'];
 		//$act = $uda_get->get_reg($acid);

		$conds = array('actid' => $acid);
		$total = $uda_get->get_count($conds);
		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => 12,
				'current_page' => $this->request->get('page'),
				'show_total_items' => true,
			);
			$multi = pager::make_links($pagerOptions);
			pager::resolve_options($pagerOptions);

			$page_option[0] = $pagerOptions['start'];
			$page_option[1] = 12;
			$orderby['created'] = 'DESC';

			$act = $uda_get->get_reg($conds, $page_option, $orderby);
		}

		$this->_format_data($act);

		$reg = $this->request->get('ac', '');
		// 下载
		if ($reg == 'down') {
			$orderby['created'] = 'DESC';
			$act = $uda_get->get_reg($conds,null, $orderby);
			$this->_format_data($act);
			$this->__download($act);
			return false;
		}

		// 赋值
		$this->view->set('total', $total);
		$this->view->set('multi', $multi);
		$this->view->set('act', $act);
		$this->view->set('subject', $subject);
		$this->view->set('type_name', $type_name);
		$this->output('office/campaign/reg');
	}

	/**
	 * 下载数据
	 */
	private function __download($list) {
		// 定义表头
		$excel_fields = array(
			'name' => array('name' => '姓名', 'width' => 15),
			'created' => array('name' => '报名时间', 'width' => 30),
			'mobile' => array('name' => '手机号', 'width' => 15),
			'is_sign' => array('name' => '状态', 'width' => 15),
			'custom' => array('name' => '自定义','width' => 20),
		);
		$this->_putout_excel($excel_fields, $list);
	}

	/**
	 * 导出excel
	 *
	 * @param array $excel_fields
	 * @param array $list
	 */
	protected function _putout_excel($excel_fields, $list) {

		$temp = '';
		if (!empty($list)) {
			foreach ($list as $_k => $_v) {

				if ($_v['is_sign'] == 1) {
					$list[$_k]['is_sign'] = '已签到';
				} else {
					$list[$_k]['is_sign'] = '未签到';
				}

				foreach($_v['custom'] as $_cv) {
					$temp.= $_cv['name'].'-'.$_cv['value']."\n";
				}
				$list[$_k]['custom'] = $temp;
				$temp = '';
			}
		}

		$options = array();
		$attrs = array();
		list($title_string, $title_width, $row_data) = $this->_excel_data($excel_fields, $list);
		$title = date('YmdHis', time());
		excel::make_excel_download($title, $title_string, $title_width, $row_data, $options, $attrs);
	}

	/**
	 * 格式化数组
	 * @param $data
	 * @return array
	 */
	protected function _format_data(&$data) {

		// 如果为空 ，返回空数组
		if (empty($data)) {
			return array();
		}

		// 将时间戳转换为特定格式
		foreach($data as $_key => $_v) {
			$data[$_key]['created'] = rgmdate($_v['created'], 'Y-m-d H:i:s');
			$data[$_key]['custom'] = unserialize($_v['custom']);
		}
	}

}
