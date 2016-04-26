<?php
namespace Note\Service;

class CategoryService extends AbstractService {

    // 构造方法
    public function __construct() {
        parent::__construct();
    }
    /**
     * 根据权限获取分类树
     * @param int $m_uid
     * @return array
     */
    public function get_tree_with_right($m_uid) {
        $s_mbdp = D('Common/MemberDepartment', 'Service');
        $dps = $s_mbdp->list_by_conds(array('m_uid'=>$m_uid));
        $cd_ids = array();
        foreach ($dps as $k => $v) {
            $cd_ids[] = $v['cd_id'];
        }
        $cd_ids[] = 2;
        $catas = D("Jobtrain/JobtrainCategory")->get_tree_with_right($m_uid, $cd_ids);
        // 重新计算一级分类文章数
        foreach ($catas as $k => $v) {
            foreach ($catas[$k]['childs'] as $_v) {
                $catas[$k]['article_num'] += $_v['article_num'];
            }
        }
        // 取消键值
        $catas = array_values($catas);
        foreach ($catas as $k => $v) {
            $catas[$k]['childs'] = array_values($catas[$k]['childs']);
        }
        return $this->get_cate_all($catas, $m_uid);
    }

    /**
     * 前端选择课程
     */
    public function get_cate_all($cates ,$m_uid){
        foreach ($cates as $k=>$vo){
            $res[$k]['id']=$vo['id'];
            $res[$k]['title']=$vo['title'];
            if($vo['pid'] != 0) $res[$k]['sub'] = array();
            if(!$vo['childs']){
                $aid = $this->get_aids($m_uid, $vo['id']);
                if(!$aid){$sum = 0;}else{
                    $where['id'] = array('in',$aid);
                    $sum = M('jobtrain_article')->where($where)->count();
                }
                $res[$k]['count'] = $sum;
            }
            $firstCount = 0;
            foreach ($vo['childs'] as $k1=>$vo1){
                $res[$k]['sub'][$k1]['title']=$vo1['title'];
                $res[$k]['sub'][$k1]['id']=$vo1['id'];
                $aid = $this->get_aids($m_uid, $vo['id']);
                if(!$aid){
                    $count = 0;
                }else{
                    $where['id'] = array('in',$aid);
                    $count = M('jobtrain_right')->where($where)->count();
                }
                $res[$k]['count'] = $count;
                $firstCount +=$count;
            }
            if($vo['childs'])
                $res[$k]['count'] = $firstCount;
        }
        $arr = array();
        foreach($res as $k => $v){
            if($v['count'] != 0 ){
                $arr[$k] = $v;
            }
        }
        return $arr;
    }

    public function get_aids($m_uid, $cid){
        $where['m_uid'] = $m_uid;
        $where['cid'] = (int)$cid;
        $aid = M('jobtrain_right')->field('aid')->where($where)->select();
        foreach($aid as $k2=>$v2){
            $aids[] = $v2['aid'];
        }
        //获取有权限的课程id
        $aids = array_unique($aids);
        return $aids;
    }


    //通过传父类的ID,获取该分类下的所有子分类ID
    public function getChildId($cate,$pid){
        $arr = array();
        foreach($cate as $v){
            if($v['pid'] == $pid) {
                $arr[] = $v['id'];
                $arr = array_merge($arr,$this->getChildId($cate,$v['id']));
            }
        }
        return $arr;
    }

    //搜索页面分类并统计笔记数量
    public function search_cate($m_uid){
        $cate = $this->get_tree_with_right($m_uid);
        foreach($cate as $k => $v){
            $res[$k]['id'] = $v['id'];
            $res[$k]['title'] = $v['title'];
            if(!$v['sub']){
                $aids = $this->get_aids($m_uid, $v['id']);
                $where['cid'] = array('in',$aids);
                $count = M('Note')->where($where)->count();
                $res[$k]['count'] = $count;
            }
            $firstCount=0;
            foreach($v['sub'] as $k1 => $v2){
                $res[$k]['sub'][$k1]['id'] = $v2['id'];
                $res[$k]['sub'][$k1]['title'] = $v2['title'];
                $aids = $this->get_aids($m_uid, $v2['id']);
                $map['cid'] = array('in',$aids);
                $count = M('Note')->field('id')->where($map)->count();
                $res[$k]['sub'][$k1]['count'] = $count;
                $firstCount +=$count;
            }
            if($v['sub'])
            $res[$k]['count'] = $firstCount;
        }
        return $res;
    }




}