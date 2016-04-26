<?php
/**
 * Created by PhpStorm.
 * User: zhang mi
 * Date: 2016/4/12
 * Time: 15:22
 */

namespace Note\Service;
use Org\Util\String;

class NoteService extends AbstractService{
    // 构造方法
    public function __construct(){
        parent::__construct();
        // 实例化相关模型
    }

    /**
     * @param $conds
     * @param $start
     * @param $limit
     * @return mixed
     */
    public function get_list($cid, $conds, $start, $limit){
        $s_note = D('Note/NoteCategory','Service');
        $cids = $s_note->get_child_cate($cid);
        if(!count($cids)) $cids = $cid;
        if($cid == 0){$cids = $cid;}
        $result = D('Note/Note')->get_list($cids, $conds, $start, $limit);
        return $result;
    }

    public function get_note_detail($note_id, $m_uid){
        $result = D('Note/Note')->get_note_detail($note_id);
        $face = M('Member')->field('m_face')->where("m_uid = '$m_uid'")->find();
        $result['m_face'] = $face['m_face'];
        $cid = M('note')->field('cid')->where("note_id = '$note_id'")->find();
        $s_model = D('Note/NoteCategory','Service');
        $aid = M('jobtrain_article')->field('cid')->where("id = '".$cid['cid']."'")->find();
        $res = $s_model->get_parents_cate($aid['cid']);
        $c_title = $res['c_title'];
        $result['c_title'] = rtrim($c_title," - ");
        if($result['attachments']){
            $attachs = $this->get_attach_path($result['attachments']);
            $result['attachs'] = $attachs;
        }else{
            $result['attachs'] = array();
        }
        return  $result;
    }
    /**
     * 获取附件信息
     * @param $attach_ids
     * @return mixed
     */
    public function get_attach_path($attach_ids){
        $cache = &\Common\Common\Cache::instance();
        $setting = $cache->get('Common.setting');
        $domain = C('PROTOCAL') . $setting['domain'];
        $model = new \Think\Model();
        $sql = "SELECT at_id,at_filename,at_filesize,at_attachment FROM oa_common_attachment WHERE at_id in($attach_ids)";
        $attachs = $model->query($sql);
        foreach($attachs as $k=>$v){
            $attachs[$k]['ext'] = pathinfo($v['at_attachment'])['extension'];
            $attachs[$k]['addr'] = $domain."/attachment/read/".$v['at_id'];
        }
        return $attachs;
    }

    /**
     * @param $cid
     * @param $title
     * @param $content
     * @param $attachs
     * @param $audimgs
     */
    public function add_note($cid, $title, $content, $attachs, $m_uid){
        $result = D('Note/Note')->add_note($cid, $title, $content, $attachs, $m_uid);
        return $result;
    }

    /**
     * 修改课程笔记
     * @param $note_id
     * @param $title
     * @param $content
     * @param $attachs
     * @param $audimgs
     * @return mixed
     */
    public function edit_note($note_id, $title, $content, $attachs){
        $result = D('Note/Note')->edit_note($note_id, $title, $content, $attachs);
        return $result;
    }

    /**
     * 删除笔记
     * @param $note_id
     * @return mixed
     */
    public function delete_note($note_ids){
        $result = D('Note/Note')->delete_note($note_ids);
        return $result;
    }

    /**
     * 删除附件(编辑页面)
     * @param $at_id
     */
    public function delete_attach_edit($at_id, $note_id){
        $result = D('Note/Note')->delete_attach_edit($at_id, $note_id);
        return $result;
    }

    /**
     * 删除附件（添加页面）
     * @param $at_id
     */
    public function delete_attach_add($at_id){
        $result = D('Note/Note')->delete_attach_add($at_id);
        return $result;
    }

    /**
     * 浏览数量+1 成功则返回true 否则返回false
     *
     * @param int $id
     * @return bool
     */
    public function inc_scan_num($note_id) {
        return D('Note/Note')->inc_scan_num($note_id);
    }

    /**
     * 根据分类id查出课程
     * @param $cid
     */
    public function course($cid, $m_uid){
        $result = D('Note/Note')->course($cid, $m_uid);
        $cache = &\Common\Common\Cache::instance();
        $setting = $cache->get('Common.setting');
        $domain = C('PROTOCAL') . $setting['domain'];
        foreach($result as $k => $v){
            if($v['cover_id']){
                $picurl = $domain . '/attachment/read/' . $v['cover_id'];
                $result[$k]['img'] = $picurl;
            };
        }
        return $result;
    }

    /**
     * 根据分类id查出课程
     * @param $cid
     */
    public function learned_course($m_uid){
        $result = D('Note/Note')->learned_course($m_uid);
        $cache = &\Common\Common\Cache::instance();
        $setting = $cache->get('Common.setting');
        $domain = C('PROTOCAL') . $setting['domain'];
        foreach($result as $k => $v){
            if($v['cover_id']){
                $picurl = $domain . '/attachment/read/' . $v['cover_id'];
                $result[$k]['img'] = $picurl;
            };
        }
        return $result;
    }

    /**
     * 我的笔记
     * @param $m_uid
     */
    public function my_list($m_uid, $start, $limit){
        $result = D('Note/Note')->my_list($m_uid, $start, $limit);
        return $result;
    }

    /**
     * 查看笔记
     * @param $m_uid
     */
    public function check_note($m_uid,$start, $limit){
        $cate = D('Note/Category','Service')->get_tree_with_right($m_uid);
        foreach($cate as $k=>$v){
            $cids[] = $v['id'];
            foreach($v['sub'] as $k1=>$v1){
                $cids[] = $v1['id'];
            }
        }
        $where['m_uid'] = $m_uid;
        $where['cid'] = array('in',$cids);
        $aid = M('jobtrain_right')->field('aid')->where($where)->select();
        foreach($aid as $k2=>$v2){
            $aids[] = $v2['aid'];
        }
        $result = '';
        $aids = array_unique($aids);
        if(!$aids){return $result;exit;}
        $map['n.cid'] = array('in',$aids);
        $result['list'] = M('Note')->alias('n')
                        ->field('n.m_username,n.note_id,n.title,n.c_time,a.title course')
                        ->join('LEFT JOIN oa_jobtrain_article a ON a.id = n.cid')
                        ->where($map)
                        ->order('n.c_time desc')
                        ->limit($start, $limit)
                        ->select();
        $result['count'] = M('Note')->alias('n')->where($map)->count();
        return $result;
    }

    /**
     * 按分类搜索
     * @param $cid
     * @param $start
     * @param $limit
     * @param $m_uid
     * @return mixed
     */
    public function search_by_cate($cid, $start, $limit, $m_uid){
        $result = D('Note/Note')->search_by_cate($cid, $start, $limit, $m_uid);
        return $result;
    }

    /**
     * 按标题搜索
     * @param $title
     */
    public function search_by_title($title, $start, $limit, $m_uid){
        if(empty($title)) E('_NOTE_ERROR_TITLE_NULL_');
        $cate = D('Note/Category','Service')->get_tree_with_right($m_uid);
        foreach($cate as $k=>$v){
            $cids[] = $v['id'];
            foreach($v['sub'] as $k1=>$v1){
                $cids[] = $v1['id'];
            }
        }
        $where['m_uid'] = $m_uid;
        $where['cid'] = array('in',$cids);
        $aid = M('jobtrain_right')->field('aid')->where($where)->select();
        foreach($aid as $k2=>$v2){
            $aids[] = $v2['aid'];
        }
        //获取有权限的课程id
        $aids = array_unique($aids);
        $map['n.cid'] = array('in',$aids);
        $map['n.title'] = array('like',"%".$title."%");
        $result['list'] = M('Note')->alias('n')
            ->field('n.m_username,n.note_id,n.title,n.c_time,a.title course')
            ->join('LEFT JOIN oa_jobtrain_article a ON a.id = n.cid')
            ->where($map)
            ->limit($start, $limit)
            ->order("c_time desc")
            ->select();
        $result['count'] = M('Note')->alias('n')->where($map)->count();
        return $result;
    }






}