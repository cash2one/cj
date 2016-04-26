<?php
/**
 * voa_c_admincp_office_askfor_base
 * 企业后台 - 审批流 - 基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_askfor_base extends voa_c_admincp_office_base {

	/** 审批状态文字描述 */
	protected $_askfor_status_descriptions = array(
			voa_d_oa_askfor::STATUS_NORMAL => '审批中',
			voa_d_oa_askfor::STATUS_APPROVE => '已批准',
			voa_d_oa_askfor::STATUS_APPROVE_APPLY => '通过并转审批',
			voa_d_oa_askfor::STATUS_REFUSE => '审核未通过',
			voa_d_oa_askfor::STATUS_DRAFT => '草稿',
			voa_d_oa_askfor::STATUS_CANCEL => '已撤销',
			voa_d_oa_askfor::STATUS_REMINDER => '已催办'
			//voa_d_oa_askfor::STATUS_REMOVE => '已删除',
	);

	/** 审批状态样式定义 */
	protected $_askfor_status_class_tag = array(
			voa_d_oa_askfor::STATUS_NORMAL => 'primary',//审批中
			voa_d_oa_askfor::STATUS_APPROVE => 'success',//已批准
			voa_d_oa_askfor::STATUS_APPROVE_APPLY => 'info',//通过并转审批
			voa_d_oa_askfor::STATUS_REFUSE => 'danger',//审核未通过
			voa_d_oa_askfor::STATUS_DRAFT => 'default',//草稿
			voa_d_oa_askfor::STATUS_REMOVE => 'warning',//已删除
	);
	/** 审批流程状态样式定义 */
	protected $_proc_condition = array(
		voa_d_oa_askfor::STATUS_NORMAL => '#69CEA7',//审批中
		voa_d_oa_askfor::STATUS_APPROVE => '#46AC46',//已批准
		voa_d_oa_askfor::STATUS_APPROVE_APPLY => '#39B3D7',//通过并转审批
		voa_d_oa_askfor::STATUS_REFUSE => '#E14430',//审核未通过
		voa_d_oa_askfor::STATUS_DRAFT => 'default',//草稿
		voa_d_oa_askfor::STATUS_REMOVE => 'warning',//已删除
	);
	/** 部门列表 */
	protected $_department_list = array();

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		/** 部门名称列表 */
		$this->_department_list = $this->_department_list();
		return true;
	}

	/**
	 * 返回指定审批信息
	 * @param number $af_id
	 * @return array
	 */
	protected function _askfor_get($cp_pluginid, $af_id){
		$askfor = $this->_service_single('askfor', $cp_pluginid, 'fetch_all_by_condition', array('af_id' => $af_id), 1, 0);
		if ( empty($askfor) || empty($askfor[$af_id]) ) {
			return array();
		}
		return $askfor[$af_id];
	}

	/**
	 * 格式化审批流详情
	 * @param array $askfor
	 * @return array
	 */
	protected function _askfor_format($askfor = array()) {
		$askfor['_created'] = rgmdate($askfor['af_created'], 'Y-m-d H:i');
		$askfor['_status'] = isset($this->_askfor_status_descriptions[$askfor['af_status']]) ? $this->_askfor_status_descriptions[$askfor['af_status']] : '';
		if ( isset($this->_department_list[$askfor['cd_id']]) ) {
			$askfor['_department'] = $this->_department_list[$askfor['cd_id']]['cd_name'];
		} else {
			$askfor['_department'] = '';
		}
		$askfor['_status_class_tag'] = isset($this->_askfor_status_class_tag[$askfor['af_status']]) ? $this->_askfor_status_class_tag[$askfor['af_status']] : 'warning';
		return $askfor;
	}

	/**
	 * 格式化审批流程
	 * @param array $list
	 * @return array
	 */
	protected function _template_format($list = array()) {

		$result = array();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				$result[$k] = $v;
				$result[$k]['created'] = rgmdate($v['aft_created'], 'Y-m-d H:i');
				$result[$k]['is_use'] = $v['is_use'] ? '启用' : '禁用';
				$result[$k]['creator'] = $v['creator'];
			}
		}

		return $result;
	}

	/**
	 * 格式化流程信息
	 * @param array $askfor
	 * @return mixed
	 */
	protected function _proc_format($askfor = array()){

		foreach($askfor as &$val) {

			$val['_created'] = rgmdate($val['afp_created'], 'Y-m-d H:i');
			$val['_condition'] = isset($this->_askfor_status_descriptions[$val['afp_condition']]) ? $this->_askfor_status_descriptions[$val['afp_condition']] : '未知';
			$val['_tag'] = isset($this->_askfor_status_class_tag[$val['afp_condition']]) ? $this->_askfor_status_class_tag[$val['afp_condition']] : 'warning';
			$val['_color'] = isset($this->_proc_condition[$val['afp_condition']]) ? $this->_proc_condition[$val['afp_condition']] : '#F29F29';

		}
		return $askfor;
	}

}
