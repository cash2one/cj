<?php
/**
 * voa_c_admincp_office_invite_view
* 企业后台/微办公管理/微社群/查看
* Create By HuangZhongZheng
* $Author$
* $Id$
*/
class voa_c_admincp_office_invite_view extends voa_c_admincp_office_invite_base {

	public function execute() {
		$per_id = rintval($this->request->get('per_id'));
		try {
			$uda = &uda::factory('voa_uda_frontend_invite_get');
			$view = array();
			$uda->get_view($per_id, $view);
			$user_name = array();
			$user_name = voa_h_user::get($view['invite_uid']);
			if(isset($user_name['m_username'])){
				$view['invite_uid'] = $user_name['m_username'];
			}else {
				$view['invite_uid'] = '已删除';
			}
			$result = $view;
			//邀请情况数据处理
			/*if(isset($result['gz_state']) && $result['gz_state'] == 1){
				$result['m_qywxstatus'] = $this->_look_array[1];
			}elseif ($result['gz_state'] == 2){
				$result['m_qywxstatus'] = $this->_look_array[2];
			}elseif ($result['gz_state'] == 4 || $result['gz_state'] == 0 ||$result['gz_state'] == null){
				$result['m_qywxstatus'] = $this->_look_array[4];
			}*/
			$result['created'] = rgmdate($result['created'], 'Y-m-d H:i');;
		} catch (help_exception $h) {
			$this->_admincp_error_message($h);
		} catch (Exception $e) {
			logger::error($e);
			$this->_admincp_system_message($e);
		}
		$this->view->set('result',$result);

 		// 输出模板
		$this->output('office/invite/view');

	}

}
