<?php
/**
 * Created by PhpStorm.
 * User: zhang mi
 * Date: 2016/4/18
 * Time: 10:19
 */
namespace Note\Controller\Api;

class CategoryController extends AbstractController{

    //构造方法
    public function __construct(){
        parent::__construct();
    }

    /**
     * 获取分类列表（选择课程）
     * @return bool
     */
    public function get_cate_list_get(){
        $serv_cata = D('Note/Category', 'Service');
        $m_uid = $this->_login->user['m_uid']?:377;
        $catas = $serv_cata->get_tree_with_right($m_uid);
        $this->_result = array(
            'catas' => $catas
        );
        return true;
    }

    //搜索笔记
    public function search_cate_list(){
        $service = D('Note/NoteCategory','Service');
        $cates = $service->search_cate_list();
        $this->_result = array(
            'cates' =>$cates
        );
        return true;
    }

    /**
     * 获取分类列表（选择课程）只显示最近学习的十个课程
     * 显示分类不显示笔记
     * @return bool
     */
    public function cate_with_study_get(){
        //查找顶级分类
        $m_uid = $this->_login->user['m_uid']?:377;
        $cates = D('Note/NoteCategory','Service')->cate_with_study($m_uid);
        $this->_result = array(
            'cates' =>$cates
        );
        return true;
    }

    public function cate_only_study_get(){
        //查找顶级分类
        $m_uid = $this->_login->user['m_uid']?:377;
        $cates = D('Note/NoteCategory','Service')->cate_only_study($m_uid);
        $this->_result = array(
            'cates' =>$cates
        );
        return true;
    }


    public function search_cate_get() {
        $serv_cata = D('Note/Category', 'Service');
        $m_uid = $this->_login->user['m_uid']?:377;
        $catas = $serv_cata->search_cate($m_uid);
        $this->_result = array(
            'catas' => $catas
        );
        return true;
    }


}

