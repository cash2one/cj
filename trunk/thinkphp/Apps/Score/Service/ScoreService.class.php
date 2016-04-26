<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/13
 * Time: 18:54
 */

namespace Score\Service;

class ScoreService extends AbstractService {

    const SCORE_RULE_STATUS_START  = 1; //积分规则状态：可用
    const SCORE_RULE_STATUS_FORBID = 0; //积分规则状态：禁用

    const SCORE_CHANGE_APP_TYPE_SHARE         = 1;
    const SCORE_CHANGE_APP_TYPE_ACTIVE        = 2;
    const SCORE_CHANGE_APP_TYPE_VOTE          = 3;
    const SCORE_CHANGE_APP_TYPE_COURSE        = 4;
    const SCORE_CHANGE_APP_TYPE_SCHOOL        = 5;
    const SCORE_CHANGE_APP_TYPE_CAMPAIGNS     = 6;
    const SCORE_CHANGE_APP_TYPE_MANUAL        = 7;
    const SCORE_CHANGE_APP_TYPE_AWARD_EXCHANGE= 8;
    const SCORE_CHANGE_APP_TYPE_AWARD_FAILED  = 9;

    public $scoreChangeAppType = array(
        self::SCORE_CHANGE_APP_TYPE_ACTIVE    => '创创活动',
        self::SCORE_CHANGE_APP_TYPE_SHARE     => '创友分享',
        self::SCORE_CHANGE_APP_TYPE_VOTE      => '投票调研',
        self::SCORE_CHANGE_APP_TYPE_COURSE    => '课程查询',
        self::SCORE_CHANGE_APP_TYPE_SCHOOL    => '校园动态',
        self::SCORE_CHANGE_APP_TYPE_CAMPAIGNS => '创友推广',
        self::SCORE_CHANGE_APP_TYPE_MANUAL    => '手动修改',
        self::SCORE_CHANGE_APP_TYPE_AWARD_EXCHANGE => '兑换奖品',
        self::SCORE_CHANGE_APP_TYPE_AWARD_FAILED   => '奖品兑换失败',
    );

    public function initUserScore() {
        $users = M('Member')->select();
        foreach ($users as $v) {
            $data = ['m_uid'=>$v['m_uid'], 'score' => 0];
            M('ScoreValue')->add($data);
        }
    }

    public function changeSwitch($switch = true) {
        $value = $switch ? 'true' : 'false';

        $map['key'] = 'switch';
        M('ScoreConfig')->where($map)->setField('value', $value);

        return ture;
    }

    /**
     * 增加一条积分变化日志
     * @param int $m_uid 用户ID
     * @param int $op_uid 操作用户ID
     * @param int $num 分数变化
     * @param int $rule_id 规则ID
     * @param string $desc 描述
     * @param int $app_type app_type
     * @return bool
     */
    public function scoreChange($m_uid = 0, $op_uid = 0, $num = 0, $rule_id = 0, $desc = '', $app_type = self::SCORE_CHANGE_APP_TYPE_MANUAL) {
        $data = compact('m_uid', 'op_uid', 'num', 'rule_id', 'desc', 'app_type');
        $data['create_time'] = time();

        M('ScoreLog')->add($data);
        return true;
    }

    /**
     * 用户积分变化操作
     * @param int $uid
     * @param int $rule_id
     * @return bool
     */
    public function scoreRuleChange($uid = 0, $rule_id = 0) {
        if (!$this->checkConfig()) return true; //积分功能关闭 不执行操作
        $rule = M('ScoreRule')->find($rule_id);
        if ($rule) {
            $score = $rule['score'];
            $desc  = $rule['title'];
        }
        //检查规则限制
        if (!$this->checkRuleLimit($uid, $rule_id)) {
            return false;
        }
        //修改用户积分
        if($this->changeUserScore($uid, $score)) {
            $this->scoreChange($uid, 0, $score, $rule_id, $desc, $rule['app_type']);
            return true;
        }

        return false;
    }

    /**
     * 检查积分规则限制
     * @param int $uid
     * @param int $rule_id
     * @return bool
     */
    public function checkRuleLimit($uid = 0, $rule_id = 0) {
        $rule = M('ScoreRule')->find($rule_id);
        if (!$rule) return false;

        if ($rule['loop'] == 1 || $rule['limit'] == 0) return true;

        $timeArea = $this->getTimeArea($rule['loop']);
        $start = $timeArea['start'];
        $end   = $timeArea['end'];
        $map['create_time'] = array('between', "$start, $end");
        $map['m_uid'] = $uid;

        $count = M('ScoreLog')->where($map)->count();

        return $count <= $rule['limit'] ? true : false;
    }

    /**
     * 修改用户积分
     * @param $uid
     * @param $score
     * @return bool
     */
    public function changeUserScore($uid, $score) {
        $map['m_uid'] = $uid;
        $value = M('ScoreValue')->where($map)->find();
        $changeScore = $value['score'] + $score;

        $rs    = M('ScoreValue')->where($map)->setField('score', $changeScore);
        return $rs;
    }

    /**
     * 检查积分功能是否开启
     * @return mixed
     */
    public function checkConfig() {
        $config = M('ScoreConfig')->find('switch');

        return $config['value'];
    }

    /**
     * 批量修改用户积分
     * @param array $uidArr
     * @param int $op_uid
     * @param int $score
     * @return bool
     */
    public function updateScores($uidArr = array(),$op_uid = 0, $score = 0, $desc = '') {
        if (!$uidArr || !is_array($uidArr)) return true;

        foreach ($uidArr as $k => $v) {
            $value = M('ScoreValue')->where('m_uid='.$v)->find();

            if ($value['score'] < -$score) {
                $value = 0;
            } else {
                $value = $value['score'] + $score;
            }
            $map['m_uid'] = $v;
            $rs = M('ScoreValue')->where($map)->setField('score', $value);
            if ($rs) {
                $this->scoreChange($v, $op_uid, $score, 0, $desc);
            }
        }

        return true;
    }

    /**
     * 获取积分规则列表
     * @param int $app_type
     * @return mixed
     */
    public function getRuleList($app_type = 0) {
        $whereCondition = '';
        if ($app_type) {
            $whereCondition = 'WHERE app_type='.$app_type;
        }
        $sql = "SELECT * FROM oa_score_rule ".$whereCondition;
        $model = new \Think\Model();
        $list = $model->query($sql);

        foreach ($list as $k => $v) {
            $list[$k]['app_name'] = $this->scoreChangeAppType[$v['app_type']];
        }

        return $list;
    }

    /**
     * 修改积分规则
     * @param array $datas
     * @return bool
     */
    public function updateRuleList($datas = array()) {
        foreach ($datas as $k => $v) {
            $model = M('ScoreRule');
            $rule = $model->find($v['rule_id']);
            if ($rule) {
                $rule['loop']  = $v['loop'];
                $rule['limit'] = $v['limit'];
                $rule['score'] = $v['score'];
                $model->save($rule);
            }
        }
        return true;
    }

    /**
     * 修改积分规则状态
     * @param int $rule_id
     * @param int $status
     * @return bool
     */
    public function changeRuleStatus($rule_id = 0, $status = self::SCORE_RULE_STATUS_START) {
        if (!$rule_id) return true;

        $map['rule_id'] = $rule_id;
        $data['status'] = $status;

        $rs = M('ScoreRule')->where($map)->save($data);

        return $rs;
    }

    /**
     * 积分变化记录列表
     * @param string $name
     * @param string $cd_name
     * @param int $type
     * @param int $start
     * @param int $limit
     * @return mixed
     */
    public function scoreLogList($name = '', $cd_name = '', $type = 0, $start = 0, $limit = 10) {
        $map = array();
        if ($name) $map['B.m_username'] = array('like', '%'.$name.'%');
        if ($cd_name) $map['D.cd_name'] = array('like', '%'.$cd_name.'%');
        if ($type) {
            if ($type == 1) $map['A.num'] = array('egt', 0);
            if ($type == 2) $map['A.num'] = array('lt', 0);
        }
        $list = M()->table('oa_score_log AS A')
                ->field('A.log_id,A.m_uid,A.op_uid,A.num,A.desc,A.create_time,B.m_username,C.m_username as op_username,D.cd_name')
                ->join('LEFT JOIN oa_member AS B on A.m_uid=B.m_uid')
                ->join('LEFT JOIN oa_common_department AS D on D.cd_id=B.cd_id')
                ->join('LEFT JOIN oa_member AS C on A.op_uid=C.m_uid')
                ->where($map)->limit("$start,$limit")->order('A.create_time desc')
                ->select();

        foreach ($list as $k => $v) {
            $v['num'] >=0 ? $list[$k]['type'] = 1 : $list[$k]['type'] = 2;
        }

        return $list;
    }

    /**
     * 积分变化记录总条数
     * @param string $name
     * @param string $cd_name
     * @param int $type
     * @return mixed
     */
    public function countScoreLogList($name = '', $cd_name = '', $type = 0) {
        $map = array();
        if ($name) $map['B.m_username'] = array('like', '%'.$name.'%');
        if ($cd_name) $map['D.cd_name'] = array('like', '%'.$cd_name.'%');
        if ($type) {
            if ($type == 1) $map['A.num'] = array('egt', 0);
            if ($type == 2) $map['A.num'] = array('lt', 0);
        }
        $count = M()->table('oa_score_log AS A')
            ->field('A.log_id,A.m_uid,A.op_uid,A.num,A.desc,A.create_time,B.m_username,C.m_username as op_username,D.cd_name')
            ->join('LEFT JOIN oa_member AS B on A.m_uid=B.m_uid')
            ->join('LEFT JOIN oa_common_department AS D on D.cd_id=B.cd_id')
            ->join('LEFT JOIN oa_member AS C on A.op_uid=C.m_uid')
            ->where($map)
            ->count();

        return $count;
    }

    /**
     * 获取用户当前积分
     * @param int $uid
     * @return int
     */
    public function getUserScore($uid = 0) {
        if (!$uid) return 0;
        $map['m_uid'] = $uid;
        $result = M('ScoreValue')->where($map)->field('score')->find();

        return $result['score'];
    }

    /**
     * 填充积分记录列表
     * @param array $list
     */
    public function processScoreLogList(&$list = []) {
        foreach ($list as $k => $v) {
            $list[$k]['app_name'] = $this->scoreChangeAppType[$v['app_type']];

            if ($v['app_type'] == self::SCORE_CHANGE_APP_TYPE_MANUAL) {
                $list[$k]['app_reason'] = $v['desc'];
            }

            if ($v['app_type'] == self::SCORE_CHANGE_APP_TYPE_AWARD_EXCHANGE || $v['app_type'] == self::SCORE_CHANGE_APP_TYPE_AWARD_FAILED) {
                $order = M('ScoreAwardExchange')->find($v['order_id']);

                $list[$k]['order_number'] = $order['order_number'];
                $list[$k]['order_status'] = $order['status'];
            }
        }
    }

    /**
     * 获取用户积分排名
     * @param int $uid
     * @return int
     */
    public function getUserRank($uid = 0) {
        if (!$uid) return 0;
        $map['m_uid'] = $uid;
        $curScore = M('ScoreValue')->where($map)->find();

        $rankMap['score'] = array('egt', $curScore['score']);
        $rank = M('ScoreValue')->where($rankMap)->count();

        return $rank;
    }

    /**
     *
     * @param int $type
     * @return array
     */
    protected function getTimeArea($type = 2) {
        switch ($type) {
            case 2: //每天
                $year = date("Y");
                $month = date("m");
                $day = date("d");
                $startTime = mktime(0,0,0,$month,$day,$year);//当天开始时间戳
                $endTime   = mktime(23,59,59,$month,$day,$year);//当天结束时间戳
                break;
            case 3: //每周
                $sdefaultDate = date("Y-m-d");
                $first=1;
                $w=date('w',strtotime($sdefaultDate));
                $startTime=date('Y-m-d',strtotime("$sdefaultDate -".($w ? $w - $first : 6).' days'));
                $endTime=date('Y-m-d',strtotime("$startTime +6 days"));
                break;
            case 4: //每月
                $startTime=mktime(0,0,0,date('m'),1,date('Y'));
                $endTime=mktime(23,59,59,date('m'),date('t'),date('Y'));
                break;
            case 5: //每年
                $startTime = mktime(0,0,0,1,1,date("Y",time()));
                $endTime   = mktime(23,59,59,12,31,date("Y",time()));;
        }
        return ['start'=>$startTime, 'end'=>$endTime];
    }
}