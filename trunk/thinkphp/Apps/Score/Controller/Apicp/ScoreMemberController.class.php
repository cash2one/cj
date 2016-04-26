<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/4/12
 * Time: 19:06
 */

namespace Score\Controller\Apicp;
use Common\Common\Pager;

class ScoreMemberController extends AbstractController {

    /**
     * 成员列表页
     */
    public function memberList() {
        $service = D('Score/ScoreMember', 'Service');
        $page    = I('get.page', 0, 'intval');
        $name    = I('get.username', '');
        $cp_name = I('get.cp_name','');
        $order   = I('get.order', 0);
        $limit   = 6;
        //分页属性
        list($start, $limit, $page) = page_limit($page, $limit);
        //总数量
        $total = $service->countMemberList($name, $cp_name);
        $list  = $service->getMemberScoreList($name, $cp_name, $start, $limit, $order);
        //输出
        $this->_result = array(
            'list'     => $list,
            'cur_page' => $page,
            'page'     => ceil($total/$limit),
            'limit'    => $limit,
            'count'    => $total,
        );
    }

    /**
     * 成员详情页
     */
    public function memberDetail() {
        $uid     = I('get.uid', 355);
        $service = D('Score/ScoreMember', 'Service');
        $detail  = $service->getMemberDetail($uid);

        $this->_result = array(
            'detail' => $detail,
        );
    }

    /**
     * 成员积分明细页
     */
    public function memberScoreList() {
        $uid     = I('get.uid', 0);
        $page    = I('get.page', 1, 'intval');
        $service = D('Score/ScoreMember', 'Service');
        $limit   = 10;

        list($start, $limit, $page) = page_limit($page, $limit);

        $list  = $service->getMemberScoreLogList($uid, $start, $limit);
        $total = $service->countMemberScoreLogList($uid);
        //输出
        $this->_result = array(
            'list'     => $list,
            'cur_page' => $page,
            'page'     => ceil($total/$limit),
            'limit'    => $limit,
            'count'    => $total,
        );
    }

    /**
     * 成员奖品兑换记录列表页
     */
    public function memberAwardExchangeList() {
        $uid     = I('get.uid', 0);
        $page    = I('get.page', 1, 'intval');
        $service = D('Score/ScoreAward', 'Service');
        $limit   = 10;

        list($start, $limit, $page) = page_limit($page, $limit);

        $list  = $service->getMemberAwardList($uid, $start, $limit);
        $total = $service->countMemberAwardList($uid);
        //输出
        $this->_result = array(
            'list'     => $list,
            'cur_page' => $page,
            'page'     => ceil($total/$limit),
            'limit'    => $limit,
            'count'    => $total,
        );
    }

    /**
     * 批量修改用户积分
     */
    public function changeMembersScore() {
        $uids  = I('post.uids', array());
        $score = I('post.score', 0);
        $desc  = I('post.desc', '');

        $rs = D('Score/Score', 'Service')->updateScores($uids, $this->_login->user['m_uid'], $score, $desc);
        $this->_result = array(
            'status' => $rs,
        );
    }


}