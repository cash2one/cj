<?php
/**
 * Created by PhpStorm.
 * User: zhang mi
 * Date: 2016/4/18
 * Time: 12:09
 */

namespace  Score\Controller\Api;
use Common\Common\Pager;
use Common\Common\Cache;

class ScoreAwardController extends AbstractController
{

    //构造方法
    public function __construct()
    {
        parent::__construct();
    }

    public function exchangeOrderDetail() {
        $order_id = I('get.order_id', 0);

        $service = D('Score/ScoreAward', 'Service');
        $detail  = $service->getAwardOrderDetail($order_id);

        $this->_result = ['detail' => $detail];
    }

    public function awardList() {
        $page    = I('get.page', 0, 'intval');
        $limit   = 6;

        $service = D('Score/ScoreAward', 'Service');
        //分页属性
        list($start, $limit, $page) = page_limit($page, $limit);
        //总数量
        $list  = $service->getAwardList($start, $limit, 1, $this->_login->user['m_uid']);
        $total = $service->getAwardCount();

        //输出
        $this->_result = array(
            'list'     => $list,
            'cur_page' => $page,
            'page'     => ceil($total/$limit),
            'limit'    => $limit,
            'count'    => $total,
        );
    }

    public function awardDetail() {
        $award_id = I('get.award_id', 0);

        $awardDetail = D('Score/ScoreAward', 'Service')->getAwardDetail($award_id);
        $cur_score   = D('Score/Score', 'Service')->getUserScore($this->_login->user['m_uid']);
        unset($awardDetail['cd_name'], $awardDetail['award_pic'], $awardDetail['cd_ids'], $awardDetail['create_time'],$awardDetail['update_time'],$awardDetail['uids']);
        $this->_result = [
            'award_detail' => $awardDetail,
            'cur_score' => $cur_score,
        ];
    }

    public function applyExchange_post() {
        $award_id = I('post.award_id', 0);
        $username = I('post.user_name', '');
        $phone    = I('post.phone', '');
        $email    = I('post.email', '');
        $desc     = I('post.desc', '');

        $award = D('Score/ScoreAward', 'Service')->applyExchange($award_id, $this->_login->user['m_uid'], $username, $phone, $email, $desc);

        $this->_result = ['status' => $award];
    }

    public function scoreLogList() {
        $page = I('get.page', 1);
        $limit = 5;

        list($start, $limit, $page) = page_limit($page, $limit);

        $service = D('Score/Score', 'Service');
        $list    = D('Score/ScoreMember', 'Service')->getMemberScoreLogList($this->_login->user['m_uid'], $start, $limit);
        $service->processScoreLogList($list);

        $total = D('Score/ScoreMember', 'Service')->countMemberScoreLogList($this->_login->user['m_uid']);

        $rule_list = D('Score/Score', 'Service')->getRuleList();

        $member_score = $service->getUserScore($this->_login->user['m_uid']);
        $member_name  = $this->_login->user['m_username'];
        $member_rank  = $service->getUserRank($this->_login->user['m_uid']);
        $member_face  = $this->_login->user['m_face'];

        $this->_result = [
            'rank'     => $member_rank,
            'face'     => $member_face,
            'list'     => $list,
            'cur_page' => $page,
            'page'     => ceil($total/$limit),
            'limit'    => $limit,
            'count'    => $total,
            'cur_score' => $member_score,
            'user_name' => $member_name,
            'rule_list' => $rule_list,
        ];
    }
}