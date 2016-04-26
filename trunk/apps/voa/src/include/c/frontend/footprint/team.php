<?php
/**
 * 小组成员以及销售轨迹列表
 * $Author$
 * $Id$
 */

class voa_c_frontend_footprint_team extends voa_c_frontend_footprint_base {
	protected $_start;
	protected $_perpage;
	protected $_page;
	protected $_updated;
	protected $_sotext;

	public function execute() {
		/** 获取分页参数 */
		$page = $this->request->get('page');
		list(
			$this->_start, $this->_perpage, $this->_page
		) = voa_h_func::get_limit($page, $this->_p_sets['perpage']);

		/** 更新时间 */
		$this->_updated = intval($this->request->get('updated'));
		$this->_updated = empty($this->_updated) ? (startup_env::get('timestamp') + 86400) : $this->_updated;

		/** 读取向我分享轨迹的人 */
		$serv_t = &service::factory('voa_s_oa_footprint_team', array('pluginid' => startup_env::get('pluginid')));
		$team_users = $serv_t->fetch_by_to_uid(startup_env::get('wbs_uid'));

		/** 读取销售轨迹内容 */
		$serv = &service::factory('voa_s_oa_footprint_mem', array('pluginid' => startup_env::get('pluginid')));
		$list = $serv->fetch_by_uid_search(startup_env::get('wbs_uid'), array(), $this->_start, $this->_perpage);

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

		/** 读取用户信息 */
		$uids = array(
			startup_env::get('wbs_uid') => startup_env::get('wbs_uid')
		);
		foreach ($team_users as $u) {
			$uids[$u['m_uid']] = $u['m_uid'];
		}

		$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $serv_m->fetch_all_by_ids($uids);

		voa_h_user::push($users);

		$this->view->set('list', $list);
		$this->view->set('team_users', $team_users);
		$this->view->set('types', $this->_p_sets['types']['type']);
		$this->view->set('fp_attachs', $fp_attachs);
		$this->view->set('fp_posts', $fp_posts);
		$this->view->set('updated', $this->_updated);
		$this->view->set('perpage', $this->_perpage);

		/** 模板 */
		$tpl = 'footprint/team';
		if (startup_env::get('inajax')) {
			$tpl = 'footprint/footprint_li';
		}

		$this->_output($tpl);
	}
}
