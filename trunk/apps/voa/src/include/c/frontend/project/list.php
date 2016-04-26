<?php
/**
 * 任务列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_project_list extends voa_c_frontend_project_base {
	/** 分页查询相关 */
	protected $_start;
	protected $_perpage;
	protected $_page;
	/** 更新时间 */
	protected $_updated;
	/** 列表 */
	protected $_list;

	public function execute() {
		/** 获取分页参数 */
		$page = $this->request->post('page');
		if(empty($page)){
			$page = 1;
		}
		list(
			$this->_start, $this->_perpage, $this->_page
		) = voa_h_func::get_limit($page, isset($this->_p_sets['perpage']) ? $this->_p_sets['perpage'] : 10);

		/**
		 * 动作集合
		 * my: 我的申请列表
		 * done: 已完成
		 * closed: 已关闭
		 */
		$acs = array('my', 'closed', 'done');

		/** 动作 */
		$ac = trim($this->request->get('ac'));
		if (!in_array($ac, $acs)) {
			$ac = 'my';
		}

		/** 更新时间 */
		$this->_updated = intval($this->request->get('updated'));
		$this->_updated = empty($this->_updated) ? startup_env::get('timestamp') + 1 : $this->_updated;

		/** 调用处理方法 */
		$list = array();
		$func = '_'.$ac;
		if (!method_exists($this, $func)) {
			$this->_error_message('undefined_action');
			return false;
		}

		call_user_func(array($this, $func));

		// 需要更新状态，修正旧数据的完成状态 By Deepseath@20141222
		$update_status = array();
		/** 整理输出 */
		foreach ($this->_list as &$v) {

			// 如果进度值是100，但状态不是已完成，则标记为已完成，此处是为了修正旧数据 By Deepseath@20141222
			if (100 == $v['p_progress'] && voa_d_oa_project::STATUS_COMPLETE != $v['p_status']) {
				$update_status[] = $v['p_id'];
				$v['p_status'] = voa_d_oa_project::STATUS_COMPLETE;
			}

			$v['_created'] = rgmdate($v['p_created'], 'u');
			$v['_updated'] = rgmdate($v['p_updated']);
			if ($this->_updated > $v['pm_updated']) {
				$this->_updated = $v['pm_updated'];
			}
		}

		// 更新状态，修正旧数据的完成状态 By Deepseath@20141222
		if (!empty($update_status)) {
			$this->_serv->update(array('p_status' => voa_d_oa_project::STATUS_COMPLETE)
					, array('p_id' => $update_status));
		}

		unset($v);
		$count = count(rhtmlspecialchars($this->_list));
		$this->view->set('list', rhtmlspecialchars($this->_list));
		$this->view->set('updated', $this->_updated);
		$this->view->set('perpage', $this->_perpage);
		$this->view->set('page', $page);
		$this->view->set('count', $count);
		$inajax = startup_env::get('inajax');
		if (empty($inajax)) {
			$this->_output('project/list');
		} else {
			$this->_output('project/list_li');
		}
	}

	/** 获取自己的任务列表 */
	function _my() {
		$serv = &service::factory('voa_s_oa_project', array('pluginid' => startup_env::get('pluginid')));
		$this->_list = $serv->fetch_my_by_uids_updated(
			$this->_user['m_uid'], $this->_updated, $this->_start, $this->_perpage
		);
	}

	/** 获取已关闭的任务数 */
	function _closed() {
		$serv = &service::factory('voa_s_oa_project', array('pluginid' => startup_env::get('pluginid')));
		$this->_list = $serv->fetch_closed_by_uids_updated(
			$this->_user['m_uid'], $this->_updated, $this->_start, $this->_perpage
		);
	}

	/** 读取已完成的 */
	function _done() {
		$serv = &service::factory('voa_s_oa_project', array('pluginid' => startup_env::get('pluginid')));
		$this->_list = $serv->fetch_done_by_uids_updated(
			$this->_user['m_uid'], $this->_updated, $this->_start, $this->_perpage
		);
	}
}
