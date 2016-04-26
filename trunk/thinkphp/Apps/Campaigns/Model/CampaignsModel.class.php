<?php

namespace Campaigns\Model;

class CampaignsModel extends AbstractModel {

    //处理成树状结构
    private function _all_p2c(&$departments, &$_all_p2c) {
        foreach ($departments as $_dp) {
            //获取树状接口第一层
            if (empty($_all_p2c[$_dp['cd_upid']])) {
                $_all_p2c[$_dp['cd_upid']] = array();
            }
            $_all_p2c[$_dp['cd_upid']][$_dp['cd_id']] = $_dp['cd_id'];
        }
    }

    private function get_child_cd_id($cd_id, &$_all_p2c, $self = true) {
        $rets = array();
        if ($self) {
            $rets[] = $cd_id;
        }
        if (!empty($_all_p2c[$cd_id])) {
            foreach ($_all_p2c[$cd_id] as $_id) {
                $rets = array_merge($rets, $this->get_child_cd_id($_id, $_all_p2c));
            }
        }
        return $rets;
    }

    /**
     * 递归获取部门所有的子部门
     * @param type $cd_id
     * @param type $tpl_id
     */
    private function _get_dp_child($cd_id, &$_all_p2c) {
        return $this->get_child_cd_id($cd_id, $_all_p2c, false);
    }

    private function _validation_data(&$post) {
        //验证标题
        $post['subject'] = trim($post['subject'], " \r\t\n");
        if ($post['subject'] == '') {
            //标题不能为空
            E('_CAM_SUBJECT_NOT_NULL_ERR');
        }
        if (mb_strlen($post['subject'], 'utf8') > 64) {
            //标题不能超过64个字
            E('_CAM_SUBJECT_MAX_LEN_ERR');
        }
        //验证活动类型
        $post['typeid'] = (int) $post['typeid'];
        if ($post['typeid'] <= 0) {
            //活动类型不能为空
            E('_CAM_TYPEID_NOT_NULL_ERR');
        }
        $cam_t_m = M("CampaignsType");
        if ($cam_t_m->where("id={$post['typeid']} AND status=1")->count() == 0) {
            //活动类型不存在
            E('_CAM_TYPEID_NOT_FOUND_ERR');
        }
        //验证活动封面
        $post['cover'] = (int) $post['cover'];
        if ($post['cover'] <= 0) {
            //活动封面不能为空
            E('_CAM_COVER_NOT_NULL_ERR');
        }
        //验证活动时间
        date_default_timezone_set('PRC');
        $post['begintime'] = strtotime($post['begintime']);
        $post['overtime'] = strtotime($post['overtime']);
        if ($post['begintime'] == 0) {
            //开始时间不能为空
            E('_CAM_BEGINTIME_NOT_NULL_ERR');
        }
        if ($post['overtime'] == 0) {
            //结束时间不能为空
            E('_CAM_OVERTIME_NOT_NULL_ERR');
        }
        if ($post['overtime'] < $post['begintime']) {
            //结束时间不能小于开始时间
            E('_CAM_OVERTIME_LT_BEGINTIME_ERR');
        }
        //验证部门权限
        if ($post['cd_ids']) {
            foreach ($post['cd_ids'] as $cd_id) {
                if (!isset($cd_id['id']) || !isset($cd_id['cd_upid']) || !preg_match('/^\d{1,}\d{0,}$/', $cd_id['id']) || !preg_match('/^\d{1,}\d{0,}$/', $cd_id['cd_upid'])) {
                    E('_CAM_CD_IDS_FORMAT_ERR');
                }
            }
        }
        //验证个人权限
        $post['m_uids'] = trim($post['m_uids'], " \r\t\n");
        if ($post['m_uids'] != '') {
            if (!preg_match('/^\d{1,}(,{1}\d{1,}){0,}\d{0,}$/', $post['m_uids'])) {
                //用户格式不正确
                E('_CAM_M_UIDS_FORMAT_ERR');
                return true;
            }
            $post['m_uids'] = explode(',', $post['m_uids']);
        } else {
            $post['m_uids'] = array();
        }
        $post['content'] = trim($post['content'], " \ r\t\n");
        //验证活动内容
        if ($post['content'] == '') {
            //活动内容不能为空
            E('_CAM_CONTENT_NOT_NULL_ERR');
        }
        $post['content'] = str_replace('&#39;', "'", htmlspecialchars_decode($post['content'], ENT_QUOTES));
        return true;
    }

    public function add_cam($post, $m_uid, $m_username) {
        $this->_validation_data($post);
        //保存数据开始
        $post['uid'] = $m_uid;
        $post['username'] = $m_username;
        $cd_ids = $post['cd_ids'];
        $m_uids = $post['m_uids'];
        $cam_id = $this->_add_data($post);
        //发送推送消息
        if ($post['is_push'] == 1) {
            $this->push_wx($m_uids, $cd_ids, $post['cover'], $cam_id, $post['subject']);
        }
    }

    private function push_wx($m_uids, $cd_ids, $cover, $cam_id, $title) {
        //推送消息到微信
        $wxmsg = \Common\Common\WxqyMsg::instance();
        date_default_timezone_set('PRC');
        $cache = &\Common\Common\Cache::instance();
        $setting = $cache->get('Common.setting');
        $domain = C('PROTOCAL') . $setting['domain'];
        $picurl = $domain . '/attachment/read/' . $cover;
        $desc = "收到一条活动推广";
        $url = "{$domain}/h5/index.html#/app/page/campaigns/campaigns-detail?id={$cam_id}";
        //判断用户和部门权限是否同时为空 如果同时为空则推送全部
        if (!$m_uids && !$cd_ids) {
            $cd_ids = $m_uids = array(-1);
        } else {
            $cd_ids = array_map(function($v) {
                return $v['id'];
            }, $cd_ids);
        }
        $wxmsg->send_news($title, $desc, $url, $m_uids, $cd_ids, $picurl, 19, 50);
    }

    private function _save_data(&$post) {
        //检测活动id及活动是否存在
        $cam_id = (int) $post['actid'];
        unset($post['actid']);
        if ($cam_id < 0) {
            //不存在
            E('_CAM_NOT_FUND_ERR');
            return false;
        }
        if ($this->_m->where("id={$cam_id} AND status<>3")->count() <= 0) {
            //不存在
            E('_CAM_NOT_FUND_ERR');
            return false;
        }
        $cd_ids = $post['cd_ids'];
        $m_uids = $post['m_uids'];
        unset($post['cd_ids'], $post['m_uids']);
        //添加一条推广活动
        $this->start_trans();
        if ($this->_m->where("id={$cam_id}")->save($post) !== false) {
            //开始保存部门权限
            if ($this->_handle_department($cd_ids, $cam_id)) {
                //开始保存用户权限
                if ($m_uids) {
                    foreach ($m_uids as $k => $m_uid) {
                        $m_uids[$k] = array(
                            'm_uid' => $m_uid,
                            'actid' => $cam_id,
                            'created' => NOW_TIME
                        );
                    }
                    //保存到数据库
                    $cam_mem_p_m = M('CampaignsMemP');
                    $cam_mem_p_m->where(array('actid' => array('eq', $cam_id)))->save(array('deleted' => NOW_TIME, 'status' => 3));
                    if ($cam_mem_p_m->addAll($m_uids) === false) {
                        //保存用户权限失败
                        E('_CAM_USER_P_ERR');
                        $this->rollback();
                        return false;
                    }
                    //判断是否需要推送消息
                    $this->commit();
                    return true;
                    //提交保存
                }
                $this->commit();
                return true;
            }
            E('_CAM_DP_P_ERR');
            $this->rollback();
            //部门权限保存失败
            return false;
        }
    }

    private function _add_data(&$post) {
        $cd_ids = $post['cd_ids'];
        $m_uids = $post['m_uids'];
        unset($post['cd_ids'], $post['m_uids']);
        //添加一条推广活动
        $this->start_trans();
        if ($cam_id = $this->insert($post)) {
            //开始保存部门权限
            if ($this->_handle_department($cd_ids, $cam_id)) {

                //保存到数据库
                $cam_mem_p_m = M('CampaignsMemP');
                $cam_mem_p_m->where(array('actid' => array('eq', $cam_id)))->save(array('deleted' => NOW_TIME, 'status' => 3));
                if ($m_uids) {
                    //开始保存用户权限
                    foreach ($m_uids as $k => $m_uid) {
                        $m_uids[$k] = array(
                            'm_uid' => $m_uid,
                            'actid' => $cam_id,
                            'created' => NOW_TIME
                        );
                    }
                    if ($cam_mem_p_m->addAll($m_uids) === false) {
                        //保存用户权限失败
                        E('_CAM_USER_P_ERR');
                        $this->rollback();
                        return false;
                    }
                }
                //分类下活动加1
                $cam_type_m = M('CampaignsType');
                $cam_type_m->where("id={$post['typeid']}")->setInc('count');
                $this->commit();
                return $cam_id;
            }
            E('_CAM_DP_P_ERR');
            $this->rollback();
            //部门权限保存失败
            return false;
        }
        //保存权限
    }

    private function _handle_department($cd_ids, $cam_id) {
        //检出所有的父id
        $p_ids = array();
        foreach ($cd_ids as $dpv) {
            if ($dpv['cd_upid'] > 1) {
                if (!in_array($dpv['cd_upid'], $p_ids)) {
                    $p_ids[] = $dpv['cd_upid'];
                }
            }
        }
        //开始对所有父级 的子 进行计数 并判断是否需要递归查询
        foreach ($cd_ids as $dpk => $dpv) {
            $cd_ids[$dpk]['is_show'] = 1;
            if (in_array($dpv['id'], $p_ids)) {
                $cd_ids[$dpk]['is_query'] = 0;
            } else {
                $cd_ids[$dpk]['is_query'] = 1;
            }
            $cd_ids[$dpk]['cd_id'] = $dpv['id'];
            unset($cd_ids[$dpk]['id']);
            $cd_ids[$dpk]['created'] = NOW_TIME;
        }
        //开始查询所有部门
        $all = array();
        //获取部门的缓存
        $cache = &\Common\Common\Cache::instance();
        $departments_cache = $cache->get('Common.department');
        $_all_p2c = array();
        $this->_all_p2c($departments_cache, $_all_p2c);
        unset($departments_cache);
        foreach ($cd_ids as $dpv) {
            $dpv['actid'] = $cam_id;
            if ($dpv['is_query']) {
                //查出该部门下所有的部门
                $childs = $this->_get_dp_child($dpv['cd_id'], $_all_p2c);
                foreach ($childs as $ck => $cv) {
                    $childs[$ck] = array(
                        'is_show' => 0,
                        'cd_id' => $cv,
                        'created' => NOW_TIME,
                        'actid' => $cam_id
                    );
                }
                unset($dpv['is_query'], $dpv['cd_upid']);
                $all[] = $dpv;
                $all = array_merge($all, $childs);
            } else {
                unset($dpv['is_query']);
                $all[] = $dpv;
            }
        }
        $cam_d_p_m = M('CampaignsDepartmentP');
        $cam_d_p_m->where(array('actid' => array('eq', $cam_id)))->save(array('deleted' => NOW_TIME, 'status' => 3));
        if ($all) {
            if ($cam_d_p_m->addAll($all) !== FALSE) {
                return true;
            }
            $this->rollback();
            E('_CAM_DP_P_ERR');
            return false;
        }
        return true;
    }

    public function save_cam($post) {
        $this->_validation_data($post);
        //保存前校验是否应该发送信息
        $is_push = $this->_m->where("is_push=0 AND id={$post['actid']}")->count();
        $cd_ids = $post['cd_ids'];
        $m_uids = $post['m_uids'];
        $this->_save_data($post);
        if ($post['is_push'] == 1 && $is_push == 1) {
            $this->push_wx($m_uids, $cd_ids, $post['cover'], $post['actid'], $post['subject']);
        }
    }

    public function list_cam($get) {
        $where = "a.status<>3";
        if (isset($get['typeid']) && $get['typeid'] > 0) {
            $where.=" AND a.typeid={$get['typeid']}";
        }
        if (isset($get['status']) && $get['status'] != 2) {
            $where.=" AND a.is_push={$get['status']}";
        }
        date_default_timezone_set('PRC');
        //校验时间

        if (isset($get['start_date']) && isset($get['end_date'])) {
            $start_date = trim($get['start_date'], " \r\n\t");
            $end_date = trim($get['end_date'], " \r\n\t");
            if ($start_date != '' && $end_date != '') {
                //验证时间
                if (!preg_match("/^\d{4}-\d{1,2}-\d{1,2}$/", $start_date) || !preg_match("/^\d{4}-\d{1,2}-\d{1,2}$/", $end_date)) {
                    //搜索的时间格式不正确
                    E('_CAM_SEARCH_DATE_FORMAT_ERR');
                    return false;
                }
                //验证时间间距是否
                $start_date = strtotime($start_date);
                $end_date = strtotime($end_date . " +1 day") - 1;
                if ($end_date < $start_date) {
                    E('_CAM_SEARCH_DATE_FORMAT_ERR');
                    return false;
                }
                $where.=" AND (a.begintime>={$start_date} AND a.overtime<={$end_date})";
            }
        }
        //校验标题
        if (isset($get['subject']) && $get['subject'] != '') {
            $get['subject'] = trim($get['subject'], " \r\n\t");
            //验证长度
            if (mb_strlen($get['subject'], 'utf8') <= 64) {
                $where.=" AND a.subject LIKE '%{$get['subject']}%'";
            } else {
                E('_CAM_SUBJECT_MAX_LEN_ERR');
                return FALSE;
            }
        }
        if ($count = $this->_m->alias('a')->where($where)->count()) {
            $page_size = 15;
            $page_sum = (int) ceil($count / $page_size);
            $page = (int) $get['page'];
            $page = $page < 0 ? 1 : ($page < $page_sum ? $page : $page_sum);
            $startpage = $page == 1 ? 0 : ($page - 1) * $page_size;
            //查询分页的数据
            $field = 'a.id,a.subject,b.title,a.begintime,a.overtime,a.is_push,d.share,d.hits';
            $join = 'INNER JOIN oa_campaigns_type AS b ON a.typeid=b.id LEFT JOIN (SELECT SUM(share) share,SUM(hits) hits,actid FROM oa_campaigns_total as e WHERE e.status<>3 GROUP BY actid) AS d ON a.id=d.actid';
            $sql = "SELECT {$field} FROM oa_campaigns a {$join}  WHERE {$where} ORDER BY a.created DESC LIMIT {$startpage},{$page_size}";
            $cams = $this->_m->query($sql);
        }
        //没有更多活动
        $result = array(
            'page' => $page?:0,
            'pages' => $page_sum?:0,
            'limit' => $page_size?:0,
            'count' => $count?:0,
            'list' => $cams?:array()
        );
        return $result;
    }

    public function detail_cam($id) {
        if ($id > 0) {
            //查询出基本数据
            $field = 'a.is_push,a.id,a.content,a.subject,a.begintime,a.overtime,b.title,c.hits,c.share'; //
            $where = "a.id={$id} AND a.status<>3";
            $ajoin = "LEFT JOIN __CAMPAIGNS_TYPE__ AS b ON a.typeid=b.id";
            $bjoin = "LEFT JOIN (SELECT SUM(share) AS share,SUM(hits) AS hits,actid FROM oa_campaigns_total WHERE actid={$id}) AS c ON a.id=c.actid"; //查出分享数
            $cam = $this->_m->alias('a')->field($field)
                    ->join($ajoin)
                    ->join($bjoin)
                    ->where($where)
                    ->find();
            if ($cam) {
                //查出排行榜
                $field = "SUM(a.share) AS share,SUM(a.hits) AS hits,b.m_username";
                $where = "a.actid={$id} AND a.status<>3";
                $cam_total_m = M('CampaignsTotal');
                $cam['tops'] = $cam_total_m->field($field)
                        ->alias('a')
                        ->where($where)
                        ->join("__MEMBER__ AS b ON a.saleid=b.m_uid")
                        ->order("hits DESC")
                        ->group('saleid')
                        ->limit(10)
                        ->select();
            }
        }
        //不存在
        return $cam;
    }

    public function dels_cam($ids) {
        $ids = trim($ids, " \r\n\t");
        if ($ids != '') {
            //验证数据格式
            if (!preg_match('/^\d{1,}(,{1}\d{1,}){0,}\d{0,}$/', $ids)) {
                //用户格式不正确
                E('_CAM_M_UIDS_FORMAT_ERR');
            }
            //更新分类下的统计字段
            if ($type_counts = $this->_m->field("COUNT(id) AS count,typeid id")->where("id IN({$ids}) AND status<>3")->group('typeid')->select()) {
                $deleted = NOW_TIME;
                $update = "UPDATE oa_campaigns SET status=3,deleted={$deleted} WHERE id IN({$ids});";
                if ($count = $this->_m->execute($update) > 0) {
                    $update = "UPDATE oa_campaigns_department_p SET status=3,deleted={$deleted} WHERE actid IN({$ids});";
                    $update.="UPDATE oa_campaigns_mem_p SET status=3,deleted={$deleted} WHERE actid IN({$ids});";
                    $update.="UPDATE oa_campaigns_total SET status=3,deleted={$deleted} WHERE actid IN({$ids});";
                    foreach ($type_counts as $type_count) {
                        $update.="UPDATE oa_campaigns_type SET count=count-{$type_count['count']} WHERE id={$type_count['id']};";
                    }
                    $this->_m->execute($update);
                }
            }
        }
        return true;
    }

    public function get_edit_detail($id) {
        if ($id > 0) {
            //查询出基本数据
            $field = 'a.is_push,a.id,a.content,a.subject,a.begintime,a.overtime,b.title,a.typeid,a.cover'; //,c.hits,c.share
            $where = "a.id={$id} AND a.status<>3";
            $ajoin = "LEFT JOIN __CAMPAIGNS_TYPE__ AS b ON a.typeid=b.id";
            //$bjoin="LEFT JOIN (SELECT SUM(share) AS share,SUM(hits) AS hits,actid FROM oa_campaign_total WHERE actid={$id}) AS c ON a.id=c.actid";
            $cam = $this->_m->alias('a')->field($field)
                    ->join($ajoin)
                    //->join($bjoin)
                    ->where($where)
                    ->find();
            if ($cam) {
                //查询出部门
                $cam_dp_m = M('CampaignsDepartmentP');
                $field = "a.cd_id,a.is_show,b.cd_name";
                $where = "a.actid={$id} AND a.status<>3";
                $ajoin = "__COMMON_DEPARTMENT__ AS b ON a.cd_id=b.cd_id";
                $cd_ids = $cam_dp_m->alias('a')
                        ->field($field)
                        ->join($ajoin)
                        ->where($where)
                        ->select();
                $cam['cd_ids'] = $cd_ids;
                //查询出用户权限
                $cam_dp_m = M('CampaignsMemP');
                $field = "a.m_uid,b.m_username";
                $where = "a.actid={$id} AND a.status<>3";
                $ajoin = "__MEMBER__ AS b ON a.m_uid=b.m_uid";
                $m_uids = $cam_dp_m->alias('a')
                        ->field($field)
                        ->join($ajoin)
                        ->where($where)
                        ->select();
                $cam['m_uids'] = $m_uids;
                $cache = &\Common\Common\Cache::instance();
                $setting = $cache->get('Common.setting');
                $domain = C('PROTOCAL') . $setting['domain'];
                $title = $post['subject'];
                $cam['picurl'] = $domain . '/attachment/read/' . $cam['cover'];
            }
        }
        //不存在
        return $cam;
    }

    public function get_list_api($get, $m_uid) {
        $awhere = 'a.status<>3 AND a.is_push=1';
        if (isset($get['subject']) && $get['subject'] != '') {
            if ($get['subject'] = trim($get['subject'], " \r\n\t")) {
                $awhere.=" AND a.subject LIKE '%" . $get['subject'] . "%'";
            }
        }
        if (isset($get['typeid']) && $get['typeid'] != '') {
            if (($get['typeid'] = (int) $get['typeid']) > 0) {
                $awhere.=" AND a.typeid={$get['typeid']}";
            }
        }
        //查询出我的部门
        $mem_dp_m = M('MemberDepartment');
        if ($cd_ids = $mem_dp_m->field('cd_id')->where("m_uid={$m_uid} AND md_status<>3")->select()) {
            $cd_ids = implode(',', array_map(function($v) {
                        return $v['cd_id'];
                    }, $cd_ids));
        }
        //查询出自己所在部门不能查看的
        $dep_p = "SELECT actid FROM oa_campaigns_department_p WHERE cd_id NOT IN({$cd_ids}) AND status<>3 GROUP BY actid";
        if ($dep_p_actids = $this->_m->query($dep_p)) {
            $dep_p_actids = implode(',', array_map(function($v) {
                        return $v['actid'];
                    }, $dep_p_actids));
            $awhere.=" AND a.id NOT IN({$dep_p_actids})";
        }
        //查询出自己能查看的活动
        $mem_p = "SELECT actid FROM oa_campaigns_mem_p WHERE m_uid={$m_uid} AND status<>3";
        if ($mem_p_actids = $this->_m->query($mem_p)) {
            $mem_p_actids = implode(',', array_map(function($v) {
                        return $v['actid'];
                    }, $mem_p_actids));
            $awhere = " ({$awhere}) OR (a.id IN({$mem_p_actids}) AND a.status<>3)";
        }
        //查询出列表
        $cache = &\Common\Common\Cache::instance();
        $setting = $cache->get('Common.setting');
        $domain = C('PROTOCAL') . $setting['domain'];
        $title = $post['subject'];
        $domain = $domain . '/attachment/read/';
        $fields = "a.id,a.subject,a.begintime,a.overtime,CONCAT('{$domain}',a.cover) cover,b.title";
        $ajoin = "__CAMPAIGNS_TYPE__ b ON a.typeid=b.id";

        //分页
        $page_size = 10;
        if ($count = $this->_m->alias('a')->where($awhere)->count()) {
            $page_sum = (int) ceil($count / $page_size);
            $page = (int) $get['page'];
            $page = $page <= 0 ? 1 : ($page < $page_sum ? $page : $page_sum);
            $cams = $this->_m->alias('a')
                    ->field($fields)
                    ->join($ajoin)
                    ->where($awhere)
                    ->page("{$page},{$page_size}")
                    ->order('a.id DESC')
                    ->select();
        } else {
            $page = 1;
            $page_sum = 0;
            $cams = array();
        }
        $result = array(
            'page' => $page,
            'pages' => $page_sum,
            'limit' => $page_size,
            'count' => $count,
            'list' => $cams
        );
        return $result;
    }

    public function get_detail_api($id, $uid) {
        if ($id > 0) {
            $cache = &\Common\Common\Cache::instance();
            $setting = $cache->get('Common.setting');
            $domain = C('PROTOCAL') . $setting['domain'];
            $title = $post['subject'];
            $domain = $domain . '/attachment/read/';
            $field = "id,CONCAT('{$domain}',cover) cover,subject,content";
            $where = "id={$id} AND status<>3 AND is_push=1";
            if ($cam = $this->_m->field($field)->where($where)->find()) {
                //检测是否包含推广人的id
                if ($uid > 0) {
                    //该分享人浏览数加1
                    $update = "UPDATE oa_campaigns_total SET hits=hits+1 WHERE saleid={$uid} AND actid={$id} AND status<>3;";
                    $this->_m->execute($update);
                }
                return $cam;
            }
        }
        return array();
    }

    public function share_cam_api($id, $uid) {
        if ($id > 0 && $uid > 0) {
            //该分享人浏览数加1
            $update = "INSERT oa_campaigns_total(actid,saleid,share,hits,created) values ({$id},{$uid},1,1,UNIX_TIMESTAMP()) ON DUPLICATE KEY UPDATE share=share+1,updated=UNIX_TIMESTAMP();";
            D('Score/Score', 'Service')->scoreRuleChange($uid, 7);
            $this->_m->execute($update);
        }
        return array();
    }

    public function my_share_api($uid, $page, $typeid) {
        if ($uid > 0) {
            $where = "a.status<>3 AND a.saleid={$uid}";
            if ($typeid > 0) {
                $where.=" AND b.typeid={$typeid}";
            }
            $join = "INNER JOIN oa_campaigns b ON a.actid=b.id";
            if ($count = $this->_m->table('oa_campaigns_total')->alias('a')->where($where)->join($join)->count()) {
                $page_size = 15;
                $page_sum = (int) ceil($count / $page_size);
                $page = $page < 0 ? 1 : ($page < $page_sum ? $page : $page_sum);
                $startpage = $page == 1 ? 0 : ($page - 1) * $page_size;
                $cache = &\Common\Common\Cache::instance();
                $setting = $cache->get('Common.setting');
                $domain = C('PROTOCAL') . $setting['domain'];
                $title = $post['subject'];
                $domain = $domain . '/attachment/read/';
                $field = "b.id id,CONCAT('{$domain}',b.cover) cover,b.subject,a.share,a.hits";
                $select = "SELECT {$field} FROM oa_campaigns_total a {$join} WHERE {$where} ORDER BY a.created DESC LIMIT {$startpage},{$page_size}";
                $list = $this->_m->query($select);
            }
        }
        $result = array(
            'page' => $page? : 0,
            'pages' => $page_sum? : 0,
            'limit' => $page_size? : 0,
            'count' => $count? : 0,
            'list' => $list? : array()
        );
        return $result;
    }

    public function data_center($page) {
        $count_sql = "SELECT COUNT(*) as count FROM (SELECT saleid FROM oa_campaigns_total WHERE status<>3 GROUP BY saleid) as a LIMIT 1;";
        $count = $this->_m->query($count_sql);
        $count = (int) $count[0]['count'];
        if ($count>0) {
            $page_size = 20;
            $page_sum = (int) ceil($count / $page_size);
            $page = $page <= 0 ? 1 : ($page < $page_sum ? $page : $page_sum);
            $where = "WHERE a.status<>3";
            $group = "GROUP BY a.saleid";
            $orderby = "ORDER BY a.hits DESC";
            $start_page = ($page == 1 ? 0 : $page - 1) * $page_size;
            $limit = "LIMIT {$start_page},{$page_size}";
            $cd_name = "(SELECT GROUP_CONCAT(e.cd_name) cd_name FROM oa_member_department c INNER JOIN oa_common_department e ON c.cd_id=e.cd_id WHERE c.md_status<>3 AND c.m_uid=a.saleid) as cd_name";
            $field = "@y:=@y+1 sort,SUM(a.share) share,SUM(a.hits) hits,b.m_username,{$cd_name}";
            $join = "INNER JOIN oa_member b ON a.saleid=b.m_uid";
            $totals_sql = "SELECT {$field} FROM oa_campaigns_total a {$join},(SELECT @y:=0) d {$where} {$group} {$orderby} {$limit}";
            $totals = $this->_m->query($totals_sql);
        }
        $result = array(
            'page' => $page?:0,
            'pages' => $page_sum?:0,
            'limit' => $page_size?:0,
            'count' => $count?:0,
            'list' => $totals?:array()
        );
        return $result;
    }

}
