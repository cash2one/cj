<?php
/**
 * 日程列表
 *
 * $Author$
 * $Id$
 */

class voa_c_frontend_plan_list extends voa_c_frontend_plan_base {
	protected $_y;
	protected $_n;
	protected $_year;
	protected $_month;

	public function __construct() {
		parent::__construct();

		/** 当前年/月 */
		$this->_y = rgmdate(startup_env::get('timestamp'), 'Y');
		$this->_n = rgmdate(startup_env::get('timestamp'), 'm');

		/** 如果有GET过来的年月，用GET的 */
		$this->_year  = intval($this->request->get('year'));
		$this->_year  = empty($this->_year) ? $this->_y : $this->_year;
		$this->_month = intval($this->request->get('month'));
		$this->_month = empty($this->_month) ? $this->_n : $this->_month + 1;
	}

	public function execute() {
		/**
		 * refresh: 刷新待办事项
		 * list: 列表页
		 */
		$acs = array(
			'list',
			'delete',
			'refresh',
			'ajaxMonth'
		);

		/** 检查是否可以调用 */
		$ac = $this->request->get('ac');
		$func = in_array($ac, $acs) ? '_' . $ac : '_list';

		/** 尝试回调执行 */
		if (method_exists($this, $func)) {
			return call_user_func(array(
				$this,
				$func
			));
		}
	}

	protected function _list() {
		$plans = $this->main->fetch_by_conditions(array('m_uid' => startup_env::get('wbs_uid')));

		/** 整理输出 */
		foreach ($plans as &$value) {
			$this->format->inList($value);
		}

		unset($value);

		/**
		 * 日期范围选择器处理
		 * 目前前端太忙了，所以hardcoding一下解决不能传0X月份
		 * @return 返回当前选中月份的前后90天
		 */
		$currentTimeStamp = rstrtotime($this->_year . $this->_month);
		$startTimeStamp = $currentTimeStamp - 90 * 86400;
		$endTimeStamp = $currentTimeStamp + 90 * 86400;

		$start_year = rgmdate($startTimeStamp, 'Y');
		$start_month = (int)rgmdate($startTimeStamp, 'm') <10?"0".(int)rgmdate($startTimeStamp, 'm'):(int)rgmdate($startTimeStamp, 'm');
		$end_year = rgmdate($endTimeStamp, 'Y');
		$end_month = (int)rgmdate($endTimeStamp, 'm')<10?"0".(int)rgmdate($endTimeStamp, 'm'):(int)rgmdate($endTimeStamp, 'm');

		// echo $currentTimeStamp . ' ' . $startTimeStamp . ' ' . $endTimeStamp . '<hr>';
		// echo $start_year . ' ' . $start_month . ' ' . $end_year . ' ' . $end_month . '<hr>';

		$this->view->set('start_year', $start_year);
		$this->view->set('start_month', $start_month);
		$this->view->set('end_year', $end_year);
		$this->view->set('end_month', $end_month);
		$this->view->set('year', $this->_year);
		$this->view->set('month', (int)$this->_month);

		$this->view->set('list', $plans);
		$this->view->set('form_action', '/plan/new');
		$this->view->set('navtitle', '日程安排');

		$this->_output('plan/list');
	}

	/** 获得某日的日程 */
	protected function _ajaxMonth() {
		$month = $this->request->get('date');

		$rows = $this->main->get_many_by_uid_and_month(startup_env::get('wbs_uid'), $month);

		$ret = empty($rows) ? array('response' => 'fail') : array('tododays' => $rows);

		$this->_json_message($ret);
	}

	/** 删除 */
	protected function _delete() {
		$id = $this->request->get('id');

		// 只允许操作自己的
		$plan = $this->main->fetch_by_id($id);

		if ($plan['m_uid'] !== startup_env::get('wbs_uid')) {
			$this->_error_message('no_privilege');
		}

		try {
			// 开始存储过程
			$this->main->begin();

			// 删除主表记录
			$this->main->delete_by_ids(array('td_id' => $id));

			// 删除回复表记录
			$this->member->delete_by_pl_ids(array($id));

			// 提交存储过程
			$this->main->commit();
		} catch (Exception $e) {
			$this->main->rollback();
			$this->errmsg(100, '操作失败');
			return false;
		}

		$this->_json_message(array('response' => 'success'));
	}
}
