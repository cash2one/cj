<?php
/**
 * voa_uda_frontend_travel_customer2remark
 * 统一数据访问/客户应用/客户所对应的备注信息
 * $Author$
 * $Id$
 */

class voa_uda_frontend_travel_customer2remark extends voa_uda_frontend_travel_abstract {
	// 特殊条件参数
	protected $_special_conds = array();

	public function __construct($ptname = null) {

		parent::__construct($ptname);
		// 特殊条件参数
		if (!empty($ptname['conds'])) {
			$this->_special_conds = $ptname['conds'];
		}
	}

	/**
	 * 获取备注列表
	 * @param array &$list 表格列选项
	 * @return boolean
	 */
	public function list_all($gp, $page_option, &$list, $total) {

		// 查询表格的条件
		$fields = array(
			array('crk_id', self::VAR_INT, null, null, true),
			array('customer_id', self::VAR_INT, null, null, true),
			array('crk_type', self::VAR_INT, null, null, true)
		);
		$conds = array();
		if (!$this->extract_field($conds, $fields, $gp)) {
			return false;
		}

		$conds = array_merge($conds, $this->_special_conds);
		// 读取数据
		$t = new voa_d_oa_travel_customer_remark();
		if (!$list = $t->list_by_conds($conds, $page_option, array('updated' => 'desc'))) {
			return true;
		}

		// 遍历备注信息
		foreach ($list as &$_v) {
			// 格式化
			$this->_format_message($_v);
		}

		$total = $t->count_by_conds($conds);

		return true;
	}

	/**
	 * 根据 crk_id 获取备注信息
	 * @param int $crk_id 备注id
	 * @param array $remark 备注信息
	 * @return boolean
	 */
	public function get_one($crk_id, &$remark) {

		$t = new voa_d_oa_travel_customer_remark();
		if (!$remark = $t->get($crk_id)) {
			$this->set_errmsg(voa_errcode_oa_travel::CUSTOMER_REMARK_IS_NOT_EXIST);
			return false;
		}

		// 格式化备注
		$this->_format_message($remark);

		return true;
	}

	/**
	 * 新增对客户的备注
	 * @param array $member 用户信息
	 * @param array $gp 请求数据
	 * @param array &$data 备注信息
	 * @return boolean
	 */
	public function add($member, $gp, &$data) {

		// 用户信息
		$this->_mem = $member;
		// 提取数据
		$data = array(
			'uid' => $member['m_uid']
		);
		$diys = array();
		if (!$this->__parse_gp($gp, $data)) {
			return false;
		}

		// 数据处理类
		$t = new voa_d_oa_travel_customer_remark();

		try {
			$data = $t->insert($data);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		// 格式化备注
		$this->_format_message($data);

		return true;
	}

	/**
	 * 更新备注信息
	 * @param array $member 用户信息
	 * @param array $gp 数据
	 * @param int $crk_id 备注id
	 * @throws service_exception
	 * @return boolean
	 */
	public function update($member, $gp, $crk_id) {

		// 用户信息
		$this->_mem = $member;
		// 提取数据
		$data = array(
			'uid' => $member['m_uid']
		);
		$diys = array();
		if (!$this->__parse_gp($gp, $data)) {
			return false;
		}

		// 初始化数据处理类
		$t = new voa_d_oa_travel_customer_remark();

		try {
			$t->update($crk_id, $data);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 删除表格列属性信息
	 * @param int|array crk_id 备注id
	 * @throws service_exception
	 * @return boolean
	 */
	public function delete($member, $crk_id) {

		// 读取指定数据
		$t = new voa_d_oa_travel_customer_remark();

		try {
			$t->delete($crk_id);
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 把备注移到指定客户下
	 * @param mixed $crk_ids 备注id
	 * @param int $customer_id 客户id
	 * @throws service_exception
	 * @return boolean
	 */
	public function mv_remark2customer($crk_ids, $customer_id) {

		$t = new voa_d_oa_travel_customer_remark();

		try {
			$t->update_by_conds(array('crk_id' => $crk_ids, 'customer_id' => 0), array('customer_id' => $customer_id));
		} catch (Exception $e) {
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}

		return true;
	}

	/**
	 * 格式化备注信息
	 * @param array $data 备注信息
	 * @return boolean
	 */
	protected function _format_message(&$data) {

		// 如果是附件备注
		if (voa_d_oa_travel_customer_remark::TYPE_PIC != $data['crk_type']
				&& voa_d_oa_travel_customer_remark::TYPE_VOICE != $data['crk_type']) {
			return true;
		}

		// 附件id => 附件url
		$data['attachs'] = array();
		$this->_parse_attach_url($data['attachids'], $data['duration'], $data['attachs']);

		return true;
	}

	/**
	 * 把附件id信息解析成附件url
	 * @param string $ids 附件id
	 * @param string $duration 时长
	 * @param array $attachs 附件url数组
	 * @return boolean
	 */
	protected function _parse_attach_url($ids, $duration, &$attachs) {

		// 切附件/时长字段
		$at_ids = explode(',', $ids);
		$durs = explode(',', $duration);
		// 重组对应关系
		$at_id2dur = array();
		foreach ($at_ids as $_k => $_id) {
			$at_id2dur[$_id] = isset($durs[$_k]) ? (int)$durs[$_k] : 0;
		}

		// 读取附件
		$serv_att = &service::factory('voa_s_oa_common_attachment');
		$atts = $serv_att->fetch_by_ids($at_ids);

		// 组织返回数据
		foreach ($atts as $_at) {
			$attachs[] = array(
				'filename' => $_at['at_filename'],
				'url' => voa_h_attach::attachment_url($_at['at_id']),
				'duration' => $at_id2dur[$_at['at_id']]
			);
		}

		return true;
	}

	/**
	 * 从 G/P 中提取数据
	 * @param array $gp 请求数据
	 * @param array $remark 数据结果
	 * @return boolean
	 */
	private function __parse_gp($gp, &$remark) {

		$fields = array(
			array('customer_id', self::VAR_INT, null, null),
			array('crk_type', self::VAR_INT, null, null),
			array('message', self::VAR_STR, null, null),
			array('attachids', self::VAR_STR, null, null),
			array('remindts', self::VAR_STR, null, null),
			array('duration', self::VAR_STR, null, null)
		);
		// 提取数据
		if (!$this->extract_field($remark, $fields, $gp)) {
			return false;
		}

		// 附件类型
		$at_types = array(
			voa_d_oa_travel_customer_remark::TYPE_PIC,
			voa_d_oa_travel_customer_remark::TYPE_VOICE
		);
		// 根据备注类型, 判断消息的正确性
		$key = 'message';
		if (in_array($remark['crk_type'], $at_types)) {
			$key = 'attachids';
		} elseif (voa_d_oa_travel_customer_remark::TYPE_REMIND == $remark['crk_type']) {
			$key = 'remindts';
		} else {
			$remark['crk_type'] = voa_d_oa_travel_customer_remark::TYPE_TEXT;
		}

		// 判断备注是否为空
		if (empty($remark[$key])) {
			$this->set_errmsg(voa_errcode_oa_travel::REMARK_IS_EMPTY);
			return false;
		}

		return true;
	}

	/**
	 * 判断备注类型是否正确
	 * @param int $type 备注类型
	 * @param string $err 错误提示
	 * @return boolean
	 */
	protected function _chk_crk_type($type, $err = null) {

		// 所有备注类型集合
		$types = array(
			voa_d_oa_travel_customer_remark::TYPE_PIC,
			voa_d_oa_travel_customer_remark::TYPE_REMIND,
			voa_d_oa_travel_customer_remark::TYPE_TEXT,
			voa_d_oa_travel_customer_remark::TYPE_VOICE
		);

		// 如果类型不在已知范围内
		if (!in_array($type, $types)) {
			$this->set_errmsg(voa_errcode_oa_travel::CRK_TYPE_INVALID);
			return false;
		}

		return true;
	}

}
