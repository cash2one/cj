<?php
/**
 * voa_c_api_project_get_list
 * 任务列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
class voa_c_api_project_get_list extends voa_c_api_project_base {

	/** 当前查询的数据起始行 */
	protected $_start;
	/** 数据总数 */
	protected $_total;
	/** 数据列表 */
	protected $_list;
	/** 任务表 */
	protected $_serv;
	/** 最后更新时间 */
	protected $_updated = 0;

	public function execute() {

		// 需要的参数
		$fields = array(
			// 当前页码
			'page' => array('type' => 'int', 'required' => false),
			// 每页显示数据数
			'limit' => array('type' => 'int', 'required' => false),
			// 读取的列表类型
			'action' => array('type' => 'string', 'required' => false)
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

		if (empty($this->_params['action'])) {
			// 设置当前的动作
			$this->_params['action'] = 'my';
		}

		/**
		 * 动作集合
		 * my: 我的申请列表
		 * done: 已完成
		 * closed: 已关闭
		 */
		$acs = array('my', 'closed', 'done');
		if (!in_array($this->_params['action'], $acs)) {
			// 设置默认动作为 我的申请列表
			return $this->_set_errcode(voa_errcode_api_project::LIST_UNDEFINED_ACTION, $this->_params['action']);
		}

		// 获取分页参数
		list($this->_start, $this->_params['limit'], $this->_params['page']) = voa_h_func::get_limit($this->_params['page'], $this->_params['limit'], 100);

		// 更新时间
		$this->_updated = startup_env::get('timestamp') + 10;
		/*
		if (!is_numeric($this->_updated)) {
			$this->_updated = rstrtotime($this->_updated);
		}
		$this->_updated = $this->_updated < 1 ? startup_env::get('timestamp') + 1 : $this->_updated;
		*/

		// 调用处理方法
		$list = array();
		$func = '_'.$this->_params['action'];
		if (!method_exists($this, $func)) {
			return $this->_set_errcode(voa_errcode_api_project::LIST_UNDEFINED_FUNCTION, $this->_params['action']);
		}

		// 任务表
		$this->_serv = &service::factory('voa_s_oa_project', array('pluginid' => $this->_pluginid));

		// 呼叫对应动作方法
		call_user_func(array($this, $func));

		// 任务状态文字
		$status_map = array(
			voa_d_oa_project::STATUS_NORMAL => '进行中',
			voa_d_oa_project::STATUS_UPDATE => '进行中',
			voa_d_oa_project::STATUS_COMPLETE => '已完成',
			voa_d_oa_project::STATUS_CLOSED => '已关闭',
			voa_d_oa_project::STATUS_REMOVE => '已删除'
		);

		// 需要更新状态，修正旧数据的完成状态 By Deepseath@20141222
		$update_status = array();
		// 整理数据
		$list = array();
		foreach ($this->_list as $_p_id => $_p) {

			// 如果进度值是100，但状态不是已完成，则标记为已完成，此处是为了修正旧数据 By Deepseath@20141222
			if (100 == $_p['p_progress'] && voa_d_oa_project::STATUS_COMPLETE != $_p['p_status']) {
				$update_status[] = $_p_id;
				$_p['p_status'] = voa_d_oa_project::STATUS_COMPLETE;
			}

			$list[] = array(
				'id' => $_p_id,// 任务ID
				'uid' => $_p['m_uid'],// 创建者uid
				'username' => $_p['m_username'],// 创建者名字
				'subject' => $_p['p_subject'],// 任务名称
				'message' => $_p['p_message'],// 任务说明
				'begintime' => $_p['p_begintime'],// 任务开始时间
				'endtime' => $_p['p_endtime'],// 任务结束时间
				'progress' => $_p['p_progress'],// 任务进度0-100
				'status' => $status_map[$_p['p_status']],// 任务状态 进行中、已完成、已关闭
				'createdtime' => $_p['p_created'],// 任务创建时间
				'updatedtime' => $_p['p_updated']// 任务更新时间
			);
		}

		// 更新状态，修正旧数据的完成状态 By Deepseath@20141222
		if (!empty($update_status)) {
			$this->_serv->update(array('p_status' => voa_d_oa_project::STATUS_COMPLETE)
					, array('p_id' => $update_status));
		}

		// 输出结果
		$this->_result = array(
			'total' => $this->_total,
			'limit' => $this->_params['limit'],
			'page' => $this->_params['page'],
			'data' => $list
		);

		return true;
	}

	/** 获取自己的任务列表 */
	function _my() {
		$this->_total = $this->_serv->count_my_by_uids_updated($this->_member['m_uid'], $this->_updated);
		$this->_list = $this->_serv->fetch_my_by_uids_updated($this->_member['m_uid'], $this->_updated, $this->_start, $this->_params['limit']);
	}

	/** 获取已关闭的任务数 */
	function _closed() {
		$this->_total = $this->_serv->count_closed_by_uids_updated($this->_member['m_uid'], $this->_updated);
		$this->_list = $this->_serv->fetch_closed_by_uids_updated($this->_member['m_uid'], $this->_updated, $this->_start, $this->_params['limit']);
	}

	/** 读取已完成的 */
	function _done() {
		$this->_total = $this->_serv->count_done_by_uids_updated($this->_member['m_uid'], $this->_updated);
		$this->_list = $this->_serv->fetch_done_by_uids_updated($this->_member['m_uid'], $this->_updated, $this->_start, $this->_params['limit']);
	}

}
