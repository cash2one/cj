<?php
/**
 * 企业后台/微办公管理/活动/统计数据
 * Create By linshiling
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_campaign_total extends voa_c_admincp_office_campaign_base {

	public function execute() {

		$page = (int)$this->request->get('page', 0);
		$limit = 50;
		list($start, $limit, $page) = voa_h_func::get_limit($page, $limit);

		$actid = (int)$this->request->get('actid', 0);
		$where = "WHERE `status`<4";
		if (0 < $actid) {
			$where .= " AND actid = " . $this->_params['actid'];
		}

		$db = new voa_d_oa_campaign_db();
		// 统计总数
		$sql = "SELECT COUNT(DISTINCT saleid) FROM {campaign_total} {$where}";
		$count = $db->getOne($sql);

		// 分页链接信息
		$multi = '';
		if ($count > 0) {
			// 输出分页信息
			$multi = pager::make_links(array('total_items' => $count, 'per_page' => $limit, 'current_page' => $page, 'show_total_items' => true));
		}

		// 读取列表
		$sql = "SELECT saleid, sum(share) share, sum(hits) hits, sum(regs) regs, sum(signs) signs FROM {campaign_total} $where GROUP BY saleid LIMIT {$start}, {$limit}";
		$list = $db->getAll($sql);

		// 获取销售uid
		$uids = array();
		foreach ($list as $_v) {
			$uids[] = $_v['saleid'];
		}

		$serv_m = &service::factory('voa_s_oa_member');
		$users = $serv_m->fetch_all_by_ids($uids);

		// 取部门信息
		$this->view->set('departments', voa_h_cache::get_instance()->get('department', 'oa'));
		$this->view->set('jobs', voa_h_cache::get_instance()->get('job', 'oa'));
		$this->view->set('users', $users);
		$this->view->set('list', $list);
		$this->view->set('multi', $multi);

		$this->output('office/campaign/total');
	}
}
