<?php
/**
 * Created by PhpStorm.
 * User: zhang mi
 * Date: 2016/4/12
 * Time: 15:28
 */
namespace Note\Model;

class NoteModel extends AbstractModel{

    protected $tableName = 'Note';
    // 构造方法
    public function __construct(){
        parent::__construct();
    }

    /**
     * @param array $conds 查询条件
     * @param int $start
     * @param int $limit
     * @param int $cid
     */
    public function get_list($cids, $conds, $start, $limit){
        $where = '';
        $model = new \Think\Model();
        if($cids != ''){
            if(is_array($cids)){
                $map['cid'] = array('in',$cids);
            }else{
                $map['cid'] = array('eq',$cids);
            }
            $id = M('jobtrain_article')->field('id')->where($map)->fetchSql(false)->select();
            foreach($id as $k => $v){
                $ids[] = $v['id'];
            }
            if($ids) $arr1 = $ids;
            $where['n.cid'] = array('in',$arr1);
        }
        if($conds['course'] != ''){
            $c_sql = "SELECT id FROM oa_jobtrain_article where title like '%".$conds['course']."%'";
            $c_id = $model->query($c_sql);
            foreach($c_id as $k=>$v){
                $aids[] = $v['id'];
            }
            if($aids) $arr2 = $aids;
            if($arr1 && $arr2){
                $aid = array_intersect($arr1,$arr2);
                $where['n.cid'] = array('in',$aid);
            }
            $where['n.cid'] = array('in',$aids);
        }
        if($conds['title'] != ''){
            $where['n.title'] = array('like',"%".$conds['title']."%");
        }

        if($conds['m_username'] != ''){
            $where['n.m_username'] = array('like',"%".$conds['m_username']."%");
        }
        
        if($conds['start_time'] != '' && $conds['end_time'] != ''){
            date_default_timezone_set('PRC');
            $where['n.c_time'] = array('between',array(strtotime($conds['start_time']),strtotime("{$conds['end_time']} +1 day")-1));
        }
        $result['list'] = $this->_m->alias('n')
                        ->field('n.note_id,n.title,n.m_username,n.c_time,a.title course')
                        ->join("LEFT JOIN oa_jobtrain_article a ON n.cid = a.id")
                        ->order('n.c_time desc')
                        ->where($where)
                        ->limit($start,$limit)
                        ->select();
        $result['count'] = $this->_m->alias('n')
                        ->field('n.title,n.m_username,n.c_time,a.title course')
                        ->join("LEFT JOIN oa_jobtrain_article a ON n.cid = a.id")
                        ->where($where)
                        ->count();
        return $result;
    }

    /**
     * 查看笔记详情
     * @param $note_id 课程笔记id
     * @return mixed
     */
    public function get_note_detail($note_id){
        if(!$note_id){E('_NOTE_ERROR_ID_NULL_');}
        $result = $this->_m->alias('n')
                ->field('n.*,a.title course')
                ->join('LEFT JOIN oa_jobtrain_article a ON a.id = n.cid')
                ->join('LEFT JOIN oa_jobtrain_category c ON a.cid = c.id')
                ->where("n.note_id = '{$note_id}'")
                ->find();
        return $result;
    }

    /**
     * 添加笔记
     * @param $cid
     * @param $title
     * @param $content
     * @param $attachs
     * @return mixed
     */
    public function add_note($cid, $title, $content, $attachs, $m_uid){
        if(!$cid) E('_NOTE_ERROR_CID_NULL_');
        if(empty($title) || empty($content)) E('_NOTE_ERROR_VAL_NULL_');
        //内容处理
        $data['content'] = $content;
        $data['cid'] = $cid;
        $data['title'] = $title;
        $data['attachments'] = $attachs;
        $data['c_time'] = time();
        $data['m_uid'] = $m_uid;
        $data['m_username'] = '管理员';
        if($m_uid != 0){
            $res = $this->get_username_info($m_uid);
            $data['m_username'] = $res['m_username'];
        }
        $result = $this->_m->add($data);
        if($result)
        $last_id = $this->_m->getLastInsID();
        return $last_id;
    }

    public function edit_note($note_id, $title, $content, $attachs){
        if(!$note_id) E("_NOTE_ERROR_ID_NULL_");
        if(empty($title) || empty($content)) E('_NOTE_ERROR_VAL_NULL_');
        //内容处理
        $data['content'] = $content;
        $data['title'] = $title;
        if(!empty($attachs))
        $data['attachments'] = $attachs;
        $data['u_time'] = time();
        $result = $this->_m->where("note_id = '$note_id'")->save($data);
        if($result !== false){
            return true;
        }
        E('_NOTE_ERROR_EDIT_FAILD_');
    }

    /**
     * 删除课堂笔记
     * @param $note_id 笔记id
     */
    public function delete_note($note_ids){
            //验证ids
            if (!is_array($note_ids) || !count($note_ids)) {
                //删除的id格式不正确
                E('_NOTE_ERROR_ID_TYPE_');
            }
            $ids = implode(',',$note_ids);
            $where['note_id'] = array('in',$ids);
            $result = $this->_m->where($where)->delete();
            if ($result !== false);return true;
            //删除失败
            E('_NOTE_ERROR_DEL_FAILD_');
    }

    /**
     * 删除附件(编辑页面)
     * @param $at_id 附件id
     * @param $note_id 笔记id
     */
    public function delete_attach_edit($at_id, $note_id){
        if(!$at_id) E('_NOTE_ERROR_ATTACH_ID_');
        if(!$note_id) E('_NOTE_ERROR_ID_NULL_');
        $attach_id = $this->_m->where("note_id = '$note_id'")->getField("attachments");
        $arr = explode(',',$attach_id);
        $key = array_search($at_id,$arr);
        if($key !== false){
            array_splice($arr,$key,1);
            $re = $this->_m->where("note_id = '$note_id'")->setfield('attachments',implode(',',$arr));
        }
        if($re){
          $res = M('common_attachment')->delete($at_id);
        }
        return $res;
    }

    /**
     * 删除附件(添加页面)
     * @param $at_id 附件id
     */
    public function delete_attach_add($at_id){
        if(!$at_id) E('_NOTE_ERROR_ATTACH_ID_');
        $res = M('common_attachment')->delete($at_id);
        return $res;
    }

    /**
     * 获取登陆用户名
     * @return mixed
     */
    public function get_username_info($m_uid){
        $model = new \Think\Model();
        $sql = "SELECT m_username FROM oa_member WHERE m_uid = '".$m_uid."'";
        $res = $model->query($sql);
        $data['m_username'] = $res[0]['m_username'];
        return $data;
    }

    //-------------------h5--------------------------
    /**
     * 按照分类查看课程
     * @param $cid
     * @return mixed
     */
    public function course($cid, $m_uid){
        if(! $cid) E('_NOTE_ERROR_CID_NULL_');
        $where['m_uid'] = $m_uid;
        $where['cid'] = $cid;
        $aid = M('jobtrain_right')->field('aid')->where($where)->select();
        foreach($aid as $k => $v){
            $ids[] = $v['aid'];
        }
        $result = '';
        if(count($ids)){
            $map['id'] = array('in',$ids);
            $result = M('jobtrain_article')->field('id,title,author,cover_id')->where($map)->select();
        }
        return $result;
    }

    /**
     * 按分类搜索笔记
     * @param $cid
     */
    public function search_by_cate($cid, $start, $limit, $m_uid){
        if(!$cid) E('_NOTE_ERROR_CID_NULL_');
        $aid = $this->course($cid, $m_uid);
        $result = '';
        if(!$aid){
            return $result;exit;
        }
        foreach ($aid as $k => $v) {
            $ids[] = $v['id'];
        }
        $where['n.cid'] = array('in',$ids);
        $result['list'] = $this->_m->alias('n')
            ->field('n.note_id,n.title,n.m_username,n.c_time,a.title course')
            ->join("LEFT JOIN oa_jobtrain_article a ON n.cid = a.id")
            ->order('n.c_time desc')
            ->limit($start, $limit)
            ->where($where)
            ->select();
        $result['count'] = $this->_m->alias('n')->where($where)->count();
        return $result;
    }

    /**
     * 学习过的课程
     * @param $m_uid
     * @return mixed
     */

    public function learned_course($m_uid){
        $result = M('jobtrain_study')->alias('s')
            ->field('s.id,a.title,a.id,a.author,a.cover_id')
            ->where("s.m_uid = '$m_uid'")
            ->join('LEFT JOIN oa_jobtrain_article a ON s.aid = a.id')
            ->limit(0,10)
            ->select();
        return $result;
    }


    /**
     * 浏览数量+1 成功则返回true 否则返回false
     *
     * @param int $id
     * @return bool
     */
    public function inc_scan_num($note_id) {
        if(!$note_id) E('_NOTE_ERROR_ID_NULL_');
        $result = $this->_m->where("note_id = '$note_id'")->setInc("scan_num");
        if($result) return true;
    }

    /**
     * 我的笔记
     * @param $m_uid
     * @return mixed
     */
    public function my_list($m_uid, $start, $limit){
        if(!$m_uid) E('_NOTE_ERROR_UID_NULL_');
        $result['list'] = $this->_m->alias('n')
                ->field('n.m_username,n.note_id,n.title,n.c_time,a.title course')
                ->join('LEFT JOIN oa_jobtrain_article a ON a.id = n.cid')
                ->where("n.m_uid = '$m_uid'")
                ->order('n.c_time desc')
                ->limit($start, $limit)
                ->select();
        $result['count'] = $this->_m->where("m_uid = '$m_uid'")->count();
        return $result;
    }

    /**
     * 附件处理
     * @param $attachs 文本附件
     * @param $audimgs 音图附件
     */
    public function deal_note_attachs($attachs, $audimgs){
            $attachments = array(
                'attachs' => array(),
                'audimgs' =>array()
            );
            //文件附件处理
            if($attachs){
                foreach($attachs as $k => $v){
                    $attachments['attachs'][] = array(
                        'id' => $v['id'],
                        'name'=>$v['name'],
                        'size'=>$v['size']
                    );
                }
            };
            //音图附件处理
            if($audimgs) {
                foreach ($audimgs as $k => $v) {
                    if($v['img_id']){
                        $attachments['audimgs'][] = array(
                            'audio_id' => $v['audio_id'],
                            'audio_duration' => $v['audio_duration'],
                            'img_id' => $v['img_id'],
                        );
                    }
                }
            }
        return serialize($attachments);
    }








}