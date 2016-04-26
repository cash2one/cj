<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/12
 * Time: 19:06
 */

namespace Campaigns\Controller\Apicp;

class CamController extends AbstractController {
    private $cam_sr;
    public function __construct() {
        parent::__construct();
        $this->cam_sr=D('Cam','Service');
    }
    /**
     * 新增活动
     */
    public function add_post(){
        $post=I('post.',array());
        $m_uid=$this->_login->user['ca_id'];
        $m_username=$this->_login->user['ca_username'];
        $this->cam_sr->add_cam($post,$m_uid,$m_username);
        return true;
    }
    /**
     * 编辑活动
     */
    public function save_post(){
        $post=I('post.',array());
        $this->cam_sr->save_cam($post);
        return true;
    }
    /**
     * 活动列表
     */
    public function list_get(){
        $get=I('get.');
        $this->_result = $this->cam_sr->list_cam($get);
        return true;
    }
    /**
     * 编辑时回显
     */
    public function editDetail_get(){
        $id=(int)I('get.id');
        $this->_result = $this->cam_sr->get_edit_detail($id);
        return true;
    }
    /**
     * 活动详情
     */
    public function detail_get(){
        $this->_result = $this->cam_sr->detail_cam(I('get.id'));
        return false;
    }
    /**
     * 批量删除
     */
    public function dels_post(){
        $ids = I('post.ids');
        $this->cam_sr->dels_cam($ids);
        return false;
    }
    public function dataCenter_get(){
        $page=(int)I('get.page',1);
        $this->_result = $this->cam_sr->data_center($page);
        return true;
    }
}