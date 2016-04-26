<?php

/**
 * 快递代领
 * $Author$
 * $Id$
 */
class voa_c_frontend_express_newexpress extends voa_c_frontend_express_base
{

	public function execute()
	{
		$eid = rintval($this->request->get('eid'));
		
		//查看快递是否已领取
		$uda = &uda::factory('voa_uda_frontend_express_view');
		$express = array();
		$uda->execute(array('eid'=>$eid),$express);
		
		$this->view->set('navtitle', '设置代领');
		//快递已领取返回提示页面
		if ($express['flag'] == voa_d_oa_express::GET_NO) {
			if ($this->_is_post()) {
				$this->_submit();
				return false;
			}
			$this->view->set('myuid',startup_env::get('wbs_uid'));
			$this->view->set('eid',$eid);
			$this->_output('mobile/express/lead');
		} else {
			$this->_output('mobile/express/leaderror');
		}
	}

	/**
	 * 设置代领人
	 * @return boolean
	 */
	protected function _submit()
	{
		try {
			// 事务开始
			voa_uda_frontend_transaction_abstract::s_begin();

			$params = $this->request->postx();
			//读取用户信息
			$serv_m = &service::factory('voa_s_oa_member');
			$users = $serv_m->fetch_by_uid($params['uid']);
			$params['username'] = $users['m_username'];
			
			$uda = &uda::factory('voa_uda_frontend_express_mem_add');
			//收件人信息
			$express_mem = array();
			if (!$uda->execute($params, $express_mem)) {
				$this->_error_message($uda->errmsg);
			}

            if(!empty($express_mem)) {
	            $uda->send_msg($express_mem, 'lead', $this->session);
            }
			// 提交事务
			voa_uda_frontend_transaction_abstract::s_commit();

			$this->_success_message('操作成功', "/frontend/express/view/eid/{$express_mem['eid']}");
		} catch (help_exception $e) {
			// 事务回滚
			voa_uda_frontend_transaction_abstract::s_rollback();
			$this->_error_message($e->getMessage());
			return false;
		}
	}
}
