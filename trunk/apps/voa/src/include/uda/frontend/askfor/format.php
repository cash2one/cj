<?php
/**
 * 审批数据格式化
 * $Author$
 * $Id$
 */

class voa_uda_frontend_askfor_format extends voa_uda_frontend_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 格式化审批数据数组
	 * @param array $data 数据数组
	 */
	public function askfor_list(&$list) {
		foreach ($list as &$data) {
			$this->askfor($data);
		}

		return true;
	}

	/**
	 * 格式化审批数据
	 * @param array $data 审批数据
	 */
	public function askfor(&$data) {
		$data['_subject'] = rhtmlspecialchars($data['af_subject']);
		$data['_created'] = rgmdate($data['af_created'], 'Y-m-d H:i');
		$data['_created_u'] = rgmdate($data['af_created'], 'u');

		/** 状态显示 */
		$data['_status_class'] = '';
		if (voa_d_oa_askfor::STATUS_APPROVE == $data['af_status']) {
			$data['_status_tip'] = '已通过';
		} else if (voa_d_oa_askfor::STATUS_REFUSE == $data['af_status']) {
			$data['_status_class'] = 'fail';
			$data['_status_tip'] = '未通过';
		} else {
			$data['_status_tip'] = '审批中';
		}
		$data['_created'] = rgmdate($data['af_created'], 'Y-m-d H:i');

		return true;
	}

	/**
	 * 格式化审批/回复详情
	 * @param array $data
	 */
	public function askfor_post(&$data) {
		$data['_subject'] = rhtmlspecialchars($data['afc_subject']);
		$data['_message'] = bbcode::instance()->bbcode2html($data['afc_message']);
		$data['_created_u'] = rgmdate($data['afc_created'], 'u');
		return true;
	}

	/**
	 * 格式化进度信息
	 * @param array $data
	 * @return boolean
	 */
	public function askfor_proc(&$data) {
		$data['_message'] = bbcode::instance()->bbcode2html($data['afp_message']);
		$data['_created_u'] = rgmdate($data['afp_created'], 'u');
		$data['_updated_u'] = rgmdate($data['afp_updated'], 'u');
		$data['_created'] = rgmdate($data['afp_created'], 'Y-m-d H:i');
		return true;
	}

	/**
	 * 格式化审批流程列表
	 * @param array $templates
	 * @return boolean
	 */
	public function askfor_template($templates) {

		$result = array();
		if (!empty($templates)) {
			foreach ($templates as $template) {
				$result[] = array(
					'aft_id' => $template['aft_id'],
					'name' => $template['name'],
					'orderid' => $template['orderid']
				);
			}
		}

		return $result;
	}
}
