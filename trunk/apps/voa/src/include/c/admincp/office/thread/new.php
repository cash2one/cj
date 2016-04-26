<?php

/**
 * voa_c_admincp_office_thread_new
 * 企业后台/同事社区/新建话题
 * Create By gaosong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_thread_new extends voa_c_admincp_office_thread_base
{

	public function execute()
	{
		if ($this->_is_post()) {
			$thread = array();
			// 事务开始
			try {
				$p_sets = voa_h_cache::get_instance()->get('plugin.thread.setting', 'oa'); // 读同事社区配置缓存
				voa_uda_frontend_transaction_abstract::s_begin();
				$uda_add = &uda::factory('voa_uda_frontend_thread_add');
				$post = $this->request->getx();
				$send_flag = $this->request->get("ck_msg");
				$post['username'] = $p_sets['offical_name']; //官网昵称
				$post['at_ids'] = $post['cover2_id'];
				$uda_add->execute($post, $thread);

				//发送消息通知企业员工
				if (! empty($send_flag) && ! empty($thread)) {
					// 发送消息通知
					$uda_add->send_msg($thread, 'new', startup_env::get('wbs_uid'), $this->session);
				}

				// 提交事务
				voa_uda_frontend_transaction_abstract::s_commit();

				$this->message('success', '新建话题成功', get_referer($this->cpurl($this->_module, $this->_operation, 'list', $this->_module_plugin_id)), false);
			} catch (help_exception $e) {
				// 事务回滚
				voa_uda_frontend_transaction_abstract::s_rollback();
				$this->_error_message($e->getMessage());
				return false;
			}
		}

		$this->view->set('formActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
		$this->output('office/thread/thread_form');
	}
}
