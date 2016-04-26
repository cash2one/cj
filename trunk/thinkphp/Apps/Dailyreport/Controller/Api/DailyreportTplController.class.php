<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/12/18
 * Time: 下午2:03
 */

namespace Dailyreport\Controller\Api;
class DailyreportTplController extends AbstractController {
    protected $_require_login = true;
    /**
     * 添加模板
     * @return bool 添加状态
     */
    public function Add_post(){
        $post = I('post.');
        $serv_drt = D('DailyreportTpl', 'Service');
        if ($serv_drt->add_tpl($post)) {
            return true;
        };
        return false;
    }
    public function GetApiTplInDepartments_get(){
        $drt_id = intval(I('get.drt_id'));
        $cd_id = (int)I('get.cd_id');
        $serv_drt = D('DailyreportTpl', 'Service');
        if ($dps = $serv_drt->get_api_in_departments($drt_id,$cd_id)) {
            $this->_result=$dps;
            return true;
        };
        return false;
    }
    public function GetApiNewDailyreportTpls_get(){
        $serv_drt = D('DailyreportTpl', 'Service');
        if ($tpls = $serv_drt->get_api_news_dailyreport_tpls($this->_login->user['m_uid'])) {
            $this->_result = $tpls;
            return true;
        };
        return false;
    }
    
}
