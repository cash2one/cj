<?php
/**
* 学习人数
* Create By wowxavi
* $Author$
* $Id$
*/
class voa_c_admincp_office_jobtrain_studylist extends voa_c_admincp_office_jobtrain_base {

	public function execute() {
		$uda = &uda::factory('voa_uda_frontend_jobtrain_article');
		$aid = $this->request->get('aid');
		$m_uids = $this->request->get('m_uids');
		$notify = $this->request->get('notify');
		$is_study = rintval($this->request->get('is_study'));

		if($notify){
			// 发送提醒
			if (empty($m_uids)) {
				$this->message('error', '请指定要提醒的人员');
			}
			try {
				if(!is_array($m_uids)){
					$m_uids = array( rintval($m_uids) );
				}
				startup_env::set('pluginid', $this->_module_plugin_id);
				startup_env::set('agentid', $this->_module_plugin['cp_agentid']);
				$article = $uda->get_article($aid);
				// 获取用户 m_openid
				$serv_m = &service::factory('voa_s_oa_member', array('pluginid' => 0));
				$users = $serv_m->fetch_all_by_ids($m_uids);
				$touser = implode('|', array_column($users, 'm_openid'));
				$msg_title = "【提醒】".rhtmlspecialchars($article['title']);
				$msg_desc = '发送人：' . $this->_user['ca_username'];
				$msg_desc .= "\n摘要：" . rhtmlspecialchars($article['summary']);
				$scheme = config::get('voa.oa_http_scheme');
				$msg_url = $scheme . $this->_setting['domain'] . '/Jobtrain/Frontend/Index/ArticleDetail?aid=' . $aid;
				$msg_picurl = voa_h_attach::attachment_url($article['cover_id'], 0);
				// 发送消息
				voa_h_qymsg::push_news_send_queue($this->session, $msg_title, $msg_desc, $msg_url, $touser, '', $msg_picurl);

			} catch (help_exception $h) {
				$this->_admincp_error_message($h);
			} catch (Exception $e) {
				logger::error($e);
				$this->_admincp_system_message($e);
			}
			$this->message('success', '发送提醒完成', $this->cpurl($this->_module, $this->_operation, 'studylist', $this->_module_plugin_id, array('aid' => $aid)), false);
		}

		$limit = 20;   // 每页显示数量
		$page = $this->request->get('page');   // 当前页码
		if (!is_numeric($page) || $page < 1) {
			$page = 1;
		}
		list($start, $limit, $page) = voa_h_func::get_limit($page, $limit);
		$page_option = array($start, $limit);

		try {
			// 获取内容
			$article = $uda->get_article($aid);
			// 获取分类
			$uda_cata = &uda::factory('voa_uda_frontend_jobtrain_category');
			$cata = $uda_cata->get_cata($article['cid']);
			// 载入搜索uda类
			$uda_list = &uda::factory('voa_uda_frontend_jobtrain_study');
			// 数据结果
			$result = array();
			$uda_list->list_study($result, array('is_study'=>$is_study,'aid'=>$aid), $page_option, $cata);

			if($is_study==0){
				$result['total'] = $article['study_sum']-$article['study_num'];
			}else{

				$result['total'] = $article['study_num'];
			}

		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}

		// 分页链接信息
		$multi = '';
		if ($result['total'] > 0) {
			// 输出分页信息
			$multi = pager::make_links(array(
				'total_items' => $result['total'],
				'per_page' => $limit,
				'current_page' => $page,
				'show_total_items' => true,
			));
		}
		// 注入模板变量
		$this->view->set('total', $result['total']);
		$this->view->set('list', $result['list']);
		$this->view->set('multi', $multi);
		$this->view->set('article', $article);
		$this->view->set('is_study', $is_study);
		$this->view->set('aid', $aid);
		// 切换链接
		$this->view->set('form_tabs_url', $this->cpurl($this->_module, $this->_operation, 'studylist', $this->_module_plugin_id));
		// 导出链接
		$this->view->set('study_export_url', $this->cpurl($this->_module, $this->_operation, 'studyexport', $this->_module_plugin_id, array('is_study' => $is_study, 'aid' => $aid)));
		// 通知链接
		$this->view->set('notify_url', $this->cpurl($this->_module, $this->_operation, 'studylist', $this->_module_plugin_id));
		// 返回链接
		$this->view->set('return_url', $this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id));
		$this->output('office/jobtrain/studylist');
	}

}