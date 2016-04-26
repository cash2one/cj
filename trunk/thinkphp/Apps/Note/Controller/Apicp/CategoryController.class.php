<?php
/**
 * Created by PhpStorm.
 * User: zhang mi
 * Date: 2016/4/12
 * Time: 18:15
 */

namespace Note\Controller\Apicp;
class CategoryController extends AbstractController{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 获取课程分类列表(添加课程笔记)
     */
    public function get_cate_list(){
        $service = D('Note/NoteCategory','Service');
        $cates = $service->get_cate_list();
        $this->_result = array(
            'cates' =>$cates
        );
        return true;
    }

    /**
     * 获取课程分类列表(搜索课程列表)
     */
    public function get_cate_search(){
        $service = D('Note/NoteCategory','Service');
        $cates = $service->get_cate_search();
        $this->_result = array(
            'cates' =>$cates
        );
        return true;
    }


}