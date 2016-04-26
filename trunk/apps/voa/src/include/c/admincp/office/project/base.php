<?php
/**
 * voa_c_admincp_office_project_base
 * 企业后台/微办公管理/工作台/基本控制器
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_project_base extends voa_c_admincp_office_base {

	/** 项目状态描述 */
	public $_project_status = array(
			voa_d_oa_project::STATUS_NORMAL => '新项目',
			voa_d_oa_project::STATUS_UPDATE => '进行中',
			voa_d_oa_project::STATUS_COMPLETE => '已完成',
			voa_d_oa_project::STATUS_CLOSED => '已关闭',
	);

	/** 项目打开状态 */
	public $_project_open = array(
			voa_d_oa_project::STATUS_UPDATE => '打开',
			voa_d_oa_project::STATUS_CLOSED => '关闭',
	);

	/** 项目关闭标识 */
	public $_project_open_config = array(
			'open' => voa_d_oa_project::STATUS_UPDATE,
			'close' => voa_d_oa_project::STATUS_CLOSED
	);

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}

		$navmenu = array();
		$navmenu['links'] = array();
		$list_url = $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id);
		if ($list_url) {
			$navmenu['links']['list'] = array(
					'icon' => 'fa-list',
					'url' => $list_url,
					'name' => '项目列表',
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
	 * 获取指定项目的信息
	 * @param number $p_id
	 */
	protected function _get_project($cp_pluginid, $p_id) {
		$project = $this->_service_single('project', $cp_pluginid, 'fetch_by_id', $p_id);
		if (!empty($project)) {
			return $this->_format_project($project);
		} else {
			return false;
		}
	}

	/**
	 * 格式化项目信息
	 * @param array $project
	 * @return array
	 */
	protected function _format_project($project) {
		/** 更新时间 */
		$project['_updated'] = rgmdate($project['p_updated'] ? $project['p_updated'] : $project['p_created'], 'Y-m-d H:i');
		/** 开始时间 */
		$project['_begintime'] = rgmdate($project['p_begintime'], 'Y-m-d H:i');
		/** 结束时间 */
		$project['_endtime'] = rgmdate($project['p_endtime'], 'Y-m-d H:i');
		/** 剩余时间 */
		$project['_remaintime'] = voa_h_func::get_dhi($project['p_endtime'] - startup_env::get('timestamp'));
		/** 项目总时间 */
		$project['_totaltime'] = voa_h_func::get_dhi($project['p_endtime'] - $project['p_begintime']);
		/** 项目耗时时间 */
		$project['_usetime'] = voa_h_func::get_dhi(startup_env::get('timestamp') - $project['p_begintime']);
		/** 项目状态 */
		$project['_status'] = isset($this->_project_status[$project['p_status']]) ? $this->_project_status[$project['p_status']] : '';
		/** 项目关闭启用状态 */
		$project['_open'] = $project['p_status'] != $this->_project_open_config['close'] ? $this->_project_open_config['open'] : $this->_project_open_config['close'];
		/** 项目具体描述 */
		$project['_message'] = $this->_bbcode2html($project['p_message']);
		return $project;

	}

}
