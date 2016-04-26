<?php
/**
 * voa_c_admincp_office_namecard_base
 * 企业后台/微办公管理/微名片/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_namecard_base extends voa_c_admincp_office_base {

	protected $_member_list = array();
	protected $_job_list = array();
	protected $_folder_list = array();
	protected $_company_list = array();

	public $_namecard_gender = array(
			0 => '未设置',
			1 => '男',
			2 => '女',
	);

	public $_namecard_active = array(
			0 => '离职',
			1 => '在职',
	);

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		// 判断应用是否过期 或者没有购买
//		$plugin_result = '';
//		if (!$this->_judge_plugin_is_overdue($this->_module_plugin_id, $plugin_result)) {
//			$this->_error_message($plugin_result);
//			return false;
//		};

		$navmenu = array();
		$navmenu['links'] = array();
		$list_url = $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id);
		if ($list_url) {
			$navmenu['links']['list'] = array(
						'icon' => 'fa-list',
						'url' => $list_url,
						'name' => '名片管理',
				);
		}
		$company_url = $this->cpurl($this->_module, $this->_operation, 'company', $this->_module_plugin_id);
		if ($company_url) {
			$navmenu['links']['company'] = array(
						'icon' => 'fa-list',
						'url' => $company_url,
						'name' => '公司管理',
				);
		}
		$folder_url = $this->cpurl($this->_module, $this->_operation, 'folder', $this->_module_plugin_id);
		if ($folder_url) {
			$navmenu['links']['folder'] = array(
						'icon' => 'fa-list',
						'url' => $folder_url,
						'name' => '群组管理',
				);
		}
		$job_url = $this->cpurl($this->_module, $this->_operation, 'job', $this->_module_plugin_id);
		if ($job_url) {
			$navmenu['links']['job'] = array(
						'icon' => 'fa-list',
						'url' => $job_url,
						'name' => '职务管理',
				);
		}

		$this->view->set('navmenu', $navmenu);
		return true;
	}

	protected function _after_action($action) {
		parent::_after_action($action);
		return true;
	}

	/**
	 * 返回多个职务id的职务信息
	 * @param array $ncj_ids
	 * @return array
	 */
	protected function _get_job_by_ncj_ids($cp_pluginid, $ncj_ids) {
		return $this->_job_list = $this->_service_single('namecard_job', $cp_pluginid, 'fetch_by_ids', $ncj_ids);
	}

	/**
	 * 返回多个公司id的公司信息
	 * @param array $ncc_ids
	 * @return array
	 */
	protected function _get_company_by_ncc_ids($cp_pluginid, $ncc_ids) {
		return $this->_company_list = $this->_service_single('namecard_company', $cp_pluginid, 'fetch_by_ids', $ncc_ids);
	}

	/**
	 * 返回多个群组id的群组信息
	 * @param array $ncf_ids
	 * @return array
	 */
	protected function _get_folder_by_ncf_ids($cp_pluginid, $ncf_ids) {
		return $this->_folder_list = $this->_service_single('namecard_folder', $cp_pluginid, 'fetch_by_ids', $ncf_ids);
	}

	/**
	 * 格式化名片信息
	 * @param array $namecard
	 * @return array
	 */
	protected function _format_namecard($namecard) {
		if (isset($this->_member_list[$namecard['m_uid']])) {
			$namecard['_username'] = $this->_member_list[$namecard['m_uid']]['m_username'];
		} else {
			$namecard['_username'] = '';
		}
		if (isset($this->_job_list[$namecard['ncj_id']])) {
			$namecard['_job'] = $this->_job_list[$namecard['ncj_id']]['ncj_name'];
		} else {
			$namecard['_job'] = '';
		}
		if (isset($this->_company_list[$namecard['ncc_id']])) {
			$namecard['_company'] = $this->_company_list[$namecard['ncc_id']]['ncc_name'];
		} else {
			$namecard['_company'] = '';
		}
		if (isset($this->_folder_list[$namecard['ncf_id']])) {
			$namecard['_folder'] = $this->_folder_list[$namecard['ncf_id']]['ncf_name'];
		} else {
			$namecard['_folder'] = '';
		}
		$namecard['_updated'] = rgmdate(($namecard['nc_updated'] ? $namecard['nc_updated'] : $namecard['nc_created']), 'Y-m-d H:i');
		return $namecard;
	}
}
