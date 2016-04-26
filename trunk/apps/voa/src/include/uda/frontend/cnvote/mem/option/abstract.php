<?php
/**
 * voa_uda_frontend_cnvote_mem_option_abstract
 * mem_option 基类
 * User: luckwang
 * Date: 15/3/11
 * Time: 上午10:46
 */
class voa_uda_frontend_cnvote_mem_option_abstract extends voa_uda_frontend_base {

    public function __construct() {
        parent::__construct();

        // 初始化 service
        if (null === $this->_serv) {
            $this->_serv = &service::factory('voa_s_oa_cnvote_mem_option');
        }
    }
}
