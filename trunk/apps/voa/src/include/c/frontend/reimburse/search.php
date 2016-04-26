<?php
/**
 * 搜索报销列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_reimburse_search extends voa_c_frontend_reimburse_base {
	/** 分页查询相关 */
	protected $_start;
	protected $_perpage;
	protected $_page;
	/** 更新时间 */
	protected $_updated;

	public function execute() {
		/** 获取分页参数 */
		$page = $this->request->get('page');
		list(
			$this->_start, $this->_perpage, $this->_page
		) = voa_h_func::get_limit($page, $this->_p_sets['perpage']);

		/** 更新时间 */
		$this->_updated = intval($this->request->get('updated'));
		$this->_updated = empty($this->_updated) ? (startup_env::get('timestamp') + 86400) : $this->_updated;

		$this->_sotext = (string)$this->request->get('sotext');
		$this->_sotext = trim($this->_sotext);

		/** 搜索条件 */
		$ac = (string)$this->request->get('ac');
		$conditions = array('updated' => $this->_updated);
		$this->_so_conditions($conditions);

		/** 读取内容 */
		$serv = &service::factory('voa_s_oa_reimburse_proc', array('pluginid' => startup_env::get('pluginid')));
		if ('mine' == $ac) {
			$list = $serv->fetch_mine(startup_env::get('wbs_uid'), $this->_updated, $this->_start, $this->_perpage);
		} elseif ('dealing' == $ac) {
			$sts = array(
				voa_d_oa_reimburse_proc::STATUS_NORMAL
			);
			$list = $serv->fetch_deal(startup_env::get('wbs_uid'), $this->_updated, $sts, $this->_start, $this->_perpage);
		} elseif ('dealed' == $ac) {
			$sts = array(
				voa_d_oa_reimburse_proc::STATUS_APPROVE,
				voa_d_oa_reimburse_proc::STATUS_REFUSE,
				voa_d_oa_reimburse_proc::STATUS_TRANSMIT
			);
			$list = $serv->fetch_deal(startup_env::get('wbs_uid'), $this->_updated, $sts, $this->_start, $this->_perpage);
		} else {
			$list = $serv->fetch_mine(startup_env::get('wbs_uid'), $this->_updated, $this->_start, $this->_perpage);
		}

		/** 整理输出 */
		$fmt = &uda::factory('voa_uda_frontend_reimburse_format');
		foreach ($list as &$v) {
			$fmt->reimburse($v);
			$v['_created_fmt'] = voa_h_func::date_fmt('y m d w', $v['rb_created']);
			list($v['_ymd'], $v['_hi']) = explode(' ', $v['_created']);
			$this->_updated = $v['rb_updated'];
		}

		unset($v);

		$this->view->set('list', $list);
		$this->view->set('updated', $this->_updated);
		$this->view->set('ac', $ac);
		$this->view->set('sotext', $this->_sotext);
		$this->view->set('weeknames', config::get('voa.misc.weeknames'));
		$this->view->set('perpage', $this->_perpage);
		$this->view->set('navtitle', '报销列表');

		/** 模板 */
		$tpl = 'reimburse/search';
		if (startup_env::get('inajax')) {
			$tpl .= '_li';
		}

		$this->_output($tpl);
	}

	/**
	 * 搜索条件
	 * @param array $conditions
	 * @return boolean
	 */
	protected function _so_conditions(&$conditions) {

		return true;
	}
}
