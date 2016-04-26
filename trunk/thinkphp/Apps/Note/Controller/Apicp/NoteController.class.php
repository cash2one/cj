<?php
/**
 * Created by PhpStorm.
 * User: zhang mi
 * Date: 2016/4/12
 * Time: 11:28
 */

 namespace Note\Controller\Apicp;
 use Common\Common\Pager;
 use Common\Common\Cache;
 use Org\Util\String;
 use Think\Log;

 class NoteController extends AbstractController{

     /**
      * 获取课堂笔记列表
      */
    public function get_list_get(){
        $s_note = D('Note/Note','Service');
        //获取搜索参数
        $cid = I('get.cid',0,'intval'); //课程分类id
        $title = I('get.title',''); //笔记名称
        $m_username = I('get.m_username',''); //创建人
        $course = I('get.course',''); //所属课程
        $start_time = I('get.start_time',''); //开始日期
        $end_time = I('get.end_time',''); //结束日期
        $page = I('get.page',1,'intval'); //第几页
        //设置分页
        $pagesize = 15; //每页显示数量
        list($start, $limit, $page) = page_limit($page,$pagesize);
        //获取笔记列表
        $conds = array(
            'title'     => $title,
            'm_username'=> $m_username,
            'course'    => $course,
            'start_time'=> $start_time,
            'end_time'  => $end_time
        );
        $result = $s_note->get_list($cid, $conds, $start, $limit);
        $this->_result = array(
            'count' => $result['count'],
            'page'  => ceil($result['count']/$pagesize),
            'cur_page'=> $page,
            'limit' => $limit,
            'list' => $result['list'],
//            'test'=>$result
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
         $m_uid = 0;
         $result = $s_note->add_note($cid, $title, $content, $attachs, $m_uid);
         $this->_result = array(
           'status'=>$result
         );
         return true;
     }

     /**
      * 查看笔记详情
      */
     public function get_note_detail_get(){
         $s_note = D('Note/Note','Service');
         $note_id = I('get.note_id',0,'intval'); //课程笔记id
         //获取文章
         $result = $s_note->get_note_detail($note_id);
         //格式化结果
         $detail = array(
             'note_id' => $result['note_id'],
             'course'=>$result['course'],
             'title' =>$result['title'],
             'c_title'=>$result['c_title'],
             'c_time' =>$result['c_time'],
             'content'=>htmlspecialchars_decode($result['content']),
             'attachs'=>$result['attachs'],
         );

         $this->_result = array(
             'detail' =>$detail
         );
         return true;
     }

     /**
      * 编辑笔记
      */
     public function edit_note_post(){
         $s_note = D('Note/Note','Service');
         $note_id = I('post.note_id',0,'intval'); //课程笔记id
         $title = I('post.title',''); //笔记标题
         $content = I('post.content',''); //笔记内容
         $attachs = I('post.attachs',''); //附件
         $result = $s_note->edit_note($note_id, $title, $content, $attachs);
         $this->_result = array(
             'status'=>$result,
         );
         return true;

     }

     /**
      * 删除笔记
      */
     public function delete_note_post(){
         $s_note = D('Note/Note','Service');
         $note_ids = I('post.note_id', array()); //课程笔记id
         $result = $s_note->delete_note($note_ids);
         $this->_result = array(
           'status' => $result,
         );
         return true;
     }

     /**
      * 删除附件
      * @param $at_id
      */
     public function delete_attach_get(){
         $s_note = D('Note/Note','Service');
         $at_id = I('get.at_id',"",'intval');
         $note_id = I('get.note_id','','intval');
         if($note_id){
             $result = $s_note->delete_attach_edit($at_id,$note_id);
         }else{
             $result = $s_note->delete_attach_add($at_id);
         }

         $this->_result = array(
             'status' => $result
         );
         return true;
     }

 }