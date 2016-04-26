<?php
/**
 * voa_c_frontend_nvote_close
 * h5关闭投票
 * User: luckwang
 * Date: 15/3/18
 * Time: 上午11:29
 */

class voa_c_frontend_nvote_close extends voa_c_frontend_nvote_base {

    public function execute()
    {
        $nv_id = rintval($this->request->post('nv_id'));
        if (empty($nv_id)) {
            $this->_error_message('nvote_empty');
        }

        $uda = &uda::factory('voa_uda_frontend_nvote_close');
        /*入库操作*/
        if (!$uda->close(startup_env::get('wbs_uid'), $nv_id, true)) {
            $this->_error_message($uda->errmsg);
        }

        echo rjson_encode(
            array(
                'errcode' => 0,
                'errmsg' => 'success',
                'timestamp' => startup_env::get('timestamp'),
                'result' => array(
                    'url' =>  "/frontend/nvote/view?nv_id={$nv_id}",
                    'message' => '关闭投票成功'
                )
            )
        );
        exit();

        //$this->_output('mobile/nvote/list');
    }

}
