<?php
/**
 * voa_c_admincp_office_superreport_view
 * 企业后台/微办公管理/超级报表/查看报表
 * Create By YanWenzhong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_superreport_view extends voa_c_admincp_office_superreport_base {

	public function execute() {

		$dr_id = (int)$this->request->get('dr_id');
		$page = $this->request->get('page');   // 当前页码
		$limit = $this->_p_sets['comment_perpage'];   // 当前页码
		if (!is_numeric($page) || $page < 1) {
			$page = 1;
		}
		list($start, $limit, $page) = voa_h_func::get_limit($page, $limit);

		//例外，因无对应的uda，调用service类，取得报表详情
		$s_detail = new voa_s_oa_superreport_detail();
		$detail = $s_detail->get_detail_by_dr_id($dr_id);
		if (!$detail) {
			$this->message('error', '日报不存在');
			return;
		}
		$conds['dr_id'] = $dr_id;
		$conds['csp_id'] = $detail['csp_id'];
		$conds['date'] = $detail['cdate'];
		$conds['limit'] = $limit;
		$conds['start'] = $start;

		try {
			$result = array();
			// 载入uda类
			$uda = &uda::factory('voa_uda_frontend_superreport_daily');
			$uda->get_daily($conds, $result);
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}


		$total_page = ceil($result['comments_total']/$limit);

		// 注入模板变量
		$this->view->set('result', $result);
		$this->view->set('total_page', $total_page);
		$this->view->set('dr_id', $dr_id);

		// 输出模板
		$this->output('office/superreport/view');
	}

}
