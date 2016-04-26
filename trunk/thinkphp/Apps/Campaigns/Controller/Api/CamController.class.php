<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/12
 * Time: 19:06
 */

namespace Campaigns\Controller\Api;
class CamController extends AbstractController {
    private $cam_sr;
    public function __construct() {
        if(ACTION_NAME=='getDetailApi'){
            $this->_require_login=false;
        }
        parent::__construct();
        $this->cam_sr=D('Cam','Service');
    }
    /**
     * 活动中心列表
     */
    public function getList_get(){
        $get=I('get.');
        $m_uid = $this->_login->user['m_uid']?:337;
        $this->_result = $this->cam_sr->get_list_api($get,$m_uid);
    }
    /**
     * 活动详情
     */
    public function getDetailApi_get(){
        $id=(int)I('get.id');
        $uid=(int)I('get.uid');
        $this->_result = $this->cam_sr->get_detail_api($id,$uid);
    }
    /**
     * 推广活动
     */
    public function shareCam_post(){
        $id=(int)I('post.id');
        $m_uid = $this->_login->user['m_uid']?:337;
        $this->_result = $this->cam_sr->share_cam_api($id,$m_uid);
    }
    /**
     * 数据追踪
     */
    public function myShares_get(){
        $m_uid = $this->_login->user['m_uid']?:337;
        $page = (int)I('get.page',1);
        $typeid=(int)I('get.typeid',0);
        $this->_result = $this->cam_sr->my_share_api($m_uid,$page,$typeid);
    }
}