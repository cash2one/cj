<?php
/**
 * Created by PhpStorm.
 * User: zhang mi
 * Date: 2016/4/13
 * Time: 17:38
 */

namespace Note\Service;

class NoteCategoryService extends AbstractService{

    public function __construct(){
        parent::__construct();
    }

    /**
     * 获取课程分类列表三级菜单（添加课程笔记）
     * @return array
     */
    public function get_cate_list(){
        $sql = "SELECT id,title FROM oa_jobtrain_category WHERE pid = 0";
        $model = new \Think\Model();
        $cate = $model->query($sql);
        foreach($cate as $k => $v){
            $re = M('jobtrain_category')->field('id,title')->where("pid = '".$v['id']."'")->select();
            $cate[$k]['sub'] = $re;
            foreach($re as $k1 => $v1){
                $res = M('jobtrain_article')->field('id cid,title')->where("cid = '".$v1['id']."'")->select();
                $cate[$k]['sub'][$k1]['sub'] = $res;
            };
            if(!$re){
                $res = M('jobtrain_article')->field('id cid,title')->where("cid = '".$v['id']."'")->select();
                $cate[$k]['sub'] = $res;
            }
        }
        return $cate;
    }
    /**
     * 获取课程分类列表二级菜单（搜索课程笔记）
     * @return array
     */
    public function get_cate_search(){
        $sql = "SELECT id,title FROM oa_jobtrain_category WHERE pid = 0";
        $model = new \Think\Model();
        $cate = $model->query($sql);
        foreach($cate as $k => $v){
            $re = M('jobtrain_category')->field('id,title')->where("pid = '".$v['id']."'")->select();
            if($re) $cate[$k]['sub'] = $re;
        }
        return $cate;
    }
    //-----------------H5----------------
    /**
     * 前端选择课程
     */
    public function get_cate_all(){
        $sql = "SELECT pid,id,title FROM oa_jobtrain_category";
        $model = new \Think\Model();
        $cate = $model->query($sql);
        $result = $this->unlimitedForLayer($cate);
        foreach ($result as $k=>$vo){
            $res[$k]['title']=$vo['title'];
            $res[$k]['id']=$vo['id'];
            $childId= $this->getChildId($cate,$vo['id']);
            $childId[]=$vo['id'];
            $res[$k]['count']=M('jobtrain_article')->where(array('cid'=>array('IN',$childId)))->count();
            foreach ($vo['sub'] as $k1=>$vo1){
                $res[$k]['sub'][$k1]['title']=$vo1['title'];
                $res[$k]['sub'][$k1]['id']=$vo1['id'];
            }
        }
        return $res;
    }

    public function cate_with_study($m_uid){
        $study=M('jobtrain_study')->field('id,m_uid,aid')->where("m_uid = '$m_uid'")->select();
        foreach ($study as $val){
            $aid[] = $val['aid'];
        }
        $aid = array_unique($aid);
        $cate = M('jobtrain_category')->field('title,pid,id')->select();
        $result = $this->unlimitedForLayer($cate);
        foreach ($result as $k=>$vo){
            $res[$k]['title'] = $vo['title'];
            $res[$k]['id'] = $vo['id'];
            $childId = $this->getChildId($cate,$vo['id']);
            $childId[] = $vo['id'];
            $article = M('jobtrain_article')->field('id')->where(array('cid'=>array('IN',$childId)))->select();
            $count = 0;
            foreach ($article as $val1){
                if(in_array($val1['id'], $aid)){
                    $count+=1;
                }
            }
            $res[$k]['count']=$count;
            foreach ($vo['sub'] as $k1=>$vo1){
                $res[$k]['sub'][$k1]['title']=$vo1['title'];
                $res[$k]['sub'][$k1]['id']=$vo1['id'];
            }
        }
        return $res;
    }

    public function cate_only_study($m_uid){
        $study=M('jobtrain_study')->field('id,m_uid,aid')->where("m_uid = '$m_uid'")->select();
        if(!$study){
            E('_NOTE_ERROR_PARAM_NULL_');
        }
        foreach ($study as $val){
            $aid[] = $val['aid'];
        }
        $aid = array_unique($aid);
        $cate = M('jobtrain_category')->alias('c')->field('c.title,c.pid,c.id')->select();
        $result = $this->unlimitedForLayer($cate);
        foreach ($result as $k=>$vo){
            $childId = $this->getChildId($cate,$vo['id']);
            $childId[] = $vo['id'];
            $article = M('jobtrain_article')->field('id')->where(array('cid'=>array('IN',$childId)))->select();
            $count = 0;
            foreach ($article as $val1){
                if(in_array($val1['id'], $aid)){
                    $count+=1;
                }
            }
            if($count){
            $res[$k]['id'] = $vo['id'];
            $res[$k]['title'] = $vo['title'];
            $res[$k]['count']=$count;
            }
            foreach ($vo['sub'] as $k1=>$vo1) {
                $aids=M('jobtrain_article')->field('id')->where(array('cid'=>$vo1['id']))->select();
                foreach($aids as $v2){
                    $ids[] = $v2['id'];
                }
                if($ids){
                    $count1 = M('jobtrain_study')->field('id')->where(array('aid'=>array('IN',$ids)))->count();
                }else{
                    $count1=0;
                }
                unset($ids);
                if($count1){
                    $res[$k]['sub'][$k1]['id'] = $vo1['id'];
                    $res[$k]['sub'][$k1]['title'] = $vo1['title'];
                    $res[$k]['sub'][$k1]['count'] = $count1;
                }


            }
        }
//        var_dump($aids);die;
        return $res;
    }


    /**
     * 获取分类列表（按分类搜索）
     */
    public function search_cate_list(){
        $cate=M('jobtrain_category')->select();
        $result=$this->unlimitedForLayer($cate);
        foreach ($result as $k=>$vo){
            $res[$k]['title']=$vo['title'];
            $res[$k]['id']=$vo['id'];
            $childId= $this->getChildId($cate,$vo['id']);
            $childId[]=$vo['id'];
            $aid=M('jobtrain_article')->field('id')->where(array('cid'=>array('IN',$childId)))->select();
            foreach($aid as $v){
                $aids[$k][] = $v['id'];
            }
            $res[$k]['count'] = M('note')->where(array('cid'=>array('IN',$aids[$k])))->count();
            foreach ($vo['sub'] as $k1=>$vo1){
                $res[$k]['sub'][$k1]['title']=$vo1['title'];
                $res[$k]['sub'][$k1]['id']=$vo1['id'];
                $aid=M('jobtrain_article')->field('id')->where(array('cid'=>$vo1['id']))->select();
                foreach($aid as $v2){
                    $ids[] = $v2['id'];
                }
                if($ids){
                    $count = M('note')->field('id')->where(array('cid'=>array('IN',$ids)))->count();
                }else{
                    $count=0;
                }
                unset($ids);
                $res[$k]['sub'][$k1]['count'] = $count;
            }
        }

        return $res;
    }

    /**
     * 获取所属课程分类
     * @param $cid
     * @return mixed
     */
    public function get_parents_cate($cid){
        $sql = "SELECT pid,id,title FROM oa_jobtrain_category";
        $model = new \Think\Model();
        $cate = $model->query($sql);
        $result = $this->getParentsCate($cate,$cid);
        $res = array();
        foreach($result as $k => $v){
                $res['c_title'] .= $v['title'].' - ';
        }
        return $res;

    }

    /**
     * 获取子类id
     * @param $cid
     */
    public function get_child_cate($cid){
        $sql = "SELECT pid,id,title FROM oa_jobtrain_category";
        $model = new \Think\Model();
        $cate = $model->query($sql);
        $cids = $this->getChildId($cate,$cid);
        return $cids;
    }

    //查询子分类数量
    public function sub_count($cate, $id){
        $childId= $this->getChildId($cate,$id);
        //查询是否有子分类
        $child=M('jobtrain_category')->field('id')->where(array('pid'=>$id))->select();
        foreach ($child as $v){
            if(M('jobtrain_category')->where(array('pid'=>$v['id']))->select()){
                $key = array_search($v['id'], $childId);
                if ($key !== false){
                    array_splice($childId, $key, 1);
                }

            }
        }
        return $childId;
    }
    //递归调用，生成多级菜单数组
    public function unlimitedForLayer($cate,$pid=0){
        $arr = array();
        foreach($cate as $v){
            if($v['pid'] == $pid){
                $v['sub'] =$this->unlimitedForLayer($cate,$v['id']);
                $arr[] = $v;
            }
        }
        return $arr;
    }
    //通过传子分类ID,返回所有父分类
    public function getParentsCate($cate,$cid){
        $arr = array();
        foreach($cate as $v){
            if($v['id'] == $cid){
                $arr[] = $v;
                $arr = array_merge($this->getParentsCate($cate,$v['pid']),$arr);
            }
        }
        return $arr;
    }

    //通过传父类的ID,获取该分类下的所有子分类ID
    public function getChildId($cate,$pid){
        $arr = array();
        foreach($cate as $v){
            if($v['pid'] == $pid){
                $arr[] = $v['id'];
                $arr = array_merge($arr,$this->getChildId($cate,$v['id']));
            }
        }
        return $arr;
    }


}