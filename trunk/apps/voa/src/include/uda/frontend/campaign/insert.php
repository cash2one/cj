<?php

/**
 * 入库操作
 * voa_uda_frontend_campaign_insert
 * */
class voa_uda_frontend_campaign_insert extends voa_uda_frontend_campaign_base {

	/**
	 * 增加点击量
	 * 
	 * @param int $id
	 * @param int $saleid
	 * @param string $salename
	 *
	 */
	public function add_hits($id, $saleid, $salename) {

		$this->_campaign('total')->hits($id, $saleid, $salename);
		return true;
	}

	/**
	 * 保存分享
	 * 
	 * @param int $id
	 * @param int $saleid
	 * @param string $sharetime
	 *
	 */
	public function add_share($id, $saleid, $sharetime, &$result = array()) {

		$result = $this->_campaign('share')->save($id, $saleid, $sharetime);
		return true;
	}

	/**
	 * 统计分享
	 * 
	 * @param int $id
	 * @param int $saleid
	 * @param string $sharetime
	 *
	 */
	public function count_share($id, $saleid, $sharetime) {

		$this->_campaign('total')->share($id, $saleid, $sharetime);
		return true;
	}

	/**
	 * 统计报名
	 */
	public function count_regs($id, $saleid, $salename) {

		$this->_campaign('total')->regs($id, $saleid, '', $salename);
		return true;
	}

	/**
	 * 保存客户信息
	 * 
	 * @param array $data
	 *
	 */
	public function save_customer($data) {

		$cusid = $this->_campaign('customer')->save($data['name'], $data['mobile']);
		$data['customerid'] = $cusid;
		$regid = $this->_campaign('reg')->save($data);
		return $regid;
	}

} 