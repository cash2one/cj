<?php
/**
 * Created by PhpStorm.
 * User: zhang mi
 * Date: 2016/4/13
 * Time: 17:39
 */

namespace Note\Model;

class NoteCategoryModel extends AbstractModel{


    // 构造方法
    public function __construct() {
        parent::__construct();
    }

    public function get_cate_list(){
        $sql = "SELECT id,title FROM oa_jobtrain_category";
        $model = new \Think\Model();
        $cate = $model->query($sql);
        $result = $this->unlimitedForLayer($cate);
        return $result;
    }
    //递归调用，生成多级菜单数组
    public function unlimitedForLayer($cate,$pid=0){
        $arr = array();
        foreach($cate as $v){
            if($v['pid'] == $pid){
                $v['child'] =$this->unlimitedForLayer($cate,$v['id']);
                $arr[] = $v;
            }
        }
        return $arr;
    }


}