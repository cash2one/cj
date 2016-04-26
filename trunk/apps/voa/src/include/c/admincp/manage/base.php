<?php
/**
 * voa_c_admincp_manage_base
 * 公司管理基本控制器/manage/base
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_manage_base extends voa_c_admincp_base {

	/**
	 * (manage/base) 最多允许添加的部门个数
	 * @var number
	 */
	public $department_maxcount		=	voa_d_oa_common_department::COUNT_MAX;

	/**
	 * (manage/base) 最多允许添加的职务个数
	 * @var number
	 */
	public $job_maxcount			=	voa_d_oa_common_job::COUNT_MAX;

	protected function _before_action($action) {
		return parent::_before_action($action);
	}

}
