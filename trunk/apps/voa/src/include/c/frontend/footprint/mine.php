<?php
/**
 * 我的销售轨迹列表
* $Author$
* $Id$
*/

class voa_c_frontend_footprint_mine extends voa_c_frontend_footprint_base {
	protected $_start;
	protected $_perpage;
	protected $_page;
	protected $_updated;
	protected $_btime;
	protected $_etime;

	public function execute() {
		/** 获取分页参数 */
		$page = $this->request->get('page');
		list(
			$this->_start, $this->_perpage, $this->_page
		) = voa_h_func::get_limit($page, $this->_p_sets['perpage']);

		/** 更新时间 */
		$this->_updated = intval($this->request->get('updated'));
		$this->_updated = empty($this->_updated) ? (startup_env::get('timestamp') + 86400) : $this->_updated;

		/** 搜索条件 */
		$this->_btime = (string)$this->request->get('btime');
		$this->_btime = trim($this->_btime);
		$this->_etime = (string)$this->request->get('etime');
		$this->_etime = trim($this->_etime);
		$conditions = array('updated' => $this->_updated);
		$this->_so_conditions($conditions);

		/** 读取销售轨迹内容 */
		$serv = &service::factory('voa_s_oa_footprint_mem', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_mine(startup_env::get('wbs_uid'), $conditions, $this->_start, $this->_perpage);

		/** 取出 fp_id */
		$fp_ids = array();
		foreach ($list as $v) {
			$fp_ids[] = $v['fp_id'];
		}

		$uda = &uda::factory('voa_uda_frontend_footprint_format');
		/** 读取销售轨迹内容 */
		$serv = &service::factory('voa_s_oa_footprint', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_ids($fp_ids);
		/** 整理输出 */
		foreach ($list as &$v) {
			$uda->format($v);
			$this->_updated = $v['fp_updated'];
		}

		unset($v);

		/** 读取附件信息 */
		$fp_attachs = array();
		$this->_fetch_attach_by_fp_id($fp_ids, $fp_attachs);

		/** 读取回复信息 */
		$fp_posts = array();
		$this->_fetch_post_by_fp_id($fp_ids, $fp_posts);

		/** 职位 */
		$jobs = voa_h_cache::get_instance()->get('job', 'oa');

		$this->view->set('list', $list);
		$this->view->set('types', $this->_p_sets['types']['type']);
		$this->view->set('jobs', $jobs);
		$this->view->set('fp_attachs', $fp_attachs);
		$this->view->set('fp_posts', $fp_posts);
		$this->view->set('updated', $this->_updated);
		$this->view->set('perpage', $this->_perpage);
		$this->view->set('type_done', $this->type_done);

		/** 模板 */
		$tpl = 'footprint/mine';
		if (startup_env::get('inajax')) {
			$tpl = 'footprint/footprint_li';
		}

		$this->_output($tpl);
	}

	/**
	 * 搜索条件
	 * @param array $conditions
	 * @return boolean
	 */
	protected function _so_conditions(&$conditions) {
		$b_date = explode(' ', $this->_btime);
		$e_date = explode(' ', $this->_etime);
		$btime = rstrtotime($b_date[0]);
		$etime = rstrtotime($e_date[0]);
		if (0 == $btime) {
			$btime = rstrtotime(rgmdate(startup_env::get('timestamp'), 'Y-m-d 00:00:00'));
		}

		/** 如果结束时间小于当前时间, 则 */
		if (empty($this->_etime) || $etime < $btime) {
			$etime = $btime + 86400;
		}

		$conditions['btime'] = $btime;
		$conditions['etime'] = $etime;

		/** 输出年/月/日 */
		$this->view->set('date_from', rgmdate($btime - 86400 * 30, 'Y-m-d'));
		$this->view->set('date_to', rgmdate($btime + 86400 * 30, 'Y-m-d'));
		$this->view->set('date_current', rgmdate($btime, 'Y-m-d'));
		return true;
	}
}
