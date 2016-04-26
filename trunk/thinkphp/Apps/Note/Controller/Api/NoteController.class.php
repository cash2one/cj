<?php
/**
 * Created by PhpStorm.
 * User: zhang mi
 * Date: 2016/4/18
 * Time: 12:09
 */

namespace  Note\Controller\Api;
use Common\Common\Pager;
use Common\Common\Cache;
use Org\Util\String;
use Think\Log;

class NoteController extends AbstractController{

    //构造方法
    public function __construct(){
        parent::__construct();
    }

    public function course_get(){
        $s_note = D('Note/Note','Service');
        $cid = I('get.cid',0,'intval'); //所属课程分类id
        $m_uid = $this->_login->user['m_uid']?:4;
        $result = $s_note->course($cid,$m_uid);
        $this->_result = array(
            'list'=>$result,
        );

        return true;

    }
    /**
     * 添加笔记
     */
    public function add_note_post(){
        $s_note = D('Note/Note','Service');
        $cid = I('post.cid',0,'intval'); //所属课程id
        $title = I('post.title',''); //课程笔记标题
        $content = I('post.content',''); //笔记内容
        $attachs = I('post.attachs',''); //附件
        $m_uid = $this->_login->user['m_uid']?:377;
        $result = $s_note->add_note($cid, $title, $content, $attachs, $m_uid);
        $this->_result = array(
            'note_id'=>$result
        );
        return true;
    }

    /**
     * 我的笔记
     */
    public function my_list_get(){
        $s_note = D('Note/Note','Service');
        $page = I('get.page',1,'intval'); //第几页
        //设置分页
        $pagesize = 15; //每页显示数量
        list($start, $limit, $page) = page_limit($page,$pagesize);
        $m_uid = $this->_login->user['m_uid']?:4;
        $result = $s_note->my_list($m_uid, $start, $limit);
        $this->_result = array(
            'count' => $result['count'], //总数
            'pages'  => ceil($result['count']/$pagesize), //总页数
            'cur_page'=> $page, //当前页面
            'limit' => $limit, //每页显示数量
            'list'=>$result['list']
        );
        return true;
    }

    /**
     * 查看笔记首页列表
     */
    public function check_note_get(){
        $s_note = D('Note/Note','Service');
        $page = I('get.page',1,'intval'); //第几页
        $title = I('get.title',""); //搜索标题
        $cid = I('get.cid','','intval'); //分类id
        //设置分页
        $pagesize = 5; //每页显示数量
        list($start, $limit, $page) = page_limit($page,$pagesize);
        $m_uid = $this->_login->user['m_uid']?:377;
        if($title !== ""){
            $result = $s_note->search_by_title($title, $start, $limit, $m_uid);
        }elseif($cid){
            $result = $s_note->search_by_cate($cid, $start, $limit, $m_uid);
        }else{
            $result = $s_note->check_note($m_uid, $start, $limit);
        }
        $this->_result = array(
            'count' => $result['count'], //总数
            'pages'  => ceil($result['count']/$pagesize), //总页数
            'cur_page'=> $page, //当前页面
            'limit' => $limit, //每页显示数量
            'list'=>$result['list']
        );
        return true;
    }

    /**
     * 查看笔记详情
     */
    public function note_detail_get(){
        $s_note = D('Note/Note','Service');
        $note_id = I('get.note_id',0,'intval'); //课程笔记id
        //获取文章
        $m_uid = $this->_login->user['m_uid']?:4;
        $result = $s_note->get_note_detail($note_id, $m_uid);
        //格式化结果
        $detail = array(
            'note_id' => $result['note_id'],
            'course'=>$result['course'],
            'title' =>$result['title'],
            'm_username'=>$result['m_username'],
            'c_time' =>$result['c_time'],
            'content'=>htmlspecialchars_decode($result['content']),
            'm_face'=>$result['m_face'],
            'img'=>$result['img'],
            'attachs'=>$result['attachs'],
        );

        $this->_result = array(
            'detail' =>$detail
        );
        return true;
    }

    /**
     * 通过笔记标题搜索笔记
     * @return bool
     */
    public function search_by_title_get(){
        $title = I('get.title',"");
        $title = I('get.title',"");
        $page = I('get.page',1,'intval'); //第几页
        //设置分页
        $pagesize = 5; //每页显示数量
        list($start, $limit, $page) = page_limit($page,$pagesize);
        $m_uid = $this->_login->user['m_uid']?:4;
        if($title !== "") $result = D('Note/Note','Service')->search_by_title($title, $start, $limit, $m_uid);
        $this->_result = array(
            'count' => $result['count'], //总数
            'pages'  => ceil($result['count']/$pagesize), //总页数
            'cur_page'=> $page, //当前页面
            'limit' => $limit, //每页显示数量
            'list'=>$result['list'],
        );
        return true;
    }

    /**
     * 通过分类id搜索获取笔记
     */
    public function search_get(){
        $s_note = D('Note/Note','Service');
        $title = I('get.title',"");
        $cid = I('get.cid','','intval');
        $page = I('get.page',1,'intval'); //第几页
        //设置分页
        $pagesize = 5; //每页显示数量
        list($start, $limit, $page) = page_limit($page,$pagesize);
        $m_uid = $this->_login->user['m_uid']?:4;
        if($cid){$result = $s_note->search_by_cate($cid, $start, $limit, $m_uid);};
        if($title !== ""){$result = D('Note/Note','Service')->search_by_title($title, $start, $limit, $m_uid);};
        $this->_result = array(
            'count' => $result['count'], //总数
            'pages'  => ceil($result['count']/$pagesize), //总页数
            'cur_page'=> $page, //当前页面
            'limit' => $limit, //每页显示数量
            'list'=>$result['list'],
        );
        return true;
    }

    /**
     * 最近学习过的10个课程
     */
    public function learned_course_get(){
        $m_uid = $this->_login->user['m_uid']?:377;
        $result = D('Note/Note','Service')->learned_course($m_uid);
        $this->_result = array(
          'list' => $result
        );
        return true;
    }

    /**
     * 笔记浏览量+1
     * @return bool
     */
    public function inc_scan_num_get(){
        $s_note = D('Note/Note','Service');
        $note_id = I('get.note_id');
        $result = $s_note->inc_scan_num($note_id);
        return true;
    }

    public function search_cate_list(){
        $service = D('Note/NoteCategory','Service');
        $s_model = D('Note/Note','Service');
        $cates = $service->search_cate_list();
        $this->_result = array(
            'cates' =>$cates
        );
        return true;
    }

    /**
     * 推送附件
     */
    public function attach_push_get() {
        $id = I('get.id');
        $serv = &\Common\Common\Wxqy\Service::instance();
        $serv_media = new \Common\Common\Wxqy\Media($serv);
        $serv_a = D('Common/CommonAttachment', 'Service');
        // 获取附件
        $attach = $serv_a->get($id);
        if($attach){
            // 获取附件物理路径
            $z_path = get_sitedir();
            $attach_path = str_replace('/thinkphp/Apps/Runtime/Temp', '/apps/voa/data/attachments', $z_path) . $attach['at_attachment'];
            $file = array();
            // 上传附件
            $serv_media->upload_file($file, array('path' => $attach_path, 'name' => $attach['at_filename']));
            $result = false;
            if($file['media_id']){
                $wxqyMsg = &\Common\Common\WxqyMsg::instance();
//                $model_plugin = D('Common/CommonPlugin');
//                $plugin = $model_plugin->get_by_identifier('jobtrain');
                // 推送附件
                $wxqyMsg->send_file($file['media_id'], array($this->_login->user['m_uid']), '',20,48);
                $result = true;
            }
        }

        $this->_result = $result;
    }


}