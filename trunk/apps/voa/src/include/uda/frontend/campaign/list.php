<?php

/**
 * Created by PhpStorm.
 * User: ChangYi
 * Date: 2015/7/29
 * Time: 17:48
 */
class voa_uda_frontend_campaign_list extends voa_uda_frontend_base {

	/**
	 * service 类
	 */
	private $__service = null;

	public function __construct() {

		parent::__construct();
		if ($this->__service === null) {
			$this->__service = new voa_d_oa_campaign_db();
		}
	}

	/**
	 * 获取分类列表
	 * 
	 * @param array &$result 分类列表
	 * @return boolean
	 */
	public function list_all($request, &$result) {

		$where = "is_all = 1";
		
		// 2.根据部门id读活动列表
		$rithg = new voa_d_oa_campaign_right();
		$cd_id = $request['cd_id'];
		$rights_list = $rithg->list_by_conds(array('depid' => $cd_id));
		if ($rights_list) {
			$actids = array_column($rights_list, 'actid');
			if ($actids) {
				$where .= " OR id in(" . implode(',', $actids) . ")";
			}
		}
		
		$where = "($where)";
		
		// 3.筛选状态
		$where .= " AND status < ?";
		
		// 4.剔除过期活动
		$where .= " AND begintime < " . time();
		$where .= " AND overtime > " . time();
		
		// 5.分类筛选
		if ($request['typeid']) {
			$where .= " AND typeid = ?";
		}
		
		// 6.关键词筛选
		if ($request['keyword']) {
			$where .= " AND subject LIKE '%?%'";
		}
		
		$data = array(voa_d_oa_campaign_db::STATUS_DELETE, $request['typeid'], $request['keyword']);
		$result = $this->__service->_list_by_complex($where, $data, array($request['page'], $request['limit']));
		// $sql = "SELECT * FROM {campaign} WHERE $where ORDER BY id DESC LIMIT $start, $limit";
		$this->__os_format($result);
		return true;
	}

	private function __os_format(&$request) {

		if (!$request) {
			return false;
		}
		foreach ($request as &$l) {
			$l['_cover'] = voa_h_attach::attachment_url($l['cover']);
		}
		return true;
	}

}
