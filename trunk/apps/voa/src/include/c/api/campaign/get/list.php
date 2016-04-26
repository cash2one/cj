<?php
/**
 * 活动列表
 * $Author$
 * $Id$
 */
class voa_c_api_campaign_get_list extends voa_c_api_campaign_base {

	// 当前查询的数据起始行
	protected $_start;
	// 数据总数
	protected $_total;

	public function execute() {

		// 需要的参数
		$fields = array(
			// 当前页码
			'page' => array('type' => 'int', 'required' => false),
			// 每页显示数据数
			'limit' => array('type' => 'int', 'required' => false),
			// 读取的活动类型
			'typeid' => array('type' => 'int', 'required' => false),
			// 关键词
			'keyword' => array('type' => 'string', 'required' => false)
		);

		if (! $this->_check_params($fields)) {
			// 检查参数
			return false;
		}

		// 过滤
		$this->_params['keyword'] = addslashes($this->_params['keyword']);

		if ($this->_params['page'] < 1) {
			// 设定当前页码的默认值
			$this->_params['page'] = 1;
		}

		if ($this->_params['limit'] < 20) {
			// 设定每页数据条数的默认值
			$this->_params['limit'] = 20;
		}

		/* 获取分页参数 */
		list($start, $limit, $page) = voa_h_func::get_limit($this->_params['page'], $this->_params['limit'], 100);

		// 一.where子句

		// 1.读所有部门权限
		$where = "is_all = 1";

		// 2.根据部门id读活动列表
		$rithg = new voa_d_oa_campaign_right();
		$cd_id = $this->_member['cd_id'];
		$rights_list = $rithg->list_by_conds(array('depid' => $cd_id));
		$actids = array_column($rights_list, 'actid');
		if ($actids) {
			$where .= " OR id in(" . implode(',', $actids) . ")";
		}

		$where = "($where)";

		// 3.筛选状态
		$where .= " AND status < 3";

		// 4.剔除过期活动
		$where .= " AND begintime<".time();
		$where .= " AND overtime > " . time();

		// 5.分类筛选
		if ($this->_params['typeid']) {
			$where .= " AND typeid = " . $this->_params['typeid'];
		}

		// 6.关键词筛选
		if ($this->_params['keyword']) {
			$where .= " AND subject LIKE '%" . $this->_params['keyword'] . "%'";
		}

		$sql = "SELECT * FROM {campaign} WHERE $where ORDER BY id DESC LIMIT $start, $limit";
		$list = $this->db->getAll($sql);
		foreach ($list as & $l) {
			$l['_cover'] = voa_h_attach::attachment_url($l['cover']);
		}

		/* 输出结果 */
		$this->_result = array('limit' => $this->_params['limit'], 'page' => $this->_params['page'], 'list' => $list);

		return true;
	}
}
