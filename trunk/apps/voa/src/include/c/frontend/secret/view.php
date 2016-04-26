<?php
/**
 * 查看信息
 * Author: Arice
 * $Id$
 */

class voa_c_frontend_secret_view extends voa_c_frontend_secret_base {

	public function execute() {
		/** 根据 st_id 查询秘密内容 */
		$st_id = rintval($this->request->get('st_id'));
		$serv = &service::factory('voa_s_oa_secret', array('pluginid' => startup_env::get('pluginid')));
		$secret = $serv->fetch_by_id($st_id);
		if (!$secret) {
			$this->_error_message('当前秘密不存在');
		}

		/** 过滤 html 代码 */
		$secret['st_subject'] = rhtmlspecialchars($secret['st_subject']);

		/** 查询秘密内容 */
		$serv_p = &service::factory('voa_s_oa_secret_post', array('pluginid' => startup_env::get('pluginid')));
		$posts = $serv_p->fetch_by_st_id($st_id);
		/** 整理输出所需数据 */
		$stp_ids = array();
		foreach ($posts as $k => &$v) {
			$v['stp_subject'] = rhtmlspecialchars($v['stp_subject']);
			$v['stp_message'] = rhtmlspecialchars($v['stp_message']);
			$v['stp_message'] = bbcode::instance()->bbcode2html($v['stp_message']);
			/** 如果是主题内容, 则 */
			if (voa_d_oa_secret_post::FIRST_YES == $v['stp_first']) {
				$secret = array_merge($v, $secret);
				unset($posts[$k]);
				continue;
			}

			$stp_ids[$v['stp_id']] = $v['stp_id'];
			$v['_created'] = rgmdate($v['stp_created'], 'u');
		}

		unset($v);

		$this->view->set('st_id', $st_id);
		$this->view->set('secret', $secret);
		$this->view->set('posts', $posts);
		$this->view->set('navtitle', '秘密详情');

		$this->_output('secret/view');
	}

}
