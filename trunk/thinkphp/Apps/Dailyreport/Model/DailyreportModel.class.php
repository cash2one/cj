<?php

/**
 * User: keller
 * Date: 16/3/16
 * Time: 下午11:22
 */

namespace Dailyreport\Model;

class DailyreportModel extends AbstractModel {

    public $prefield = 'dr_';

    public function get($dr_id) {
        $where['dr_id'] = array('eq', $dr_id);
        $where['dr_status'] = array('neq', 3);
        return $this->_m->where($where)->find();
    }

    public function save($dr) {
        return $this->_m->insert($dr);
    }

    public function getMember($m_uid) {
        $sql = "SELECT m_uid,m_mobilephone,m_email,m_username,m_face FROM oa_member WHERE m_uid=" . $m_uid;
        return $this->_m->fetch_row($sql);
    }

    public function getMembers($m_uids) {
        $sql = "SELECT m_uid,m_mobilephone,m_email,m_username,m_face FROM oa_member WHERE m_uid in (" . implode(",", $m_uids) . ")";
        return $this->_m->query($sql);
    }

    public function getReceivers($dr_id) {
        $sql = "SELECT a.m_uid,a.m_mobilephone,a.m_email,a.m_username,a.m_face FROM oa_member a INNER JOIN oa_dailyreport_mem b ON a.m_uid=b.m_uid and b.get_level=1 and b.dr_id=" . $dr_id;
        return $this->_m->query($sql);
    }

    public function getCopyTos($dr_id) {
        $sql = "SELECT a.m_uid,a.m_mobilephone,a.m_email,a.m_username,a.m_face FROM oa_member a INNER JOIN oa_dailyreport_mem b ON a.m_uid=b.m_uid and b.get_level=0 and b.dr_id=" . $dr_id;
        return $this->_m->query($sql);
    }

    private function _getCreatedById($dr_id) {
        $created = $this->_m->field('dr_created')->where(array('dr_id' => array('eq', $dr_id)))->find();
        return $created ? $created['dr_created'] : NOW_TIME;
    }

    public function get_track($dr_id) {
        $where = array('dr_id' => array('eq', $dr_id));
        $dr = $this->_m->field('dr_id,dr_from_dr_id,m_uid')->where($where)->find();
        $dr_id = $dr['dr_from_dr_id'] == 0 ? $dr['dr_id'] : $dr['dr_from_dr_id'];
        //来源
        $tracks = array();
        $tracks[0]['dr_created'] = $this->_getCreatedById($dr_id);
        $tracks[0]['sender'] = $this->getMember($dr['m_uid']);
        $tracks[0]['receiver'] = $this->getReceivers($dr_id);
        $tracks[0]['copyto'] = $this->getCopyTos($dr_id);
        //查询出所有的轨迹
        if ($drs = $this->_m->field('dr_id,dr_forword_uid,dr_created')->where(array('dr_from_dr_id' => array('eq', $dr_id)))->select()) {
            foreach ($drs as $dr_o) {
                //转发报告
                $tracks[] = array(
                    'dr_created' => $dr_o['dr_created'],
                    'track' => $this->getMember($dr_o['dr_forword_uid']),
                    'receiver' => $this->getReceivers($dr_o['dr_id'])
                );
            }
        }

        return $tracks;
    }

    /**
     * 验证并处理基本数据
     */
    const TPL_TYPE_IMG = 'img';
    const TPL_TYPE_TEXT = 'text';
    const TPL_TYPE_TEXTAREA = 'textarea';
    const TPL_TYPE_DATE = 'date';
    const TPL_TYPE_TIME = 'time';
    const TPL_TYPE_DATEANDTIME = 'dateandtime';
    const TPL_TYPE_RADIO = 'radio';
    const TPL_TYPE_CHECKBOX = 'checkbox';
    const TPL_TYPE_NUMBER = 'number';

    private $_tpl_types = array(
        self::TPL_TYPE_IMG,
        self::TPL_TYPE_TEXT,
        self::TPL_TYPE_TEXTAREA,
        self::TPL_TYPE_DATE,
        self::TPL_TYPE_TIME,
        self::TPL_TYPE_DATEANDTIME,
        self::TPL_TYPE_RADIO,
        self::TPL_TYPE_CHECKBOX,
        self::TPL_TYPE_NUMBER
    );

    /**
     * 获取日报分页列表
     * @author keller<likai@vchangyi.com>
     * @route
     * @method
     * @param $page
     */
    public function get_list($param = array()) {
        $page = intval($param['page']);

        //拼装查询条件
        $sql_where = "WHERE oa_dailyreport.dr_status <> 3";
        //部门
        if (array_key_exists("drt_type", $param) && $param["drt_type"] != "" && $param["drt_type"] != "0") {
            $sql_where .= " AND oa_dailyreport_tpl.drt_id = " . $param["drt_type"];
        }
        //提交人
        if (array_key_exists("submitter", $param) && $param["submitter"] != "") {
            $sql_where .= " AND oa_dailyreport.m_username LIKE '%" . $param["submitter"] . "%'";
        }
        //转发人
        if (array_key_exists("forwarded", $param) && $param["forwarded"] != "") {
            $sql_where .= " AND oa_dailyreport.dr_forword_uname LIKE '%" . $param["forwarded"] . "%'";
        }
        //提交时间-开始时间
        if (array_key_exists("start_date", $param) && $param["start_date"] != "") {
            $sql_where .= " AND oa_dailyreport.dr_created >= " . $param["start_date"];
        }
        //提交时间-结束时间
        if (array_key_exists("end_date", $param) && $param["end_date"] != "") {
            $sql_where .= " AND oa_dailyreport.dr_created <= " . $param["end_date"];
        }
        //接收人
        if (array_key_exists("receiver", $param) && $param["receiver"] != "") {
            $sql_where .= " AND EXISTS (SELECT * FROM oa_dailyreport_mem WHERE oa_dailyreport_mem.m_username LIKE '%" . $param['receiver'] . "%'
            AND oa_dailyreport_mem.get_level=1 AND oa_dailyreport_mem.drm_status <> 4 AND oa_dailyreport_mem.dr_id = oa_dailyreport.dr_id)";
        }


        $sql_count = "SELECT COUNT(*) FROM (SELECT oa_dailyreport.dr_id,oa_dailyreport_tpl.drt_name,oa_dailyreport.m_username as submitter, oa_dailyreport.dr_subject,oa_dailyreport.dr_created FROM oa_dailyreport INNER JOIN oa_dailyreport_tpl on oa_dailyreport_tpl.drt_id = oa_dailyreport.dr_type {$sql_where}) t";
        $count = $this->_m->result($sql_count);

        $page_len = 15;
        $page_num = ceil($count / $page_len);
        $page = $page <= 0 ? 1 : ($page > $page_num ? $page_num : $page);
        // 判断当前是否分页
        $sql_limit = '';
        if ($page) {
            $sql_limit = " LIMIT " . (($page - 1) * $page_len) . ',' . $page_len;
        }

        $sql = "SELECT oa_dailyreport.dr_forword_uid,oa_dailyreport.dr_forword_uname,oa_dailyreport.dr_id,oa_dailyreport_tpl.drt_name,oa_dailyreport.m_username as submitter, oa_dailyreport.dr_subject,oa_dailyreport.dr_created,
	              (SELECT GROUP_CONCAT(DISTINCT oa_common_department.cd_name)
	               FROM oa_common_department
		           INNER JOIN oa_member_department on oa_common_department.cd_id=oa_member_department.cd_id
	               WHERE oa_member_department.m_uid=oa_dailyreport.m_uid and oa_member_department.md_status<>3) as cd_name
                FROM oa_dailyreport
	              INNER JOIN oa_dailyreport_tpl on oa_dailyreport_tpl.drt_id = oa_dailyreport.dr_type {$sql_where} ORDER BY oa_dailyreport.dr_created DESC {$sql_limit}";


        $drs = $this->_m->query($sql);

        $result = array(
            'page' => $page,
            'pages' => $page_num,
            'limit' => $page_len,
            'count' => $count,
            'list' => $drs
        );
        $cache = &\Common\Common\Cache::instance();
        $setting = $cache->get('Common.setting');
        $result['shortname'] = $setting['sys_email_user'];
        return $result;
    }

    public function del_dr($dr_id) {
        if ((intval($dr_id) <= 0)) {
            E('_ERR_DAILYREPORT_DEL_ERR');
            return false;
        }
        if ($this->delete(intval($dr_id)) !== false) {
            return true;
        }
        E('_ERR_DAILYREPORT_DEL_ERR');
        return false;
    }

    public function get_admin_report($dr_id) {
        //验证id
        if ($dr_id <= 0) {
            return false;
        }
        /* 查出基本信息 */
        $where['dr_id'] = array('eq', $dr_id);
        $drtbn = 'oa_dailyreport';
        $drttbn = $drtbn . '_tpl';
        $mdtbn = 'oa_member_department';
        $dptbn = 'oa_common_department';
        $feild = "dr_id,m_username,dr_subject,dr_created,drt_name,dr_is_new,m_uid,dr_from_dr_id";
        $dr = $this->_m
                ->alias('a')
                ->field($feild)
                ->where($where)
                ->join('JOIN oa_dailyreport_tpl as b ON a.dr_type=b.drt_id')
                ->find();
        $dr['dr_created'] = (int) $dr['dr_created'] * 1000;
        //查出部门名称
        $dp = $this->_m
                ->table($mdtbn)
                ->alias('a')
                ->field('b.cd_name')
                ->join("{$dptbn} as b ON b.cd_id=a.cd_id")
                ->where(array('a.m_uid' => array('eq', $dr['m_uid']), 'a.md_status' => array('neq', 3)))
                ->select();
        $dr['cd_names'] = $dp; //
        $cache = &\Common\Common\Cache::instance();
        $setting = $cache->get('Common.setting');
        $dr['shortname'] = $setting['sys_email_user'];
        unset($dr['cd_id']);
        //查出内容
        if ($dr['dr_is_new'] == 0) {
            $dr['dr_content'] = $this->_hanle_admin_old_report_content($dr['dr_id']);
        } else {
            $dr['dr_content'] = $this->_hanle_admin_new_report_content($dr['dr_id']);
        }
        /* 查出发送轨迹 */
        //查出我自己的
        $t_dr_id = $dr['dr_from_dr_id'] == 0 ? $dr['dr_id'] : $dr['dr_from_dr_id'];
        $receivers = $this->getReceivers($t_dr_id);
        $copytos = $this->getCopyTos($t_dr_id);
        $locus = array();
        $locus[] = array('recipient' => $receivers, 'copytos' => $copytos);
        //如果是转发报告则显示转发轨迹
        if ($dr['dr_from_dr_id'] > 0) {
            //查出相关的
            if ($from = $this->_m->field('dr_id,dr_forword_uname,dr_created')->where(array('dr_id' => array('eq', $dr_id)))->find()) {
                $receivers = $this->getReceivers($from['dr_id']);
                $locus[] = array('recipient' => $receivers, 'copytos' => array(), 'forword_uname' => $from['dr_forword_uname'], 'dr_created' => $from['dr_created']);
            }
        }
        $dr['locus'] = $locus;
        return $dr;
    }

    private function _hanle_admin_old_report_content($dr_id) {
        //获取内容
        $where = array('dr_id' => array('eq', $dr_id), 'drp_first' => array('eq', 1));
        $drc = $this->_m->table('oa_dailyreport_post')->field('drp_message')->where($where)->find();
        //获取报告图片
        $drat_m = M('dailyreport_attachment');
        $imgs = $drat_m->field('at_id')->where(array('dr_id' => array('eq', $dr_id)))->select();
        $imgs = array_map(function($v) {
            $v['url'] = '/attachment/read/' . $v['at_id'];
            return $v;
        }, $imgs);
        $content = array(array(
                'type' => 'text',
                'title' => '报告内容',
                'content' => $drc['drp_message']
            ), array(
                'type' => 'img',
                'title' => '报告附件',
                'content' => $imgs
        ));
        return $content;
    }

    private function _hanle_admin_new_report_content($dr_id) {
        $drp_m = M('DailyreportPost');
        $drp_message = $drp_m->field('drp_new_message as drp_message')->where(array('drp_first' => array('eq', 1), 'dr_id' => $dr_id))->find();
        return json_decode($drp_message['drp_message'], true);
    }

    public function get_admin_report_comments($dr_id, $page) {
        $drp_m = M('dailyreport_post');
        $where['oa_dailyreport_post.dr_id'] = array('eq', $dr_id);
        $where['oa_dailyreport_post.drp_first'] = array('eq', 0);
        $where['oa_dailyreport_post.drp_deleted'] = array('eq', 0);
        $count = $drp_m->where($where)->count();
        if ($count <= 0) {
            E('_ERR_DAILYREPOR_COMMENT_NOT_FOUND_ERR');
        }
        $page_len = 15;
        $page_num = ceil($count / $page_len);
        $page = $page <= 0 ? 1 : ($page > $page_num ? $page_num : $page);
        $field = "oa_dailyreport_post.drp_created,oa_dailyreport_post.drp_id,oa_dailyreport_post.m_username,oa_dailyreport_post.drp_comment_content,oa_dailyreport_post.drp_message,oa_member.m_username as drp_comment_user,avatar.m_face";
        $comments = $drp_m->field($field)
                ->join('LEFT JOIN oa_member ON oa_dailyreport_post.drp_comment_user_id=oa_member.m_uid')
                ->join('LEFT JOIN oa_member AS avatar ON oa_dailyreport_post.m_uid=avatar.m_uid')
                ->where($where)
                ->page($page, $page_len)
                ->select();
        $result = array(
            'page' => $page,
            'pages' => $page_num,
            'limit' => $page_len,
            'count' => $count,
            'list' => $comments
        );
        return $result;
    }

    public function export_dr($param = array()) {
        $sql = "SELECT oa_dailyreport.dr_id,oa_dailyreport_tpl.drt_name,oa_dailyreport.m_username as submitter,oa_dailyreport.dr_forword_uname as forwarded, oa_dailyreport.dr_subject,oa_dailyreport.dr_created,
                      (SELECT GROUP_CONCAT(DISTINCT oa_common_department.cd_name)
                       FROM oa_common_department
                       INNER JOIN oa_member_department on oa_common_department.cd_id=oa_member_department.cd_id
                       WHERE oa_member_department.m_uid=oa_dailyreport.m_uid and oa_member_department.md_status<>3) as cd_name,
                    oa_dailyreport_post.drp_message,
                      (SELECT GROUP_CONCAT(CONCAT('http://{$_SERVER['HTTP_HOST']}','/attachment/read/',oa_dailyreport_attachment.at_id))
                       FROM oa_common_attachment
                       INNER JOIN oa_dailyreport_attachment on oa_dailyreport_attachment.at_id = oa_common_attachment.at_id
                       WHERE oa_common_attachment.at_mediatype = 1 and oa_dailyreport_attachment.dr_id = oa_dailyreport.dr_id) as at_ids
                FROM oa_dailyreport
                INNER JOIN oa_dailyreport_tpl on oa_dailyreport_tpl.drt_id = oa_dailyreport.dr_type
                INNER JOIN oa_dailyreport_post on oa_dailyreport_post.dr_id = oa_dailyreport.dr_id
                WHERE oa_dailyreport_post.drp_first=1 AND oa_dailyreport.dr_status <> 3";

        //拼装查询条件
        $sql_where = "";
        //部门
        if (array_key_exists("drt_type", $param) && $param["drt_type"] != "" && $param["drt_type"] != "0") {
            $sql_where .= " AND oa_dailyreport_tpl.drt_id = " . $param["drt_type"];
        }
        //提交人
        if (array_key_exists("submitter", $param) && $param["submitter"] != "") {
            $sql_where .= " AND oa_dailyreport.m_username LIKE '%" . $param["submitter"] . "%'";
        }
        //转发人
        if (array_key_exists("forwarded", $param) && $param["forwarded"] != "") {
            $sql_where .= " AND oa_dailyreport.dr_forword_uname LIKE '%" . $param["forwarded"] . "%'";
        }
        //提交时间-开始时间
        if (array_key_exists("start_date", $param) && $param["start_date"] != "") {
            $sql_where .= " AND oa_dailyreport.dr_reporttime >= " . $param["start_date"];
        }
        //提交时间-结束时间
        if (array_key_exists("end_date", $param) && $param["end_date"] != "") {
            $sql_where .= " AND oa_dailyreport.dr_reporttime <= " . $param["end_date"];
        }
        //接收人
        if (array_key_exists("receiver", $param) && $param["receiver"] != "") {
            $sql_where .= " AND EXISTS (SELECT * FROM oa_dailyreport_mem WHERE oa_dailyreport_mem.m_username LIKE '%" . $param['receiver'] . "%'
            AND oa_dailyreport_mem.drm_status <> 4 AND oa_dailyreport_mem.dr_id = oa_dailyreport.dr_id)";
        }

        $sql_limit = '';
        if (array_key_exists("start", $param)) {
            $sql_limit = " LIMIT " . $param["start"] . ',' . $param["limit"];
        }


        $sql = "{$sql} {$sql_where} {$sql_limit}";

        $list = $this->_m->query($sql);

        return $list;
    }

    public function export_count_dr($param = array()) {
        $sql = "SELECT oa_dailyreport.dr_id,oa_dailyreport_tpl.drt_name,oa_dailyreport.m_username as submitter, oa_dailyreport.dr_subject,oa_dailyreport.dr_created,
                      (SELECT GROUP_CONCAT(DISTINCT oa_common_department.cd_name)
                       FROM oa_common_department
                       INNER JOIN oa_member_department on oa_common_department.cd_id=oa_member_department.cd_id
                       WHERE oa_member_department.m_uid=oa_dailyreport.m_uid and oa_member_department.md_status<>3) as cd_name,
                    oa_dailyreport_post.drp_message,
                      (SELECT GROUP_CONCAT(oa_dailyreport_attachment.at_id)
                       FROM oa_common_attachment
                       INNER JOIN oa_dailyreport_attachment on oa_dailyreport_attachment.at_id = oa_common_attachment.at_id
                       WHERE oa_common_attachment.at_mediatype = 1 and oa_dailyreport_attachment.dr_id = oa_dailyreport.dr_id) as at_ids
                FROM oa_dailyreport
                INNER JOIN oa_dailyreport_tpl on oa_dailyreport_tpl.drt_id = oa_dailyreport.dr_type
                INNER JOIN oa_dailyreport_post on oa_dailyreport_post.dr_id = oa_dailyreport.dr_id
                WHERE oa_dailyreport_post.drp_first=1 AND oa_dailyreport.dr_status <>3";

        //拼装查询条件
        $sql_where = "";
        //部门
        if (array_key_exists("drt_type", $param) && $param["drt_type"] != "" && $param["drt_type"] != "0") {
            $sql_where .= " AND oa_dailyreport_tpl.drt_id = " . $param["drt_type"];
        }
        //提交人
        if (array_key_exists("submitter", $param) && $param["submitter"] != "") {
            $sql_where .= " AND oa_dailyreport.m_username LIKE '%" . $param["submitter"] . "%'";
        }
        //转发人
        if (array_key_exists("forwarded", $param) && $param["forwarded"] != "") {
            $sql_where .= " AND oa_dailyreport.dr_forword_uname LIKE '%" . $param["forwarded"] . "%'";
        }
        //提交时间-开始时间
        if (array_key_exists("start_date", $param) && $param["start_date"] != "") {
            $sql_where .= " AND oa_dailyreport.dr_reporttime >= " . $param["start_date"];
        }
        //提交时间-结束时间
        if (array_key_exists("end_date", $param) && $param["end_date"] != "") {
            $sql_where .= " AND oa_dailyreport.dr_reporttime <= " . $param["end_date"];
        }
        //接收人
        if (array_key_exists("receiver", $param) && $param["receiver"] != "") {
            $sql_where .= " AND EXISTS (SELECT * FROM oa_dailyreport_mem WHERE oa_dailyreport_mem.m_username LIKE '%" . $param['receiver'] . "%'
            AND oa_dailyreport_mem.drm_status <> 4 AND oa_dailyreport_mem.dr_id = oa_dailyreport.dr_id)";
        }
        if (array_key_exists("start", $param) && $param["start"] != "") {
            
        }

        $sql = "SELECT COUNT(*) FROM ({$sql} {$sql_where}) c";

        $count = $this->_m->result($sql);
        return $count;
    }

    public function save_api_dailyreport($post, $m_uid, $m_username) {
        $outdata = '';
        $imgs = array();
        //验证处理数据
        $this->_validation_save_api_data($post, $outdata, $imgs);
        //开始事务
        $this->start_trans();
        //保存报告
        $dailyreport = array(
            'm_uid' => $m_uid,
            'm_username' => $m_username,
            'dr_subject' => $post['drd_title'],
            'dr_type' => intval($post['drt_id']),
            'dr_reporttime' => strtotime(date('Y-m-d', time())),
            'dr_is_new' => 1
        );
        //保存报告
        if ($dr_id = $this->insert($dailyreport)) {
            $dailyreport_post = array(
                'dr_id' => $dr_id,
                'm_uid' => $m_uid,
                'm_username' => $m_username,
                'dr_subject' => $post['drd_title'],
                'drp_message' => $outdata,
                'drp_new_message' => json_encode($post['drt_module']),
                'drp_first' => 1,
                'drp_is_new' => 1,
                'drp_status' => 1,
                'drp_created' => NOW_TIME
            );
            $dp_post_m = M('DailyreportPost');
            //保存报告数据
            if ($drp_id = $dp_post_m->add($dailyreport_post)) {
                //保存接收人
                $mem_m = M('member');
                if ($dp_a_data = $mem_m->field('m_uid,m_username')->where(array('m_uid' => array('IN', $post['drd_a_uid'])))->select()) {
                    $dra_m = M('DailyreportMem');
                    $drread_m = M('DailyreportRead');
                    $dread_data = array();
                    $drr_ids = array();
                    foreach ($dp_a_data as $ak => $av) {
                        if (!in_array($av['m_uid'], $drr_ids)) {
                            $dread_data[] = array(
                                'm_uid' => $av['m_uid'],
                                'dr_id' => $dr_id,
                                'status' => 1,
                                'is_read' => 1,
                                'created' => NOW_TIME
                            );
                        }
                        $drr_ids[] = $av['m_uid'];
                        $dp_a_data[$ak]['dr_id'] = $dr_id;
                        $dp_a_data[$ak]['get_level'] = 1;
                        $dp_a_data[$ak]['drm_status'] = 1;
                        $dp_a_data[$ak]['drm_created'] = NOW_TIME;
                    }
                    if ($dra_m->addAll($dp_a_data)) {
                        //保存抄送人
                        if ($post['drd_cc_uid']) {
                            if ($dp_cc_data = $mem_m->field('m_uid,m_username')->where(array('m_uid' => array('IN', $post['drd_cc_uid'])))->select()) {
                                foreach ($dp_cc_data as $cck => $ccv) {
                                    if (!in_array($ccv['m_uid'], $drr_ids)) {
                                        $dread_data[] = array(
                                            'm_uid' => $ccv['m_uid'],
                                            'dr_id' => $dr_id,
                                            'status' => 1,
                                            'is_read' => 1,
                                            'created' => NOW_TIME
                                        );
                                    }
                                    $drr_ids[] = $ccv['m_uid'];
                                    $dp_cc_data[$cck]['dr_id'] = $dr_id;
                                    $dp_cc_data[$cck]['get_level'] = 0;
                                    $dp_cc_data[$cck]['drm_status'] = 1;
                                    $dp_cc_data[$cck]['drm_created'] = NOW_TIME;
                                }
                                if (!$dra_m->addAll($dp_cc_data)) {
                                    $this->rollback();
                                    E('_ERR_DAILYREPORT_CC_UID_SAVE_ERR');
                                    return false;
                                }
                            }
                        }
                        //保存图片
                        if (count($imgs) > 0) {
                            //处理图片并存储图片
                            foreach ($imgs as $imgk => $imgv) {
                                $imgs[$imgk]['m_uid'] = $m_uid;
                                $imgs[$imgk]['m_username'] = $m_username;
                                $imgs[$imgk]['dr_id'] = $dr_id;
                                $imgs[$imgk]['drp_id'] = $drp_id;
                                $imgs[$imgk]['drat_status'] = 1;
                                $imgs[$imgk]['drat_created'] = NOW_TIME;
                            }
                            $drat_m = M('DailyreportAttachment');
                            if (!$drat_m->addAll($imgs)) {
                                $this->rollback();
                                E('_ERR_DAILYREPORT_IMG_SAVE_ERR');
                                return false;
                            }
                        }
                    } else {
                        $this->rollback();
                        E('_ERR_DAILYREPORT_A_UID_SAVE_ERR');
                        return false;
                    }
                    //如果草稿存在则删除草稿
                    if (isset($post['drd_id'])) {
                        $drd_m = D('DailyreportDraftx');
                        if ($drd_m->delete($post['drd_id']) === false) {
                            $this->rollback();
                            E('_ERR_DRAFT_DEL_ERR');
                            return false;
                        }
                    }
                    $drread_m->addAll($dread_data);
                    $this->commit();
                    date_default_timezone_set('PRC');
                    $now = date("Y-m-d H:i:s");
                    $send_body = $post['drd_title'] . "\n时间:" . $now;
                    $cache = &\Common\Common\Cache::instance();
                    $setting = $cache->get('Common.setting');
                    $domain = C('PROTOCAL') . $setting['domain'];
                    $send_url = $domain . '/h5/index.html?#/app/page/dailyreport/dailyreport-detail?id=' . $dr_id . '&name=' . $post['drd_title'];
                    //推送给自己
                    $send_msg = "新建报告成功!";
                    \Common\Common\WxqyMsg::instance()
                            ->send_news($send_msg, $send_body, $send_url, $m_uid);
                    //推送消息到接收人及抄送人
                    $send_msg = "您收到一份报告";
                    $send_body = $post['drd_title'] . "\n来自:" . $m_username;
                    $send_uids = array_unique(array_merge(explode(',', $post['drd_a_uid']), explode(',', $post['drd_cc_uid'])));
                    \Common\Common\WxqyMsg::instance()
                            ->send_news($send_msg, $send_body, $send_url, $send_uids);
                    return array('dr_id' => $dr_id);
                } else {
                    $this->rollback();
                    E('_ERR_DAILYREPORT_A_UID_SAVE_ERR');
                    return false;
                }
            } else {
                $this->rollback();
                E('_ERR_DAILYREPORT_A_UID_SAVE_ERR');
                return false;
            }
        }
        $this->rollback();
        E('_ERR_DAILYREPORT_ADD_ERR');
        return false;
    }

    /**
     * 校验保存报告时的数据
     * @param type $post
     */
    private function _validation_save_api_data(&$post, &$outdata, &$imgs) {
        $drt_where = array(
            'drt_switch' => array('eq', 1),
            'drt_id' => array('eq', (int) $post['drt_id']),
            'drt_status' => array('neq', 3)
        );
        //验证模板是否已经被删除或者禁用
        $drt_m = M('DailyreportTpl');
        if ($drt_m->where($drt_where)->count() <= 0) {
            //抛出组件被禁用或删除
            E('_ERR_DAILYREPOR_TPL_NOT_FUND_ERR');
            return false;
        }
        //验证标题
		$post['drd_title']=trim($post['drd_title']," \t\r\n");
        $dr_title_len = mb_strlen($post['drd_title'], 'utf8');
        //标题不能为空
        if (!isset($post['drd_title']) || ($dr_title_len <= 0)) {
            E('_ERR_TITLE_NOT_NULL_ERR');
            return false;
        }
        //标题不能太长
        if ($dr_title_len > 25) {
            E('_ERR_TITLE_MAX_ERR');
            return false;
        }
        //验证输入的组件数量
        $dr_module_len = count($post['drt_module']);
        if ($dr_module_len > 15 || $dr_module_len <= 0) {
            //组件数量不正确
            E('_ERR_DAILYREPORTPL_MODULE_NUMBER_ERR');
            return false;
        }
        //验证接收人
        $post['drd_a_uid'] = trim($post['drd_a_uid'], ' \t\n\r');
        $post['drd_cc_uid'] = trim($post['drd_cc_uid'], ' \t\n\r');
        if (!preg_match('/^[\d|,]{1,}[\d]{0,}$/', $post['drd_a_uid'])) {
            //接收人不能为空
            E('_ERR_DAILYREPORT_A_UID_NOT_NULL_ERR');
            return false;
        }
        //验证抄送人
        if ($post['drd_cc_uid'] != '' && !preg_match('/^[\d|,]{1,}[\d]{0,}$/', $post['drd_cc_uid'])) {
            //抄送人数据输入错误
            E('_ERR_DAILYREPORT_CC_UID_ERR');
        }
        //验证每一个组件信息
        foreach ($post['drt_module'] as $mk => $mv) {
            if (isset($mv['type']) && in_array($mv['type'], $this->_tpl_types)) {
                //模板类型错误
                $vfuncname = '_validation_' . $mv['type'];
                $this->$vfuncname($mv);
                unset($mv['is_null']);
                $post['drt_module'][$mk] = $mv;
                //构建导出的数据
                if ($mv['type'] != self::TPL_TYPE_IMG) {
                    $outdata.=$mv['title'] . ':' . $mv['content'] . ';';
                } else {
                    $outdata.=$mv['title'] . ':见附件;';
                    if ($mv['is_null'] == 1 || ($mv['is_null'] == 0 && count($mv['content']) > 0)) {
                        foreach ($mv['content'] as $imgv) {
                            unset($imgv['url']);
                            $imgs[] = $imgv;
                        }
                    }
                }
                continue;
            }
            E('_ERR_DAILYREPORTPL_MODULE_TYPE_ERR');
            break;
        }
        return true;
    }

    private function _validation_date(&$module) {
        return $this->_validation_pub_date($module, 'Y-m-d');
    }

    private function _validation_time(&$module) {
        return $this->_validation_pub_date($module, 'H:i');
    }

    private function _validation_dateandtime(&$module) {
        return $this->_validation_pub_date($module, "Y-m-d H:i");
    }

    private function _validation_pub_date(&$module, $format) {
        $module['content'] = trim($module['content'], "\t\r\n");
        //1必填
        if ($module['is_null'] == 0 && !$module['content']) {
            return true;
        }
        if ($module['is_null'] == 1 && !$module['content']) {
            E('_ERR_NOT_NULL_ERR');
            return false;
        }
        $module['content'] = date($format, intval($module['content'] / 1000) + (60 * 60 * 8));
        return true;
    }

    private function _validation_textarea(&$module) {
        $module['content'] = trim($module['content'], " \t\r\n");
        //1必填
        if ($module['is_null'] == 0 && !$module['content']) {
            return true;
        }
        if ($module['is_null'] == 1 && !$module['content']) {
            E('_ERR_NOT_NULL_ERR');
            return false;
        }
        return false;
    }

    private function _validation_text(&$module) {
        $module['content'] = trim($module['content'], " \t\r\n");
        //1必填
        if ($module['is_null'] == 0 && !$module['content']) {
            return true;
        }
        if ($module['is_null'] == 1 && !$module['content']) {
            E('_ERR_NOT_NULL_ERR');
            return false;
        }
        return false;
    }

    private function _validation_number(&$module) {
        //1必填
        if ($module['is_null'] == 0 && $module['content'] == '') {
            return true;
        }
        if ($module['is_null'] == 1 && !$module['content']) {
            E('_ERR_NOT_NULL_ERR');
            return false;
        }
        if (!preg_match('/^[0-9.]{1,}$/', $module['content'])) {
            //数字输入不正确
            E('_ERR_INPUT_NUMBER_ERR');
            return true;
        }
        return false;
    }

    private function _validation_img(&$module) {
        //1必填
        if ($module['content'] && !is_array($module['content'])) {
            E('_ERR_INPUT_IMG_ERR');
            return false;
        }
        $img_len = is_array($module['content']) ? count($module['content']) : 0;
        if ($module['is_null'] == 0 && $img_len == 0) {
            return true;
        }
        if ($module['is_null'] == 1 && $img_len == 0) {
            E('_ERR_NOT_NULL_ERR');
            return false;
        }
        $cache = &\Common\Common\Cache::instance();
        $setting = $cache->get('Common.setting');
        $domain = C('PROTOCAL') . $setting['domain'];
        foreach ($module['content'] as $imgk => $imgv) {
            $imgv['at_id'] = intval($imgv['id']);
            if ($imgv['at_id'] <= 0) {
                E('_ERR_DAILYREPORT_MEDIAID_ERR');
                break;
            }
            //判断是否有url地址
            if (!$imgv['url']) {
                //拼接url
                $imgv['url'] = $domain . '/attachment/read/' . $imgv['at_id'];
            }
            unset($imgv['id'], $imgv['mediatype'], $imgv['isimage'], $imgv['$$hashKey'], $imgv['big'], $imgv['filesize'], $imgv['thumb'], $imgv['filename']);
            $module['content'][$imgk] = $imgv;
        }
        //保存图片
        return false;
    }

    private function _validation_radio(&$module) {
        $module['content'] = trim($module['content'], " \t\r\n");
        //1必填
        if ($module['is_null'] == 0 && !$module['content']) {
            return true;
        }
        if ($module['is_null'] == 1 && !$module['content']) {
            E('_ERR_NOT_NULL_ERR');
            return false;
        }
        //判断选项的个数
        $c_len = count(explode(',', $module['content']));
        if ($c_len != 1) {
            E('_ERR_DAILYREPORT_RADIO_CHK_ERR');
            return false;
        }
        foreach ($module['value'] as $v) {
            if ($v['value'] == $module['content']) {
                $module['content'] = $v['name'];
                break;
            }
        }
        unset($module['value']);
        return true;
    }

    private function _validation_checkbox(&$module) {
        $module['content'] = trim($module['content'], " \t\r\n");
        //1必填
        if ($module['is_null'] == 0 && !$module['content']) {
            return true;
        }
        if ($module['is_null'] == 1 && !$module['content']) {
            E('_ERR_NOT_NULL_ERR');
            return false;
        }
        //判断选项的个数
        $c_len = count(explode(',', $module['content']));
        if ($c_len <= 0) {
            E('_ERR_DAILYREPORT_RADIO_CHK_ERR');
            return false;
        }
        $contents = explode(',', $module['content']);
        $contents_str = array();
        foreach ($module['value'] as $v) {
            if (in_array($v['value'], $contents)) {
                $contents_str[] = $v['name'];
                continue;
            }
        }
        $module['content'] = implode(',', $contents_str);
        unset($module['value']);
        return true;
    }

    public function get_dailyreport_info_api($dr_id, $m_uid) {
        //校验基本条件
        if (!$dr_id) {
            E('_ERR_DAILYREPORT_NOT_FUND_ERR'); //报告不存在
            return false;
        }
        //查出基本资料
        $where = array(
            'dr_id' => array('eq', $dr_id),
            'dr_status' => array('neq', 3)
        );
        $field = 'dr_forword_uid,dr_forword_uname,dr_id,m_uid,m_username,dr_subject as dr_title,dr_type as drt_id,dr_created,dr_is_new,dr_from_dr_id';
        if (!$dp = $this->_m->field($field)->where($where)->find()) {
            E('_ERR_DAILYREPORT_NOT_FUND_ERR'); //报告不存在
            return false;
        }
        //查出接收人
        $drm_m = M('DailyreportMem');
        $where = array();
        $where['drm.dr_id'] = array('eq', $dp['dr_id']);
        $where['drm.get_level'] = array('eq', 1);
        $dr_a_uid = $drm_m
                ->field('drm.m_uid,om.m_face,om.m_username,drr.is_read')
                ->where($where)
                ->alias('drm')
                ->join('oa_member as om ON om.m_uid=drm.m_uid')
                ->join('oa_dailyreport_read as drr ON drr.m_uid=drm.m_uid AND drm.dr_id=drr.dr_id')
                ->order('drm.m_uid')
                ->select();
        $where['drm.get_level'] = array('eq', 0);
        //查出抄送人
        $dr_cc_uid = $drm_m
                ->field('drm.m_uid,om.m_face,om.m_username,drr.is_read')
                ->where($where)
                ->alias('drm')
                ->join('oa_member as om ON om.m_uid=drm.m_uid')
                ->join('oa_dailyreport_read as drr ON drr.m_uid=drm.m_uid AND drm.dr_id=drr.dr_id')
                ->select();
        //查出报告信息
        switch ($dp['dr_is_new']) {
            case 1:
                $dp_post = $this->get_dp_post_new_api($dp['dr_id']);
                break;
            case 0:
                $dp_post = $this->get_dp_post_old_api($dp['dr_id']);
                break;
            default :
                break;
        }
        $dp['dr_a_uid'] = $dr_a_uid;
        $dp['dr_cc_uid'] = $dr_cc_uid;
        $dp['dr_content'] = $dp_post;
        //查出头像
        $om_m = M('member');
        $m_face = $om_m->field('m_face')->where(array('m_uid' => array('eq', $dp['m_uid'])))->find();
        $dp['m_face'] = $m_face['m_face'];
        //记录阅读记录
        $drr_m = M('dailyreport_read');
        $drr_m->where(array('dr_id' => array('eq', $dp['dr_id']), 'm_uid' => array('eq', $m_uid)))->save(array('is_read' => 2, 'updated' => NOW_TIME));
        //查出是不是接收人
        $drm_m = M('DailyreportMem');
        $get_level = $drm_m->field('get_level')->where(array('dr_id' => array('eq', $dp['dr_id']), 'm_uid' => array('eq', $m_uid)))->find();
        $dp['get_level'] = intval($get_level['get_level']);
        return $dp;
    }

    private function get_dp_post_new_api($dr_id) {
        $drp_m = M('DailyreportPost');
        $drp_message = $drp_m->field('drp_new_message as drp_message')->where(array('drp_first' => array('eq', 1), 'dr_id' => $dr_id))->find();
        return json_decode($drp_message['drp_message'], true);
    }

    private function get_dp_post_old_api($dr_id) {
        $drp_m = M('DailyreportPost');
        //获取内容
        $where = array('dr_id' => array('eq', $dr_id), 'drp_first' => array('eq', 1));
        $drc = $drp_m->field('drp_message')->where($where)->find();
        //获取报告图片
        $drat_m = M('dailyreport_attachment');
        $imgs = $drat_m->field('at_id')->where(array('dr_id' => array('eq', $dr_id)))->select();
        $imgs = array_map(function($v) {
            $v['url'] = '/attachment/read/' . $v['at_id'];
            return $v;
        }, $imgs);
        $content = array(array(
                'type' => 'text',
                'title' => '报告内容',
                'content' => $drc['drp_message']
            ), array(
                'type' => 'img',
                'title' => '报告附件',
                'content' => $imgs
        ));
        return $content;
    }

    public function get_my_send_dailyreport_list($m_uid, $page, $q, $k, $drt_id) {
        $where = array(
            "dp." . $this->prefield . 'status' => array('neq', 3)
        );
        //转发的
        $borWhere['dp.dr_forword_uid'] = array('eq', $m_uid);
        $borWhere['_logic'] = 'OR';
        //自己发送的报告
        $aandWhere['dp.m_uid'] = array('eq', $m_uid);
        $aandWhere['dp.dr_forword_uid'] = array('eq', 0);
        $aandWhere['_logic'] = 'AND';
        $borWhere['_complex'] = $aandWhere;
        $where['_complex'] = $borWhere;
        if ($drt_id) {
            $where['dp.dr_type'] = array('eq', $drt_id);
        }
        switch ($q) {
            case 'd':
                $k = trim($k, " \t\n\r");
                if (preg_match('/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/', $k)) {
                    date_default_timezone_set('PRC');
                    $start_time = strtotime($k);
                    $stop_time = $start_time + 86399;
                    $where["dp.{$this->prefield}created"] = array(array('egt', $start_time), array('elt', $stop_time));
                } else {
                    E('_ERR_NOT_MORE_DAILYREPORT_ERR');
                }
                break;
            case 't':
                $k = trim($k, " \t\n\r");
                if ($k != '') {
                    $where["dp.{$this->prefield}subject"] = array('LIKE', "%{$k}%");
                } else {
                    E('_ERR_NOT_MORE_DAILYREPORT_ERR');
                }
                break;
            default :
                break;
        }
        $count = $this->_m->alias('dp')->where($where)->count();
        if ($count <= 0) {
            E('_ERR_NOT_MORE_DAILYREPORT_ERR');
        }
        $page_len = 15;
        $page_num = ceil($count / $page_len);
        $page = $page <= 0 ? 1 : ($page > $page_num ? $page_num : $page);
        $field = 'dp.dr_id,dp.dr_created,om.m_face,dp.dr_from_dr_id,dp.dr_subject as dr_title';
        $drafts = $this->_m
                ->alias('dp')
                ->field($field)
                ->join('oa_member as om ON om.m_uid=dp.m_uid')
                ->where($where)
                ->order('dp.dr_created DESC')
                ->page($page, $page_len)
                ->select();
        $result = array(
            'page' => $page,
            'pages' => $page_num,
            'limit' => $page_len,
            'count' => $count,
            'list' => $drafts
        );
        return $result;
    }

    /**
     * 获取与我相关及我负责的通用方法
     * @param type $m_uid
     * @param type $page
     * @param type $q
     * @param type $k
     * @param type $drt_id
     */
    private function get_responsibles_and_about_me($m_uid, $page, $q, $k, $drt_id, &$result, $get_level) {

        $where = array(
            "dp." . $this->prefield . 'status' => array('neq', 3),
            'drm.m_uid' => array('eq', $m_uid),
            'drm.get_level' => array('eq', $get_level)
        );
        if ($drt_id) {
            $where['dp.dr_type'] = array('eq', $drt_id);
        }
        switch ($q) {
            case 'd':
                $k = trim($k, " \t\n\r");
                if (preg_match('/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/', $k)) {
                    date_default_timezone_set('PRC');
                    $start_time = strtotime($k);
                    $stop_time = $start_time + 86399;
                    $where["dp.{$this->prefield}created"] = array(array('egt', $start_time), array('elt', $stop_time));
                } else {
                    E('_ERR_NOT_MORE_DAILYREPORT_ERR');
                }
                break;
            case 't':
                $k = trim($k, " \t\n\r");
                if ($k != '') {
                    $where["dp.{$this->prefield}subject"] = array('LIKE', "%{$k}%");
                } else {
                    E('_ERR_NOT_MORE_DAILYREPORT_ERR');
                }
                break;
            default :
                break;
        }
        $drm_m = M('DailyreportMem');
        $count = $drm_m
                ->alias('drm')
                ->where($where)
                ->join('oa_dailyreport AS dp ON drm.dr_id=dp.dr_id')
                ->count();
        if ($count <= 0) {
            E('_ERR_NOT_MORE_DAILYREPORT_ERR');
        }
        $page_len = 15;
        $page_num = ceil($count / $page_len);
        $page = $page <= 0 ? 1 : ($page > $page_num ? $page_num : $page);
        $field = 'dp.dr_id,dp.dr_created,dp.dr_from_dr_id,dp.dr_subject as dr_title,om.m_face,drr.is_read';
        $where['drr.m_uid'] = $m_uid;
        $dps = $drm_m
                ->alias('drm')
                ->field($field)
                ->where($where)
                ->join('oa_dailyreport AS dp ON drm.dr_id=dp.dr_id')
                ->join('LEFT JOIN oa_dailyreport_read AS drr ON drm.dr_id=drr.dr_id')
                ->join('oa_member AS om ON dp.m_uid=om.m_uid')
                ->order('dp.dr_created DESC')
                ->page($page, $page_len)
                ->select();
        $result = array(
            'page' => $page,
            'pages' => $page_num,
            'limit' => $page_len,
            'count' => $count,
            'list' => $dps
        );
        return $result;
    }

    /**
     * 我负责的
     * @param type $m_uid
     * @param type $page
     * @param type $q
     * @param type $k
     * @param type $drt_id
     * @return type
     */
    public function get_my_responsibles($m_uid, $page, $q, $k, $drt_id) {
        $result = array();
        $this->get_responsibles_and_about_me($m_uid, $page, $q, $k, $drt_id, $result, 1);
        return $result;
    }

    public function get_for_me($m_uid, $page, $q, $k, $drt_id) {
        $result = array();
        $this->get_responsibles_and_about_me($m_uid, $page, $q, $k, $drt_id, $result, 0);
        return $result;
    }

    public function get_past_api($m_uid, $page, $q, $k, $drt_id, $target_id) {
        //查看该用户给我发送的所有报告
        $where['drr.m_uid'] = array('eq', $m_uid);
        $where['dp.dr_status'] = array('neq', 3);
        //转发的
        $borWhere['dp.dr_forword_uid'] = array('eq', $target_id);
        $borWhere['_logic'] = 'OR';
        //自己发送的报告
        $aandWhere['dp.m_uid'] = array('eq', $target_id);
        $aandWhere['dp.dr_forword_uid'] = array('eq', 0);
        $aandWhere['_logic'] = 'AND';
        $borWhere['_complex'] = $aandWhere;
        $where['_complex'] = $borWhere;
        if ($drt_id) {
            $where['dp.dr_type'] = array('eq', $drt_id);
        }
        switch ($q) {
            case 'd':
                $k = trim($k, " \t\n\r");
                if (preg_match('/^[0-9]{4}-[0-9]{1,2}-[0-9]{1,2}$/', $k)) {
                    date_default_timezone_set('PRC');
                    $start_time = strtotime($k);
                    $stop_time = $start_time + 86399;
                    $where["dp.{$this->prefield}created"] = array(array('egt', $start_time), array('elt', $stop_time));
                } else {
                    E('_ERR_NOT_MORE_DAILYREPORT_ERR');
                }
                break;
            case 't':
                $k = trim($k, " \t\n\r");
                if ($k != '') {
                    $where["dp.{$this->prefield}subject"] = array('LIKE', "%{$k}%");
                } else {
                    E('_ERR_NOT_MORE_DAILYREPORT_ERR');
                }
                break;
            default :
                break;
        }
        $dr_read_m = M('DailyreportRead');
        $count = $dr_read_m
                ->alias('drr')
                ->where($where)
                ->join('oa_dailyreport AS dp ON drr.dr_id=dp.dr_id')
                ->count();
        if ($count <= 0) {
            E('_ERR_NOT_MORE_DAILYREPORT_ERR');
        }
        $page_len = 15;
        $page_num = ceil($count / $page_len);
        $page = $page <= 0 ? 1 : ($page > $page_num ? $page_num : $page);
        $field = 'dp.dr_id,dp.dr_created,dp.dr_from_dr_id,dp.dr_subject as dr_title,om.m_face,drr.is_read';
        $dps = $dr_read_m
                ->alias('drr')
                ->field($field)
                ->where($where)
                ->join('oa_dailyreport AS dp ON drr.dr_id=dp.dr_id')
                ->join('oa_member AS om ON dp.m_uid=om.m_uid')
                ->order('dp.dr_created DESC')
                ->page($page, $page_len)
                ->select();
        $result = array(
            'page' => $page,
            'pages' => $page_num,
            'limit' => $page_len,
            'count' => $count,
            'list' => $dps
        );
        return $result;
    }

    public function saveAtta($dr_id, $fdr_id,$fdrp_id,$fm_uid,$fm_username) {
        $dr_at_m = M('DailyreportAttachment');
        //查出来源的所有附件
        if($ats = $dr_at_m->field('at_id')->where(array('dr_id'=>array('eq',$dr_id)))->select()){
            //保存附件到转发报告
            foreach($ats as $k=>$v){
                $ats[$k]['dr_id']=$fdr_id;
                $ats[$k]['drp_id']=$fdrp_id;
                $ats[$k]['m_uid']=$fm_uid;
                $ats[$k]['m_username']=$fm_username;
                $ats[$k]['drat_created']=NOW_TIME;
            }
            if($dr_at_m->addAll($ats)){
                return false;
            }
            return true;
        }
        return false;
        //保存附件到转发报告
    }

}
