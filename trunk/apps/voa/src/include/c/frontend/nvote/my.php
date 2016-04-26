<?php
/**
 * voa_c_frontend_nvote_my
 * 投票调研-我的投票
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午11:01
 */

class voa_c_frontend_nvote_my extends voa_c_frontend_nvote_base {

    public function execute()
    {
	    $action = $this->request->get('nvote');

	    // 当前动作
	    $this->view->set('action', $action);
        $this->_output('mobile/nvote/my');
    }

}
