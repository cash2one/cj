<?php
/**
 * voa_c_frontend_nvote_vote
 * h5投票
 * User: luckwang
 * Date: 15/3/18
 * Time: 上午11:29
 */

class voa_c_frontend_nvote_vote extends voa_c_frontend_nvote_base {

    public function execute()
    {
        $nvo_ids = $this->request->post('nvo_id');
        $nv_id = $this->request->post('nv_id');
        if (empty($nvo_ids)) {
            $this->_error_message('nvote_option_empty');
        }

        if (!is_array($nvo_ids)) {
            $nvo_ids = rintval($nvo_ids);
            $nvo_ids = array($nvo_ids);
        }


        $uda = &uda::factory('voa_uda_frontend_nvote_mem_option_add');
        /*入库操作*/
        if (!$uda->vote($nvo_ids, startup_env::get('wbs_uid'))) {
            $this->_error_message($uda->errmsg);
        }
	    
        //json编码返回
        echo rjson_encode(
            array(
                'errcode' => 0,
                'errmsg' => 'success',
                'timestamp' => startup_env::get('timestamp'),
                'result' => array(
                    'url' =>  "/frontend/nvote/view?nv_id={$nv_id}",
                    'message' => '投票成功'
                )
            )
        );
        exit;

        //$this->_output('mobile/nvote/list');
    }

}
