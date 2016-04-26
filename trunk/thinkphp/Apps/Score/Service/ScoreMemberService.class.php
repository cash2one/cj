<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/13
 * Time: 11:29
 */

namespace Score\Service;

use Think\Think;

class ScoreMemberService extends AbstractService {

    public function __construct()
    {
        parent::__construct();
        $this->_d = D('Score/ScoreValue');
    }

    /**
     * 获取成员列表
     * @param string $name
     * @param string $cd_id
     * @param int $page
     * @param int $limit
     * @return mixed
     */
    public function getMemberScoreList($name = '', $cd_name = '',$offset = 0, $limit = 10, $order = 0) {
        $whereCondition = '';
        if ($name) {
            $whereCondition = "WHERE A.m_username LIKE '%".$name."%'";
        }
        if ($cd_name && $name) {
            $whereCondition .= " AND B.cd_name LIKE '%".$cd_name."%'";
        } else if (!$name && $cd_name) {
            $whereCondition = "WHERE B.cd_name LIKE '%".$cd_name."%'";
        }

        $order_sql = '';
        if ($order == 0) $order_sql = ' desc';
        $sql = "SELECT A.m_uid,A.m_username,A.cd_id,B.cd_name,C.score FROM
                oa_member AS A LEFT JOIN oa_common_department AS B on A.cd_id=B.cd_id
                LEFT JOIN oa_score_value AS C ON A.m_uid=C.m_uid ".$whereCondition." order by C.score".$order_sql." limit $offset,$limit";

        $model = new \Think\Model();
        $list = $model->query($sql);
        //获取奖品兑换记录次数
        foreach ($list as $k => $v) {
            $sql = "SELECT COUNT(*) FROM oa_score_award_exchange where m_uid=".$v['m_uid'];
            $count = $model->query($sql);

            $list[$k]['exchange_count'] = $count[0]['count(*)'];
            if (!$v['score']) {
                $list[$k]['score'] = 0;
            }
        }

        return $list;
    }

    /**
     * 成员总数
     * @param string $name
     * @param string $cd_name
     * @return mixed
     */
    public function countMemberList($name = '', $cd_name = '')
    {
        $whereCondition = '';
        if ($name) {
            $whereCondition = "WHERE A.m_username LIKE '%" . $name . "%'";
        }
        if ($cd_name && $name) {
            $whereCondition .= " AND B.cd_name LIKE '%" . $cd_name . "%'";
        } else if (!$name && $cd_name) {
            $whereCondition = "WHERE B.cd_name LIKE '%" . $cd_name . "%'";
        }
        $sql = "SELECT count(*) FROM
                oa_member AS A LEFT JOIN oa_common_department AS B on A.cd_id=B.cd_id
                LEFT JOIN oa_score_value AS C ON A.m_uid=C.m_uid " . $whereCondition;
        $model = new \Think\Model();
        $count = $model->query($sql);

        return $count[0]['count(*)'];
    }

    /**
     * 成员详细信息
     * @param $uid
     * @return mixed
     */
    public function getMemberDetail($uid) {
        if (!$uid) return '';
        $sql = "SELECT A.m_uid,A.m_username,A.m_mobilephone,A.m_email,A.m_weixin,B.cd_name,C.score FROM oa_member AS A
                LEFT JOIN oa_common_department AS B on A.cd_id=B.cd_id
                LEFT JOIN oa_score_value AS C on A.m_uid=C.m_uid WHERE A.m_uid=".$uid;

        $model  = new \Think\Model();
        $detail = $model->query($sql);
        $detail = $detail[0];
        if (!$detail['score']) {
            $detail['score'] = 0;
        }
        return $detail;
    }

    public function getMemberScoreLogList($uid = 0, $start = 0, $page = 10) {
        if (!$uid) return [];
        $sql = "SELECT A.create_time,A.rule_id,A.app_type,A.desc,A.num,A.order_id,B.m_username AS op_username FROM
                oa_score_log AS A LEFT JOIN oa_member AS B ON A.op_uid=B.m_uid WHERE A.m_uid=".$uid." order by A.create_time desc limit $start,$page";

        $model  = new \Think\Model();
        $list = $model->query($sql);

        foreach ($list as $k => $v) {
            $scoreS = D('Score/Score', 'Service');
            if ($v['rule_id']) {
                $rule = M('ScoreRule')->find($v['rule_id']);
                $list[$k]['reason'] = $scoreS->scoreChangeAppType[$rule['app_type']] . ' ' . $rule['title'];
            } else {
                $list[$k]['reason'] = $scoreS->scoreChangeAppType[$v['app_type']];
            }
        }

        return $list;
    }

    /**
     * @param int $uid
     * @return array
     */
    public function countMemberScoreLogList($uid = 0) {
        if (!$uid) return 0;
        $count = M('ScoreLog')->where('m_uid='.$uid)->count();
        return $count;
    }
}