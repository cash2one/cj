<?php
/**
 * 活动/我的业绩
 * $Author$
 * $Id$
 */
class voa_c_api_campaign_get_total extends voa_c_api_campaign_base {

	public function execute() {

		// 需要的参数
		$fields = array(
			// 当前页码
			'actid' => array('type' => 'int', 'required' => false)
		);
		if (! $this->_check_params($fields)) {
			// 检查参数
			return false;
		}

		$where = "WHERE saleid = " . $this->_member['m_uid'];
		if ($this->_params['actid']) {
			$where .= " AND actid = " . $this->_params['actid'];
		}

		$sql = "SELECT sum(share) share, sum(hits) hits, sum(regs) regs, sum(signs) signs FROM {campaign_total} $where";
		$data = $this->db->getRow($sql);

		$data = array('share' => intval($data['share']), 'hits' => intval($data['hits']), 'regs' => intval($data['regs']), 'signs' => intval($data['signs']));

		// 输出结果
		$this->_result = $data;

		return true;
	}
}
