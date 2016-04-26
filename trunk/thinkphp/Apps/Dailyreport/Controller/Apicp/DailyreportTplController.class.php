<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/12/18
 * Time: 下午2:03
 */

namespace Dailyreport\Controller\Apicp;
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
    /**
     * 
     */
    public function Gettpl_get(){
        $drt_id = intval(I('get.drt_id'));
        $serv_drt = D('DailyreportTpl', 'Service');
        if ($tpl = $serv_drt->get_tpl($drt_id)) {
            $this->_result=$tpl;
            return true;
        };
        return false;
    }
    /**
     * 获取模板的分页列表
     * @return boolean
     */
    public function GetList_get(){
        $page = I('get.page');
        $serv_drt = D('DailyreportTpl', 'Service');
        if ($tpls = $serv_drt->get_list($page)) {
            $this->_result=$tpls;
            return true;
        };
        return false;
    }
    public function Save_post(){
        $post = I('post.');
        $serv_drt = D('DailyreportTpl', 'Service');
        if ($serv_drt->save_tpl($post)) {
            return true;
        };
        return false;
    }
    /**
     * 禁用/启用模版
     */
    public function Switch_get(){
        $drt_id = I('get.drt_id');
        $drt_switch = I('get.drt_switch');
        $serv_drt = D('DailyreportTpl', 'Service');
        if ($serv_drt->switch_tpl($drt_id,$drt_switch)) {
            return true;
        };
        return false;
    }
    /**
     * 删除模板
     */
    public function Del_get(){
        $drt_id = (int)I('get.drt_id');
        $serv_drt = D('DailyreportTpl', 'Service');
        if ($serv_drt->del_tpl($drt_id)) {
            return true;
        };
        return false;
    }
    /**
     * 新增时获取排序
     */
    public function GetTplSort_get(){
        $serv_drt = D('DailyreportTpl', 'Service');
        if ($sort = $serv_drt->get_tpl_sort()) {
            $this->_result=$sort;
            return true;
        };
        return false;
    }
}
