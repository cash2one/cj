<?php

/**
 * User: keller
 * Date: 16/3/16
 * Time: 下午10:53
 */
namespace Dailyreport\Controller\Api;

class DailyreportController extends AbstractController {
    protected $_require_login = true;
    /**
     * 我发出的报告列表
     * @author xiebinbin<xiebinbin@vchangyi.com>
     */
    public function GetMySendDailyreportList_get(){
        $page = I('get.page');
        $q=I('get.q','');
        $k=I('get.k','');
        $drt_id=intval(I('get.drt_id',0));
        $serv_drt = D('Dailyreport', 'Service');
        $m_uid=intval($this->_login->user['m_uid']);
        if ($list = $serv_drt->get_my_send_dailyreport_list($m_uid,$page,$q,$k,$drt_id)){
            $this->_result=$list;
            return true;
        };
        return false;
    }
    /**
     * 回显报告
     * @author xiebinbin<xiebinbin@vchangyi.com>
     * @return boolean
     */
    public function GetDailyreportInfoApi_get(){
        $dr_id= intval(I('get.dr_id'));
        $m_uid = intval($this->_login->user['m_uid']);
        $serv_drt = D('Dailyreport', 'Service');
        if ($dailyreport = $serv_drt->get_dailyreport_info_api($dr_id,$m_uid)){
            $this->_result=$dailyreport;
            return true;
        };
        return false;
    }
    /**
     * 保存报告
     * @author xiebinbin<xiebinbin@vchangyi.com>
     * @return boolea
     */
    public function SaveDailyreportApi_post(){
        $post =I('post.');
        $serv_drt = D('Dailyreport', 'Service');
        $m_uid = $this->_login->user['m_uid'];
        $m_username=$this->_login->user['m_username'];
        if ($dr_id = $serv_drt->save_api_dailyreport($post,$m_uid,$m_username)){
            $this->_result = $dr_id;
            return true;
        };
        return false;
    }
    /**
     * 编辑草稿
     * @author xiebinbin<xiebinbin@vchangyi.com>
     * @return boolean
     */
    public function EditDraftxApi_post(){
        $post =I('post.');
        $serv_drt = D('Dailyreportx', 'Service');
        $m_uid = intval($this->_login->user['m_uid']);
        if ($serv_drt->edit_draftx($post,$m_uid)){
            return true;
        };
        return false;
    }
    /**
     * 保存草稿
     * @author xiebinbin<xiebinbin@vchangyi.com>
     * @return boolean
     */
    public function SaveDraftxApi_post(){
        $post =I('post.');
        $serv_drt = D('Dailyreportx', 'Service');
        $m_uid = intval($this->_login->user['m_uid']);
        if ($serv_drt->add_draftx($post,$m_uid)){
            return true;
        };
        return false;
    }
    /**
     * 获取草稿列表
     * @author xiebinbin<xiebinbin@vchangyi.com> boolean
     */
    public function GetDraftxList_get(){
        $page = I('get.page');
        $q=I('get.q','');
        $k=I('get.k','');
        $drt_id=intval(I('get.drt_id',0));
        $serv_drt = D('Dailyreportx', 'Service');
        $m_uid=intval($this->_login->user['m_uid']);
        if ($list = $serv_drt->get_draftx_list($m_uid,$page,$q,$k,$drt_id)){
            $this->_result=$list;
            return true;
        };
        return false;
    }
    /**
     * 获取草稿
     * @author xiebinbin<xiebinbin@vchangyi.com>
     */
    public function GetApiDraftx_get(){
        $drd_id=intval(I('get.drd_id',0));
        $serv_drt = D('Dailyreportx', 'Service');
        if ($drd = $serv_drt->get_api_draftx($drd_id)){
            $this->_result=$drd;
            return true;
        };
        return false;
    }
    /**
     * 删除草稿
     * @author xiebinbin<xiebinbin@vchangyi.com>
     * @return boolean
     */
    public function DelApiDraftx_get(){
        $drd_id=I('get.drd_id',0);
        $m_uid = intval($this->_login->user['m_uid']);
        $serv_drt = D('Dailyreportx', 'Service');
        if ($serv_drt->del_api_draftx($drd_id,$m_uid)){
            return true;
        };
        return false;
    }
    /**
     * 获取模板的列表
     * @author xiebinbin<xiebinbin@vchangyi.com>
     * @route
     * @method$this->_login->user
     */
    public function GetApiTplList_get() {
        $serv_drt = D('DailyreportTpl', 'Service');
        $target_id=(int)I('get.target_id',0);
        $m_uid=$this->_login->user['m_uid'];
        if ($tpls = $serv_drt->get_api_tpl_list($m_uid,(int)I('get.type'),$target_id)) {
            $this->_result = $tpls;
            return true;
        };
        return false;
    }

    /**
     * 新建时获取模板的信息
     * @author xiebinbin<xiebinbin@vchangyi.com>
     * @route
     * @method$this->_login->user
     */
    public function GetApiTplInfo_get() {
        $drt_id = I('get.drt_id');
        $serv_drt = D('DailyreportTpl', 'Service');
        if ($tpl = $serv_drt->get_api_tpl_info($drt_id, $this->_login->user['m_username'])) {
            $this->_result = $tpl;
            return true;
        };
        return false;
    }
    /**
     * 获取评论列表
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     * @return bool
     */
    public function CommentList_get(){
        $param = I('get.');
        $serv_dr = D('Dailyreport', 'Service');
        if ($comments = $serv_dr->get_comment_list($param)) {
            $this->_result = $comments;
            return true;
        };
        return false;
    }

    /**
     * 评论保存接口
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     */
    public function Comment_post(){
        $post = I('post.');
        $serv_drt = D('Dailyreport', 'Service');
        if ($rel = $serv_drt->add_comment($post,$this->_login->user['m_uid'])) {
            $this->_result=$rel;
            return true;
        };
        return false;
    }
    /**
     * 获取工作汇报轨迹
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     */
    public function Track_get(){
        $dr_id = I('get.dr_id');
        $serv_drt = D('Dailyreport', 'Service');
        if ($tracks = $serv_drt->get_track($dr_id)) {
            $this->_result = $tracks;
            return true;
        };
        return false;
    }
    /**
     * 我负责的
     * @author xiebinbin<xiebinbin@vchangyi.com>
     * @return boolean
     */
    public function GetMyResponsiblesApi_get() {
        $page = I('get.page');
        $q=I('get.q','');
        $k=I('get.k','');
        $drt_id=intval(I('get.drt_id',0));
        $serv_drt = D('Dailyreport', 'Service');
        $m_uid=$this->_login->user['m_uid'];
        if ($list = $serv_drt->get_my_responsibles($m_uid,$page,$q,$k,$drt_id)){
            $this->_result=$list;
            return true;
        };
        return false;
    }
     /**
      * 与我相关的
      * @author xiebinbin<xiebinbin@vchangyi.com>
      * @return boolean
      */
    public function GetForMeApi_get() {
        $page = I('get.page');
        $q=I('get.q','');
        $k=I('get.k','');
        $drt_id=intval(I('get.drt_id',0));
        $serv_drt = D('Dailyreport', 'Service');
         $m_uid=(int)$this->_login->user['m_uid'];
        if ($list = $serv_drt->get_for_me($m_uid,$page,$q,$k,$drt_id)){
            $this->_result=$list;
            return true;
        };
        return false;
    }
    /**
     * 查看往期
     * @author xiebinbin<xiebinbin@vchangyi.com>
     * @return boolean
     */
    public function GetPastApi_get() {
        $page = I('get.page');
        $q=I('get.q','');
        $k=I('get.k','');
        $drt_id=(int)I('get.drt_id',0);
        $target_id=(int)I('get.target_id');
        $serv_drt = D('Dailyreport', 'Service');
        $m_uid=$this->_login->user['m_uid'];
        if ($list = $serv_drt->get_past_api($m_uid,$page,$q,$k,$drt_id,$target_id)){
            $this->_result=$list;
            return true;
        };
        return false;
    }
}
