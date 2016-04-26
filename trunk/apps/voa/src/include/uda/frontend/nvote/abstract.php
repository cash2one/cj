<?php
/**
 * voa_uda_frontend_nvote_abstract
 * 投票调研-uda
 * User: luckwang
 * Date: 15/3/9
 * Time: 上午11:20
 */


class voa_uda_frontend_nvote_abstract extends voa_uda_frontend_base {

    public function __construct() {

        parent::__construct();

        // 初始化 service
        if (null === $this->_serv) {
            $this->_serv = new voa_s_oa_nvote();
        }

        //应用设置缓存
        $this->_sets = voa_h_cache::get_instance()->get('plugin.nvote.setting', 'oa');
        //全局设置缓存
        $this->_setting = voa_h_cache::get_instance()->get('setting', 'oa');
        //应用信息缓存
        $this->_plugins = voa_h_cache::get_instance()->get('plugin', 'oa');

    }
}
