<?php

/**
 * GuestbookSettingService.class.php
 * $author$
 */

namespace Dailyreport\Service;

use Common\Service\AbstractSettingService;
use Common\Common\Cache;

class DailyreportSettingService extends AbstractSettingService {

    // 插件名称
    const PLUGIN_NAME = 'dailyreport';

    // 构造方法
    public function __construct() {

        parent::__construct();
        $this->_d = D('DailyreportSetting');
    }

    public function save_wechat_menu($post,$menu,$pluginid,$agentid) {
        if ($this->_d->save_wechat_menu($post,$menu,$pluginid,$agentid)) {
            return false;
        }
        return true;
    }
    public function reset_wechat_menu($menu,$pluginid,$agentid) {
        if ($r_menu= $this->_d->reset_wechat_menu($menu,$pluginid,$agentid)) {
            return $r_menu;
        }
        return true;
    }
    public function get_wechat_menu(){
        return $this->_d->get_wechat_menu();
    }
}
