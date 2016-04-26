<?php

/**
 * 审批相关的入库操作
 * $Author$
 * $Id$
 * +---------------------------------------------
 * Update Date: 2015-7-29
 * Update Author :Muzhitao
 */
class voa_uda_frontend_askfor_template_insert extends voa_uda_frontend_askfor_template_base {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * 审批数据入库
	 * @param $username
	 * @return bool
	 * @throws Exception
	 */
	public function template_new($username) {

		$servm = &service::factory('voa_s_oa_member', array('pluginid' => startup_env::get('pluginid')));

		$get = $this->_request->getx();

		// 缺失名称
		if (!$this->val_name($get['name'])) {
			return false;
		}

		// 审批人
		$approvers = array();
		$users = $servm->fetch_all_by_ids($get['m_uid']);
		if (!empty($get['m_uid'])) {
			foreach ($get['m_uid'] as $get['m_uid']) {
				$approvers[] = array('m_uid' => $get['m_uid'], 'm_username' => $users[$get['m_uid']]['m_username']);
			}
		}

		$orderid = (int)$this->_request->get('orderid');
		$upload_image = (int)$this->_request->get('upload_image');
		$cols = (array)$this->_request->get('cols');

		/** 数据入库 */
		$servt = &service::factory('voa_s_oa_askfor_template', array('pluginid' => startup_env::get('pluginid')));
		$servc = &service::factory('voa_s_oa_askfor_customcols', array('pluginid' => startup_env::get('pluginid')));

		$servm->begin();
		/** 模板入库 */
		$template = array(
			'name' => $get['name'],
			'creator' => $username['ca_username'],
			'orderid' => $orderid,
			'upload_image' => $upload_image,
			'aft_status' => voa_d_oa_askfor_template::STATUS_NORMAL,
		);

		$aft_id = $servt->insert($template, true);
		if (empty($aft_id)) {
			throw new Exception('新增流程失败');
		}

		/** 自定义字段入库 */
		if (!empty($cols)) {
			foreach ($cols as $k => $col) {
				$data[] = array(
					'aft_id' => $aft_id,
					'field' => $aft_id . '_field_' . $k,
					'name' => $col['name'],
					'type' => $col['type'],
					'required' => isset($col['required']) ? 1 : 0,
				);
			}

			$servc->insert_multi($data);
		}

		$servm->commit();

		return true;
	}

	/**
	 * 判断数组是否有序
	 * @param array $arr
	 * @return boolean
	 */
	private function __judegSortArray($array) {
		if (is_array($array)) {
			$count = count($array);
			if ($count > 1) {
				if ($array [0] > $array [1]) {
					$flag = 1;
				} else {
					//$flag = 0;
					return false;
				}
				$temp = $flag;
				for ($i = 1; $i < $count - 1; $i ++) {
					if ($flag == 0) {
						if ($array [$i] < $array [$i + 1]) {
							continue;
						} else {
							$flag = 1;
							break;
						}
					}
					if ($flag == 1) {
						if ($array[$i] > $array[$i + 1]) {
							continue;
						} else {
							$flag = 0;
							break;
						}
					}
				}
				if ($flag != $temp) {
					return false;
				} else {
					return true;
				}
			}

			return true;
		}

		return false;
	}

}
