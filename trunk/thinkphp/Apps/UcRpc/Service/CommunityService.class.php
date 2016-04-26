<?php
/**
 * CommunityService.class.php
 * $author$
 */

namespace UcRpc\Service;
use Common\Common\Cache;
use Common\Model\MemberModel;
use Think\Log;

class CommunityService extends AbstractService {

	const EVENT_IDENTIFIER = 'event';//活动
	const VOTE_IDENTIFIER = 'cnvote';//投票
	const COMMUNITY_IDENTIFIER = 'community';//话题
	const NEWS_IDENTIFIER = 'inform';//公告

	const LIEK_DYNAMIC = 1; //点赞
	const DISCUSS_DYNAMIC = 2;//评论
	const SIGN_DYNAMIC = 3;//活动报名
	const RELEASE_DYNAMIC = 4;//发表帖子release
	const COLLECT_DYNAMIC = 5;//收藏collect
	const SPONSORED_DYNAMIC = 6;//发起投票 sponsored
	const INVOLVED_DYNAMIC = 7;//参与投票 involved
	const LOGIN_DYNAMIC = 8;//登录
	const BROWSE_DYNAMIC = 9;//浏览 browse
	const REGISTER_DYNAMIC = 10;//活动签到 register
	// 构造方法
	public function __construct() {
		parent::__construct();

	}

	// 执行汇总任务
	public function update_gather() {


		Log::record('汇总crontab开始', Log::INFO);

		try {
			// 读取插件信息
			$model_plugin = D('Common/CommonPlugin');
			$plugin       = $model_plugin->get_by_identifier('Community');
			// 如果 agentid 为空
			if (empty($plugin['cp_agentid'])) {
				return true;
			}

			//dao注入
			$member_Model = D("Common/Member");
			// 读取用户列表--及已关注
			$m_data = array(
				'm_qywxstatus' => MemberModel::QYSTATUS_SUBCRIBE,
			);
			$count = $member_Model->get_real_count();
			if (!$count) {
				Log::record('read Membercount error.');
				return false;
			}
			$m_result = $member_Model->list_by_conds_all($m_data);
			if (!$m_result) {
				Log::record('read Memberlist error.');
				return false;
			}

			$result = array();
			$data = array(
				'user' => $m_result,
				'count' => $count
			);
			$this->__list_handle($data, $result);

			//更新冗余人员
			$redundancy = $member_Model->get_delete_member();

			$this->__update_delete_member($redundancy);

		}catch (\Exception $e){
			Log::record('活跃度汇总任务异常：', Log::ERR);
			Log::record($e->getMessage(), Log::ERR);
			return false;
		}

		Log::record('汇总crontab结束', Log::INFO);
		return true;
	}

	/**
	 * 获取用户的操作数--汇总
	 * @param $in
	 * @param $out
	 */
	private function __list_handle($in, &$out = array()) {

		$cache         = &Cache::instance();
		$cache_community_setting = $cache->get('Community.setting');

		$d_Model= D("Common/CommonDynamic");
		$m_Model = D("Community/CommunityTotal");

		//参与活动
		$active = $d_Model->get_active_group_uid(self::EVENT_IDENTIFIER, self::SIGN_DYNAMIC);
		$this->__format_uid($active, $format_active);

		//发帖
		$community = $d_Model->get_active_group_uid(self::COMMUNITY_IDENTIFIER, self::RELEASE_DYNAMIC);
		$this->__format_uid($community, $format_community);

		//投票
		//$vote = $d_Model->get_active_group_uid(self::VOTE_IDENTIFIER, self::SPONSORED_DYNAMIC);
		//评论
		$dis = $d_Model->get_active_group_uid('', self::DISCUSS_DYNAMIC);
		$this->__format_uid($dis, $format_dis);

		//阅读
		$red = $d_Model->get_active_group_uid('', self::BROWSE_DYNAMIC);
		$this->__format_uid($red, $format_red);

		//点赞
		$like = $d_Model->get_active_group_uid('', self::LIEK_DYNAMIC);
		$this->__format_uid($like, $format_like);
		//总数
		$sum_all = $d_Model->get_sum_group_uid();
		$this->__format_uid($sum_all, $format_sum_all);

		$m_Model->clean();
		$data = array();
		$i = 0;
		foreach($in['user'] as $k => $val) {
			$data[$i] =array(
				'uid' => $val['m_uid'],
				'username' => $val['m_username'],
				'phone' => $val['m_mobilephone'],
				'total' => isset($format_sum_all[$val['m_uid']]['score']) ? $format_sum_all[$val['m_uid']]['score'] : 0,
				'involved' => isset($format_active[$val['m_uid']]['count']) ? $format_active[$val['m_uid']]['count'] : 0,
				'published' => isset($format_community[$val['m_uid']]['count']) ? $format_community[$val['m_uid']]['count'] : 0,
				'received' => isset($format_dis[$val['m_uid']]['count']) ? $format_dis[$val['m_uid']]['count'] : 0,
				'thumbsup' => isset($format_like[$val['m_uid']]['count']) ? $format_like[$val['m_uid']]['count'] : 0,
				'my_sort' => $i
			);
			$i++;
		}

		$total = array();
		foreach ($data as $user) {
			$total[] = $user['total'];
		}
		//以数组total排序
		array_multisort($total, SORT_DESC, $data);


		//跟新等级
		$user_total = $in['count'];
		$levels_desc = isset($cache_community_setting['levels']) ? $cache_community_setting['levels'] : array();

		$levels = array_reverse($levels_desc);

		//判断可分级数
		$percentage_s = array();
		$i = 0;
		$percentage = 0;
		foreach ($levels_desc as $k => $v) {
			$percentage += floor($user_total * $v['percentage'] / 100);
			$percentage_s[] = floor($user_total * $v['percentage'] / 100);
			if (floor($user_total * $v['percentage']/ 100) >= 1){
				$i++;
			}
		}
		$_i = $i;
		$start = 0;
		$_j = 0;
		$page = 0;
		//等级机制
		foreach ($percentage_s as $key => $val) {

			//可升级等级人员为空时跳过
			if ($val < 1) {
				continue;
			}
			$_i--;
			if ($_j == 0) {
				$start = 0;
			} else {
				$start = $page+$start;
			}
			if ($_i <= 0 ) {
				$page = $user_total;
			} else {
				$page = $val;
			}

			//更新等级
			foreach ($data as $_key => &$_val) {
				if ($_key < $start || $_key >= ($start+$page)) {
					continue;
				}
				$i_key = $_i+1 ;
				$_val['name'] = $levels[$_i]['name'];
				$_val['levels'] = $i_key;
			}

			$_j++;

		}

		$m_Model->insert_all($data);
		return true;
	}

	private function __format_uid($in, &$out) {
		if ($in) {
			foreach ($in as &$value) {
				$out[$value['m_uid']] = $value;
			}
		}

		return true;
	}

	/**
	 * 更新人员删除后的冗余数据
	 * @param       $in
	 * @param array $out
	 */
	private function __update_delete_member($in, &$out = array()) {
		$ep_serv = D('Event/EventPartake');
		$e_serv = D('Event/Event');
		$mf_serv = D('Community/CommunityFriends');

		//三天内没有人员删除
		if (!$in) {
			return false;
		}

		//已删除人员活动报名
		$uid = array_column($in, 'm_uid');

		$result = $ep_serv->list_eventpartake_by_uid($uid);

		//删除人员没有报名
		if ($result) {
			//活动人员报名id
			$apid = array_column($result, 'apid');

			//活动id
			$acid = array_flip(array_flip(array_column($result, 'acid')));
			//删除人员
			$ep_serv->delete(array('apid' => $apid));

			//重新统计活动报名人数
			$count_result = $ep_serv->fetch_count_by_eids($acid);

			//更新总报名数
			foreach ($count_result as $key => $val) {
				$e_serv->update($val['acid'], array('reg_num' => $val['counts']));
			}
		}


		//获取关注人员
		$friends_reuslt = $mf_serv->list_by_uid($uid);

		if ($friends_reuslt) {
			$fid = array_column($friends_reuslt, 'f_id');
			$f_id = implode(',', $fid);
			//删除关注人员信息
			$mf_serv->delete($f_id);
		}


		return true;
	}

}
