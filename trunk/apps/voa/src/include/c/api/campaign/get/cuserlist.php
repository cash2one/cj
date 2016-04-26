<?php
/**
 * 客户列表
 * $Author$
 * $Id$
 */
class voa_c_api_campaign_get_cuserlist extends voa_c_api_campaign_base {

	/** 当前查询的数据起始行 */
	protected $_start;
	/** 数据总数 */
	protected $_total;

	public function execute() {

		if($_GET['act'] == 'delete') {
			return $this->delete();
		}

		// 需要的参数
		$fields = array(
			// 当前页码
			'page' => array('type' => 'int', 'required' => false),
			// 每页显示数据数
			'limit' => array('type' => 'int', 'required' => false),
			// 读取的活动类型
			'actid' => array('type' => 'int', 'required' => false),
		);
		if (!$this->_check_params($fields)) {
			// 检查参数
			return false;
		}

		if ($this->_params['page'] < 1) {
			// 设定当前页码的默认值
			$this->_params['page'] = 1;
		}

		if ($this->_params['limit'] < 1) {
			// 设定每页数据条数的默认值
			$this->_params['limit'] = 10;
		}

		// 获取分页参数
		list($start, $limit, $page) = voa_h_func::get_limit($this->_params['page'], $this->_params['limit'], 100);

		$uid = $this->_member['m_uid'];
		$where = "r.saleid = $uid AND r.`status` < 3 AND a.`status` < 3";
		if($this->_params['actid']) {
			$where .= " AND r.actid = " . $this->_params['actid'];
		}

		$sql = "SELECT r.id, r.actid, r.name, r.mobile, r.created, r.is_sign, a.subject FROM {campaign_reg} r
				LEFT JOIN {campaign} a ON r.actid = a.id
				where $where ORDER BY id DESC LIMIT $start, $limit";

		$list = $this->db->getAll($sql);
		foreach ($list as & $l) {
			$l['_created'] = rgmdate($l['created']);
			$l['_saleid'] = $uid;
			$l['_time'] = time();
		}

		//输出结果
		$this->_result = array(
			'limit' => $this->_params['limit'],
			'page' => $this->_params['page'],
			'list' => $list
		);

		return true;
	}

		// 删除客户
	private function delete() {

		$id = intval($_GET['id']);
		if (! $id) {
			$this->_set_errcode('删除客户错误,ID异常');
			return false;
		}

		$d = new voa_d_oa_campaign_reg();
		$reg = $d->get($id);
		if (! $reg) {
			$this->_set_errcode('客户已删除');
			return true;
		}

		$rs = $d->delete($id);
		if (! $rs) {
			$this->_set_errcode('删除失败');
			return false;
		}

		// 统计报名数
		$total = new voa_d_oa_campaign_total();
		$total->regs($reg['actid'], $reg['saleid'], rgmdate($reg['created'], 'Y-m-d'));

		return true;
	}
}
