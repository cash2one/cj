<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/14
 * Time: 11:16
 */
namespace Score\Service;

use Think\Think;

class ScoreAwardService extends AbstractService {

    const AWARD_STATUS_USEABLE = 1; //奖品状态：可用
    const AWARD_STATUS_DISABEL = 0; //奖品状态：禁用

    const AWARD_EXCHANGE_STATUS_PROCESSING = 1; //奖品兑换处理中
    const AWARD_EXCHANGE_STATUS_AGREE      = 2; //奖品兑换已处理(同意)
    const AWARD_EXCHANGE_STATUS_REFUSE     = 3; //奖品兑换已处理(拒绝)

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 用户奖品兑换记录
     * @param int $uid
     * @param int $start
     * @param int $limit
     * @return array|mixed
     */
    public function getMemberAwardList($uid = 0, $start = 0, $limit = 10) {
        if (!$uid) return [];

        $sql = "SELECT A.create_time,A.status,A.score,B.m_username AS op_username,C.title,D.score AS last_score FROM
                oa_score_award_exchange AS A
                LEFT JOIN oa_member AS B ON A.op_uid=B.m_uid
                LEFT JOIN oa_score_award AS C ON C.award_id=A.award_id
                LEFT JOIN oa_score_value AS D ON A.m_uid=D.m_uid
                where A.m_uid=$uid order by A.create_time desc limit $start,$limit";
        $model = new \Think\Model();
        $list  = $model->query($sql);

        return $list;
    }

    /**
     * 用户奖品兑换记录总数
     * @param $uid
     * @return int
     */
    public function countMemberAwardList($uid) {
        if (!$uid) return 0;

        $count = M('ScoreAwardExchange')->where('m_uid='.$uid)->count();

        return $count;
    }

    /**
     * 获取奖品列表
     * @param int $start
     * @param int $limit
     * @return mixed
     */
    public function getAwardList($start = 0, $limit = 10, $status = 1, $uid = 0) {
        $model = new \Think\Model();
        if ($uid) {
            $sql = "SELECT award_id,status,title,`limit`,stock,cd_ids,uids,score,award_pic FROM oa_score_award where status=$status order by create_time desc";
            $list = $model->query($sql);
            //如果不在范围内
            foreach ($list as $k => $v) {
                $limits = explode(',',$v['uids']);
                if (!in_array($uid, $limits)) {
                    unset($list[$k]);
                }
            }

            $list = array_slice($list, $start, $limit);
        } else {
            $sql = "SELECT award_id,status,title,`limit`,stock,cd_ids,uids,score,award_pic FROM oa_score_award where status=$status order by create_time desc limit $start,$limit";

            $list = $model->query($sql);
        }
        //组装范围用户姓名和图片地址
        foreach ($list as $k => $v) {
            $list[$k]['cd_name'] = $this->splitCdAndUser($v['cd_ids'], $v['uids']);
            $count = M('ScoreAwardExchange')->where('award_id='.$v['award_id'])->count();
            $list[$k]['exchange_count'] = $count;

            $list[$k]['pic_urls'] = $this->awardPicUrls($v);
        }

        return $list;
    }

    /**
     * 获取奖品总数
     * @return mixed
     */
    public function getAwardCount($status = 1) {
        $map['status'] = $status;
        return M('ScoreAward')->where($map)->count();
    }

    /**
     * 新增一个奖品
     * @param $award_name
     * @param $limit
     * @param $score
     * @param $uids
     * @param $cd_ids
     * @param $award_pic
     * @param $desc
     * @return bool
     */
    public function addAward($award_name, $limit, $stock, $score, $uids, $cd_ids, $award_pic, $desc) {
        $data = array();
        $data['title']       = $award_name;
        $data['limit']       = $limit;
        $data['stock']       = $stock;
        $data['score']       = $score;
        $data['cd_ids']      = $cd_ids;
        $data['uids']        = $uids;
        $data['award_pic']   = json_encode($award_pic);
        $data['desc']        = $desc;
        $data['create_time'] = time();
        $data['status']      = self::AWARD_STATUS_USEABLE;
        $data['update_time'] = 0;

        $rs = M('ScoreAward')->add($data);
        if ($rs) return true;
    }

    /**
     * 编辑一个奖品
     * @param $award_id
     * @param $award_name
     * @param $limit
     * @param $score
     * @param $uids
     * @param $cd_ids
     * @param $award_pic
     * @param $desc
     * @return bool
     */
    public function editAward($award_id, $award_name, $limit, $stock, $score, $uids, $cd_ids, $award_pic, $desc) {
        $data = array();
        $data['title']       = $award_name;
        $data['limit']       = $limit;
        $data['stock']       = $stock;
        $data['score']       = $score;
        $data['cd_ids']      = $cd_ids;
        $data['uids']        = $uids;
        $data['award_pic']   = json_encode($award_pic);
        $data['desc']        = $desc;
        $data['create_time'] = time();
        $data['status']      = self::AWARD_STATUS_USEABLE;
        $data['update_time'] = 0;

        $map['award_id'] = $award_id;
        $rs = M('ScoreAward')->where($map)->save($data);
        if ($rs) return true;
    }

    /**
     * 获取奖品兑换列表
     * @param int $start_date
     * @param int $end_date
     * @param int $status
     * @param int $orderNum
     * @param int $start
     * @param int $limit
     * @return mixed
     */
    public function getAwardExchangeLogs($start_date = 0, $end_date = 0, $status = self::AWARD_EXCHANGE_STATUS_PROCESSING, $orderNum = 0, $start = 0, $limit =10) {
        $map = array();
        if ($start_date && $end_date) $map['A.create_time'] = array('between', "$start_date,$end_date");
        if ($status) {
            if ($status == self::AWARD_EXCHANGE_STATUS_AGREE) { //如果是已处理的状态 查询条件为status>=2
                $map['A.status'] = array('gt', 1);
            } else {
                $map['A.status'] = $status;
            }
        }
        if ($orderNum) $map['A.order_number'] = array('like', '%'.$orderNum.'%');

        $list = M()->table('oa_score_award_exchange AS A')
                   ->field('A.order_id,A.order_number,A.award_num,A.score,A.create_time,A.status,A.member_info, C.title AS award_title')
                   ->join('LEFT JOIN oa_score_award AS C on A.award_id=C.award_id')
                   ->where($map)->limit("$start,$limit")->order('A.create_time desc')
                   ->select();
        //解析申请信息
        foreach ($list as $k => $v) {
            $list[$k]['member_info'] = json_decode($v['member_info'], true);
            $list[$k]['m_username'] = $list[$k]['member_info']['u_name'];
            $list[$k]['m_mobilephone'] = $list[$k]['member_info']['u_phone'];
            $list[$k]['m_email'] = $list[$k]['member_info']['u_email'];
            $list[$k]['m_desc'] = $list[$k]['member_info']['u_desc'];
        }

        return $list;
    }

    /**
     * 兑换记录数量
     */
    public function awardExchangeLogCount($start_date = 0, $end_date = 0, $status = self::AWARD_EXCHANGE_STATUS_PROCESSING, $orderNum = 0) {
        $map = array();
        if ($start_date && $end_date) $map['A.create_time'] = array('between', "$start_date,$end_date");
        if ($status)   $map['A.status'] = $status;
        if ($orderNum) $map['A.order_number'] = array('like', '%'.$orderNum.'%');

        $count = M()->table('oa_score_award_exchange AS A')
            ->join('LEFT JOIN oa_member AS B on A.m_uid=B.m_uid')
            ->join('LEFT JOIN oa_score_award AS C on A.award_id=C.award_id')
            ->where($map)
            ->count();

        return $count;
    }

    /**
     * 获取奖品信息
     * @param int $award_id
     * @return array|mixed
     */
    public function getAwardDetail($award_id = 0) {
        if (!$award_id) return [];

        $award = M('ScoreAward')->find($award_id);

        $award['uids']   = explode(',', $award['uids']);
        $award['cd_ids'] = explode(',', $award['cd_ids']);
        $uids = [];
        //处理奖品范围用户 转化为姓名数组
        foreach ($award['uids'] as $key => $value) {
            $map['m_uid'] = $value;
            $info = M('Member')->where($map)->field('m_uid,m_username')->find();
            $uids[] = ['m_uid' => $info['m_uid'], 'm_username' => $info['m_username']];
        }
        $award['uids'] = $uids;
        $award['desc'] = htmlspecialchars_decode($award['desc']);

        //处理奖品图片
        $award['pic_urls'] = $this->awardPicUrls($award);
        $award['award_pic'] = json_decode($award['award_pic'], true);
        
        return $award;
    }

    /**
     * 奖品兑换详情
     * @param int $order_id
     * @return array|mixed
     */
    public function getAwardOrderDetail($order_id = 0) {
        if (!$order_id) return [];
        $map['A.order_id'] = $order_id;
        $result = M()->table('oa_score_award_exchange AS A')
                     ->field('A.order_id,A.order_number,A.create_time,A.status,A.award_num,A.refuse_reason,A.member_info,B.award_id,B.title,B.award_pic,B.score')
                     ->join('LEFT JOIN oa_score_award AS B on A.award_id=B.award_id')
                     ->join('LEFT JOIN oa_member AS C on A.m_uid=C.m_uid')
                     ->where($map)
                     ->find();

        $result['member_info'] = json_decode($result['member_info'], true);
        $result['total_score'] = $result['score'] * $result['award_num']; //计算积分总消耗
        //组装奖品图片地址
        $result['pic_url'] = $this->awardPicUrls($result);
        return $result;
    }

    /**
     * 处理兑换申请
     * @param int $order_id
     * @param int $status
     * @param string $reason
     * @return bool
     */
    public function processAwardOrder($order_id = 0 ,$status = self::AWARD_EXCHANGE_STATUS_AGREE, $reason = '') {
        if (!$order_id) return false;
        $map['order_id'] = $order_id;
        $order = M('ScoreAwardExchange')->find($order_id);
        if (!$order) return false;

        $award = D('ScoreAward')->find($order['award_id']);
        $memberScore = M('ScoreValue')->where(array('m_uid'=>$order['m_uid']))->find();

        if ($memberScore['score'] < $award['score']) return false; //如果成员兑换积分不够 不允许兑换
        //如果同意兑换 则减少一个奖品库存
        if ($status == self::AWARD_EXCHANGE_STATUS_AGREE) {
            if (!$award) return false;
            if ($award['stock'] > 0) {
                $aData['stock']   = $award['stock'] -1;
                $aMap['award_id'] = $award['award_id'];
                M('ScoreAward')->where($aMap)->save($aData);
            }
        }

        $data['status']        = $status;
        $data['refuse_reason'] = $reason;

        $rs = M('ScoreAwardExchange')->where($map)->save($data);

        return $rs;
    }

    /**
     * 变更奖品状态
     * @param int $award_id
     * @param int $status
     * @return bool
     */
    public function changeAwardStatus($award_id = 0, $status = self::AWARD_STATUS_USEABLE) {
        if (!$award_id) return true;
        $map['award_id'] = $award_id;

        $data['status'] = $status;
        $rs = M('ScoreAward')->where($map)->save($data);

        return $rs;
    }

    /**
     *
     * @param int $award_id
     * @param int $uid
     * @param string $applyName
     * @param string $applyPhone
     * @param string $applyEmail
     * @param string $applyDesc
     * @return bool
     */
    public function applyExchange($award_id = 0, $uid = 0, $applyName = '', $applyPhone = '', $applyEmail = '', $applyDesc = '') {
        $award = M('ScoreAward')->find($award_id);
        if (!$award || $award['status'] == self::AWARD_STATUS_DISABEL)  E('_ERROR_AWARD_NOT_EXIST');
        if ($award['stock'] <= 0)                           E('_ERROR_AWARD_RUN_OUT');
        if (!in_array($uid, explode(',', $award['uids']))) E('_ERROR_AWARD_EXCHANGE_NO_AUTH');

        //检查奖品限制
        if ($award['limit'] != 0) {
            $eMap['m_uid']    = $uid;
            $eMap['award_id'] = $award_id;
            $eMap['status']   = self::AWARD_EXCHANGE_STATUS_AGREE;
            $count = M('ScoreAwardExchange')->where($eMap)->count();
            if ($count > $award['limit']) E('_ERROR_AWARD_LIMIT_OUT');
        }
        //检查用户积分是否满足 满足则扣除积分
        $userScore = D('Score/Score', 'Service')->getUserScore($uid);
        if ($userScore < $award['score']) E('_ERROR_USER_SCORE_NOT_ENOUGH');
        $finalScore = $userScore - $award['score'];
        $change = D('Score/Score', 'Service')->changeUserScore($uid, $finalScore);

        if (!$change) E('_ERROR_USER_SCORE_NOT_ENOUGH');

        $info = [
            'm_name'  => $applyName,
            'm_phone' => $applyPhone,
            'm_email' => $applyEmail,
            'm_desc'  => $applyDesc,
        ];
        $order = [];
        $order['order_number']  = date('Ymd',time()).substr(time(),6,5);
        $order['award_id']      = $award_id;
        $order['m_uid']         = $uid;
        $order['op_uid']        = 0;
        $order['award_num']     = 1;
        $order['score']         = $award['score'];
        $order['member_info']   = json_encode($info);
        $order['refuse_reason'] = '';
        $order['status']        = 1;
        $order['create_time']   = 0;

        $newOrder = M('ScoreAwardExchange')->add($order);

        if ($newOrder) return true;
        return false;
    }

    /**
     * 组装奖品图片地址
     * @param array $award
     * @return array
     */
    protected function awardPicUrls($award = array()) {
        $pics = [];
        if ($award['award_pic']) {
            $award['award_pic'] = json_decode($award[award_pic], true);

            foreach ($award['award_pic'] as $k => $v) {
                $cache = &\Common\Common\Cache::instance();
                $setting = $cache->get('Common.setting');
                $domain = C('PROTOCAL') . $setting['domain'];
                $picurl = $domain . '/attachment/read/' . $v;
                $pics[] = ['url' => $picurl, 'id' => $v];
            }
        }
        return $pics;
    }

    /**
     * 根据cd_ids uids 获取用户名称，部门名称
     * @param $cd_ids
     * @param $uids
     * @return string
     */
    protected function splitCdAndUser($cd_ids, $uids) {
        $result = array();
        foreach (explode(',',$cd_ids) as $key => $value) {
            $cd = M('CommonDepartment')->find($value);
            if ($cd) $result[] = $cd['cd_name'];
        }

        foreach (explode(',',$uids) as $k => $v) {
            $member = M('Member')->find($v);
            if ($member) $result[] = $member['m_username'];
        }

        return implode(',', $result);
    }
}