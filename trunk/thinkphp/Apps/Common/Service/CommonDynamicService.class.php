<?php
/**
 * DynamicService.class.php
 * $author$
 */

namespace Common\Service;
use Common\Common\User;
use Common\Common\Cache;

class CommonDynamicService extends AbstractService {

	protected $_dynamic;
	protected $_community;
	protected $_community_friends;
	protected $_event;
	protected $_inform;
	protected $_cnvote;

	// 构造方法
	public function __construct() {

		parent::__construct();
		$this->_dynamic = D("Common/CommonDynamic");

		if ($this->avaiable_plugin('community')) {
			$this->_community = D("Community/Community");
			$this->_community_friends = D('Community/CommunityFriends');
		}

		if ($this->avaiable_plugin('event')) {
			$this->_event = D("Event/Event");
		}

		if ($this->avaiable_plugin('inform')) {
			$this->_inform = D("Inform/Inform");
		}

		if ($this->avaiable_plugin('cnvote')) {
			$this->_cnvote = D("Cnvote/Cnvote");
		}
	}

	/**
	 * 我的动态
	 *
	 * @param $uid 用户ID
	 * @return bool
	 */
	public function list_by_uid($uid, $page_option, $order_option) {

		// 话题收藏
		$list = $this->_dynamic->dynamic_by_uid($uid, $page_option, $order_option);

		$cids = array(); // 微社区id
		$eids = array(); // 活动id
		$i_ids = array(); // 公告id
		$nids = array(); // 投票id

		// 获取obj_id 并且判断类型租成id数组
		foreach ($list as $ks => $vs) {
			if ($vs['cp_identifier'] == 'community') {
				if (! empty($vs['obj_id'])) {
					$cids[] = $vs['obj_id'];
				}
			} elseif ($vs['cp_identifier'] == 'event') {
				$eids[] = $vs['obj_id'];
			} elseif ($vs['cp_identifier'] == 'inform') {
				$i_ids[] = $vs['obj_id'];
			} elseif ($vs['cp_identifier'] == 'cnvote') {
				$nids[] = $vs['obj_id'];
			}
		}
		$cids = implode(",", $cids);
		$eids = implode(",", $eids);
		$i_ids = implode(",", $i_ids);
		$nids = implode(",", $nids);

		if ($cids) {
			// 根据cids获取话题列表
			$community = $this->_community->get_by_cid($cids);
			$community_r = array();
			foreach ($community as $k => $v) {
				$community_r[$v['cid']] = $v;
			}
		}

		if ($eids) {
			// 根据eids获取活动列表
			$event = $this->_event->get_by_eid($eids);
			$event_r = array();
			foreach ($event as $k => $v) {
				$event_r[$v['eid']] = $v;
			}
		}

		if ($i_ids) {
			// 根据i_ids获取公告列表
			$inform = $this->_inform->get_by_i_id($i_ids);
			$inform_r = array();
			foreach ($inform as $k => $v) {
				$inform_r[$v['i_id']] = $v;
			}
		}

		if ($nids) {
			// 根据nids获取投票列表
			$nvote = $this->_cnvote->get_by_nids($nids);
			$nvote_r = array();
			foreach ($nvote as $k => $v) {
				$nvote_r[$v['id']] = $v;
			}
		}

		// 用户提前方法
		$result = array();

		// 重组数据
		foreach ($list as $ks => $vs) {
			if ($vs['cp_identifier'] == 'community') {
				switch ($vs['dynamic']) {
					case '2' : // 评论
						$result[$vs['id']]['ac'] = '评论了';
					break;
					case '3' : // 活动报名
						$result[$vs['id']]['ac'] = '报名了';
					break;
					case '4' : // 发表话题
						$result[$vs['id']]['ac'] = '发布了';
					break;
					case '7' : // 参与投票
						$result[$vs['id']]['ac'] = '参与了';
					break;
					case '11' : // 发布活动
						$result[$vs['id']]['ac'] = '发布了';
					break;
					default :
					// 没必要修改
					break;
				}
				$result[$vs['id']]['id'] = $vs['id'];
				$result[$vs['id']]['obj_id'] = $vs['obj_id'];
				$result[$vs['id']]['cp_identifier'] = $vs['cp_identifier'];
				$result[$vs['id']]['created'] = $vs['created'];
				$result[$vs['id']]['dynamic'] = $vs['dynamic'];
				$result[$vs['id']]['title'] = $community_r[$vs['obj_id']]['subject'];
				$result[$vs['id']]['author'] = $community_r[$vs['obj_id']]['username'];
				$result[$vs['id']]['m_face'] = User::instance()->avatar($community_r[$vs['obj_id']]['uid']);
				$at_ids = explode(',', $community_r[$vs['obj_id']]['attach_id']);
				$at_id = $at_ids[0];
				$result[$vs['id']]['thumb'] = $at_id ? attachment_url($at_id) : '';
				$result[$vs['id']]['start_time'] = $community_r[$vs['obj_id']]['created'];
				$result[$vs['id']]['type'] = '话题';
				$result[$vs['id']]['action'] = $result[$vs['id']]['ac'] . $result[$vs['id']]['type'];
			} elseif ($vs['cp_identifier'] == 'event') {
				switch ($vs['dynamic']) {
					case '2' : // 评论
						$result[$vs['id']]['ac'] = '评论了';
					break;
					case '3' : // 活动报名
						$result[$vs['id']]['ac'] = '报名了';
					break;
					case '4' : // 发表话题
						$result[$vs['id']]['ac'] = '发布了';
					break;
					case '7' : // 参与投票
						$result[$vs['id']]['ac'] = '参与了';
					break;
					case '11' : // 发布活动
						$result[$vs['id']]['ac'] = '发布了';
					break;
					default :
					// 没必要修改
					break;
				}
				$result[$vs['id']]['id'] = $vs['id'];
				$result[$vs['id']]['obj_id'] = $vs['obj_id'];
				$result[$vs['id']]['cp_identifier'] = $vs['cp_identifier'];
				$result[$vs['id']]['created'] = $vs['created'];
				$result[$vs['id']]['dynamic'] = $vs['dynamic'];
				$result[$vs['id']]['title'] = $event_r[$vs['obj_id']]['title'];
				$result[$vs['id']]['author'] = $event_r[$vs['obj_id']]['author'];
				$result[$vs['id']]['thumb'] = $event_r[$vs['obj_id']]['thumb'] ? attachment_url($event_r[$vs['obj_id']]['thumb']) : '';
				$result[$vs['id']]['start_time'] = $event_r[$vs['obj_id']]['start_time'];
				$result[$vs['id']]['address'] = $event_r[$vs['obj_id']]['city'];
				$result[$vs['id']]['type'] = '活动';
				$result[$vs['id']]['action'] = $result[$vs['id']]['ac'] . $result[$vs['id']]['type'];
			} elseif ($vs['cp_identifier'] == 'inform') {
				switch ($vs['dynamic']) {
					case '2' : // 评论
						$result[$vs['id']]['ac'] = '评论了';
					break;
					case '3' : // 活动报名
						$result[$vs['id']]['ac'] = '报名了';
					break;
					case '4' : // 发表话题
						$result[$vs['id']]['ac'] = '发布了';
					break;
					case '7' : // 参与投票
						$result[$vs['id']]['ac'] = '参与了';
					break;
					case '11' : // 发布活动
						$result[$vs['id']]['ac'] = '发布了';
					break;
					default :
					// 没必要修改
					break;
				}
				$result[$vs['id']]['id'] = $vs['id'];
				$result[$vs['id']]['obj_id'] = $vs['obj_id'];
				$result[$vs['id']]['cp_identifier'] = $vs['cp_identifier'];
				$result[$vs['id']]['created'] = $vs['created'];
				$result[$vs['id']]['dynamic'] = $vs['dynamic'];
				$result[$vs['id']]['title'] = $inform_r[$vs['obj_id']]['title'];
				$result[$vs['id']]['author'] = $inform_r[$vs['obj_id']]['author'];
				$result[$vs['id']]['thumb'] = attachment_url($inform_r[$vs['obj_id']]['cover_id']);
				$result[$vs['id']]['type'] = '公告';
				$result[$vs['id']]['action'] = $result[$vs['id']]['ac'] . $result[$vs['id']]['type'];
			} elseif ($vs['cp_identifier'] == 'cnvote') {
				switch ($vs['dynamic']) {
					case '2' : // 评论
						$result[$vs['id']]['ac'] = '评论了';
					break;
					case '3' : // 活动报名
						$result[$vs['id']]['ac'] = '报名了';
					break;
					case '4' : // 发表话题
						$result[$vs['id']]['ac'] = '发布了';
					break;
					case '7' : // 参与投票
						$result[$vs['id']]['ac'] = '参与了';
					break;
					case '11' : // 发布活动
						$result[$vs['id']]['ac'] = '发布了';
					break;
					default :
					// 没必要修改
					break;
				}
				$result[$vs['id']]['id'] = $vs['id'];
				$result[$vs['id']]['obj_id'] = $vs['obj_id'];
				$result[$vs['id']]['cp_identifier'] = $vs['cp_identifier'];
				$result[$vs['id']]['created'] = $vs['created'];
				$result[$vs['id']]['dynamic'] = $vs['dynamic'];
				$result[$vs['id']]['title'] = $nvote_r[$vs['obj_id']]['subject'];
				$result[$vs['id']]['author'] = $nvote_r[$vs['obj_id']]['m_username'];
				$result[$vs['id']]['thumb'] = $nvote_r[$vs['obj_id']]['thumb'] ? attachment_url($nvote_r[$vs['obj_id']]['thumb']) : '';
				$result[$vs['id']]['start_time'] = $nvote_r[$vs['obj_id']]['created'];
				$result[$vs['id']]['end_time'] = $nvote_r[$vs['obj_id']]['end_time'];
				$result[$vs['id']]['voted_mem_count'] = $nvote_r[$vs['obj_id']]['voted_mem_count'];
				$result[$vs['id']]['type'] = '投票';
				$result[$vs['id']]['action'] = $result[$vs['id']]['ac'] . $result[$vs['id']]['type'];
			}
		}

		// 根据动态时间倒序
		foreach ($result as $key => $value) {
			$created[$key] = $value['created'];
		}

		array_multisort($created, SORT_NUMERIC, SORT_DESC, $result);
		return $result;
	}

	/**
	 * 朋友圈动态
	 *
	 * @param $uid 用户ID
	 * @return bool
	 */
	public function list_all_by_uid($uid, $page_option, $order_option) {

		$result = array();
		$friends_list = $this->_community_friends->list_friends_by_conds($uid);

		// 如果没有关注用户 则返回空
		if (empty($friends_list)) {
			return $result;
		}

		$uids = array_column($friends_list, 'g_id');
		$uids[] = $uid;

		$uids_arr = implode(',', $uids);
		// 话题收藏
		$list = $this->_dynamic->dynamic_all_by_uid($uids_arr, null, $order_option);

		// 代替分页
		$list = array_slice($list, $page_option[0], $page_option[1]);

		$cids = array(); // 微社区id
		$eids = array(); // 活动id
		$i_ids = array(); // 公告id
		$nids = array(); // 投票id

		// 获取obj_id 并且判断类型租成id数组
		foreach ($list as $ks => $vs) {
			if ($vs['cp_identifier'] == 'community') {
				if (! empty($vs['obj_id'])) {
					$cids[] = $vs['obj_id'];
				}
			} elseif ($vs['cp_identifier'] == 'event') {
				$eids[] = $vs['obj_id'];
			} elseif ($vs['cp_identifier'] == 'inform') {
				$i_ids[] = $vs['obj_id'];
			} elseif ($vs['cp_identifier'] == 'cnvote') {
				$nids[] = $vs['obj_id'];
			}
		}
		$cids = implode(",", $cids);
		$eids = implode(",", $eids);
		$i_ids = implode(",", $i_ids);
		$nids = implode(",", $nids);

		if ($cids) {
			// 根据cids获取话题列表
			$community = $this->_community->get_by_cid($cids);
			$community_r = array();
			foreach ($community as $k => $v) {
				$community_r[$v['cid']] = $v;
			}
		}

		if ($eids) {
			// 根据eids获取活动列表
			$event = $this->_event->get_by_eid($eids);
			$event_r = array();
			foreach ($event as $k => $v) {
				$event_r[$v['eid']] = $v;
			}
		}

		if ($i_ids) {
			// 根据i_ids获取公告列表
			$inform = $this->_inform->get_by_i_id($i_ids);
			$inform_r = array();
			foreach ($inform as $k => $v) {
				$inform_r[$v['i_id']] = $v;
			}
		}

		if ($nids) {
			// 根据nids获取投票列表
			$nvote = $this->_cnvote->get_by_nids($nids);
			$nvote_r = array();
			foreach ($nvote as $k => $v) {
				$nvote_r[$v['id']] = $v;
			}
		}

		// 重组数据
		foreach ($list as $ks => $vs) {
			if ($vs['cp_identifier'] == 'community') {
				switch ($vs['dynamic']) {
					case '2' : // 评论
						$result[$vs['id']]['ac'] = '评论了';
					break;
					case '3' : // 活动报名
						$result[$vs['id']]['ac'] = '报名了';
					break;
					case '4' : // 发表话题
						$result[$vs['id']]['ac'] = '发布了';
					break;
					case '7' : // 参与投票
						$result[$vs['id']]['ac'] = '参与了';
					break;
					case '11' : // 发布活动
						$result[$vs['id']]['ac'] = '发布了';
					break;
					default :
					// 没必要修改
					break;
				}
				$result[$vs['id']]['id'] = $vs['id'];
				$result[$vs['id']]['obj_id'] = $vs['obj_id'];
				$result[$vs['id']]['cp_identifier'] = $vs['cp_identifier'];
				$result[$vs['id']]['created'] = $vs['created'];
				$result[$vs['id']]['dynamic'] = $vs['dynamic'];
				$result[$vs['id']]['title'] = $community_r[$vs['obj_id']]['subject'];
				$result[$vs['id']]['author'] = $vs['m_username'];
				$result[$vs['id']]['m_face'] = User::instance()->avatar($vs['m_uid']);
				$result[$vs['id']]['m_uid'] = $vs['m_uid'];
				$at_id = array();
				if ($community_r[$vs['obj_id']]['attach_id']) {
					$at_ids = explode(',', $community_r[$vs['obj_id']]['attach_id']);
					foreach ($at_ids as $v) {
						$at_id[] = attachment_url($v);
					}
				}

				$result[$vs['id']]['thumb'] = $at_id;
				$result[$vs['id']]['start_time'] = $community_r[$vs['obj_id']]['created'];
				$result[$vs['id']]['type'] = '话题';
				$result[$vs['id']]['action'] = $result[$vs['id']]['ac'] . $result[$vs['id']]['type'];
			} elseif ($vs['cp_identifier'] == 'event') {
				switch ($vs['dynamic']) {
					case '2' : // 评论
						$result[$vs['id']]['ac'] = '评论了';
					break;
					case '3' : // 活动报名
						$result[$vs['id']]['ac'] = '报名了';
					break;
					case '4' : // 发表话题
						$result[$vs['id']]['ac'] = '发布了';
					break;
					case '7' : // 参与投票
						$result[$vs['id']]['ac'] = '参与了';
					break;
					case '11' : // 发布活动
						$result[$vs['id']]['ac'] = '发布了';
					break;
					default :
					// 没必要修改
					break;
				}
				$result[$vs['id']]['id'] = $vs['id'];
				$result[$vs['id']]['obj_id'] = $vs['obj_id'];
				$result[$vs['id']]['cp_identifier'] = $vs['cp_identifier'];
				$result[$vs['id']]['created'] = $vs['created'];
				$result[$vs['id']]['dynamic'] = $vs['dynamic'];
				$result[$vs['id']]['title'] = $event_r[$vs['obj_id']]['title'];
				$result[$vs['id']]['author'] = $vs['m_username'];
				$result[$vs['id']]['m_face'] = User::instance()->avatar($vs['m_uid']);
				$result[$vs['id']]['m_uid'] = $vs['m_uid'];
				$at_id = array();
				if ($event_r[$vs['obj_id']]['thumb']) {
					$at_id[] = attachment_url($event_r[$vs['obj_id']]['thumb']);
				}
				$result[$vs['id']]['thumb'] = $at_id;
				$result[$vs['id']]['start_time'] = $event_r[$vs['obj_id']]['start_time'];
				$result[$vs['id']]['address'] = $event_r[$vs['obj_id']]['city'];
				$result[$vs['id']]['type'] = '活动';
				$result[$vs['id']]['action'] = $result[$vs['id']]['ac'] . $result[$vs['id']]['type'];
			} elseif ($vs['cp_identifier'] == 'inform') {
				switch ($vs['dynamic']) {
					case '2' : // 评论
						$result[$vs['id']]['ac'] = '评论了';
					break;
					case '3' : // 活动报名
						$result[$vs['id']]['ac'] = '报名了';
					break;
					case '4' : // 发表话题
						$result[$vs['id']]['ac'] = '发布了';
					break;
					case '7' : // 参与投票
						$result[$vs['id']]['ac'] = '参与了';
					break;
					case '11' : // 发布活动
						$result[$vs['id']]['ac'] = '发布了';
					break;
					default :
					// 没必要修改
					break;
				}
				$result[$vs['id']]['id'] = $vs['id'];
				$result[$vs['id']]['obj_id'] = $vs['obj_id'];
				$result[$vs['id']]['cp_identifier'] = $vs['cp_identifier'];
				$result[$vs['id']]['created'] = $vs['created'];
				$result[$vs['id']]['dynamic'] = $vs['dynamic'];
				$result[$vs['id']]['title'] = $inform_r[$vs['obj_id']]['title'];
				$result[$vs['id']]['author'] = $vs['m_username'];
				$result[$vs['id']]['m_face'] = User::instance()->avatar($vs['m_uid']);
				$result[$vs['id']]['m_uid'] = $vs['m_uid'];
				$at_id = array();
				if ($inform_r[$vs['obj_id']]['cover_id']) {
					$at_id[] = attachment_url($inform_r[$vs['obj_id']]['cover_id']);
				}

				$result[$vs['id']]['thumb'] = $at_id;
				$result[$vs['id']]['type'] = '公告';
				$result[$vs['id']]['action'] = $result[$vs['id']]['ac'] . $result[$vs['id']]['type'];
			} elseif ($vs['cp_identifier'] == 'cnvote') {
				switch ($vs['dynamic']) {
					case '2' : // 评论
						$result[$vs['id']]['ac'] = '评论了';
					break;
					case '3' : // 活动报名
						$result[$vs['id']]['ac'] = '报名了';
					break;
					case '4' : // 发表话题
						$result[$vs['id']]['ac'] = '发布了';
					break;
					case '7' : // 参与投票
						$result[$vs['id']]['ac'] = '参与了';
					break;
					case '11' : // 发布活动
						$result[$vs['id']]['ac'] = '发布了';
					break;
					default :
					// 没必要修改
					break;
				}
				$result[$vs['id']]['id'] = $vs['id'];
				$result[$vs['id']]['obj_id'] = $vs['obj_id'];
				$result[$vs['id']]['cp_identifier'] = $vs['cp_identifier'];
				$result[$vs['id']]['created'] = $vs['created'];
				$result[$vs['id']]['dynamic'] = $vs['dynamic'];
				$result[$vs['id']]['title'] = $nvote_r[$vs['obj_id']]['subject'];
				$result[$vs['id']]['author'] = $vs['m_username'];
				$result[$vs['id']]['m_face'] = User::instance()->avatar($vs['m_uid']);
				$result[$vs['id']]['m_uid'] = $vs['m_uid'];
				$at_id = array();
				if ($nvote_r[$vs['obj_id']]['thumb']) {
					$at_id[] = attachment_url($nvote_r[$vs['obj_id']]['thumb']);
				}

				$result[$vs['id']]['thumb'] = $at_id;
				$result[$vs['id']]['start_time'] = $nvote_r[$vs['obj_id']]['created'];
				$result[$vs['id']]['end_time'] = $nvote_r[$vs['obj_id']]['end_time'];
				$result[$vs['id']]['voted_mem_count'] = $nvote_r[$vs['obj_id']]['voted_mem_count'];
				$result[$vs['id']]['type'] = '投票';
				$result[$vs['id']]['action'] = $result[$vs['id']]['ac'] . $result[$vs['id']]['type'];
			}
		}

		// 根据动态时间倒序
		foreach ($result as $key => $value) {
			$created[$key] = $value['created'];
		}

		array_multisort($created, SORT_NUMERIC, SORT_DESC, $result);
		return $result;
	}

	/**
	 * 朋友圈动态总数
	 * @param $uid
	 * @return int
	 */
	public function total_all_by_uid($uid) {

		// 总记录列表
		$friends_list = $this->_community_friends->list_friends_by_conds($uid);

		// 如果为空 则为0
		if (empty($friends_list)) {
			return 0;
		}
		$uids = array_column($friends_list, 'g_id');
		$uids[] = $uid;

		$uids_arr = implode(',', $uids);
		// 话题收藏
		$list = $this->_dynamic->dynamic_all_by_uid($uids_arr);

		// 总记录数
		$total = count($list);

		return $total;
	}

	/**
	 * 我的动态总数
	 * @param $uid 用户ID
	 * @return bool
	 */
	public function total_by_uid($uid) {

		$list = $this->_dynamic->totals_by_uid($uid);
		$total = $list['total'];
		return $total;
	}

	/**
	 * 添加公共动态
	 * @param $conds
	 * @return bool
	 */
	public function add_dynamic($conds) {

		// 插入积分制度
		$this->__add_integral($conds);

		// 入库
		$result = $this->_dynamic->insert($conds);
		if (empty($result)) {
			$this->_set_error('_ERR_INSERT_ERROR');
			return false;
		}

		return true;
	}

	/**
	 * 增加积分制度
	 * @param $data
	 * @return bool
	 */
	private function __add_integral(&$data) {

		if(empty($data)) {
			return true;
		}

		// 获取社区setting
		$cache = &Cache::instance();
		$setting = $cache->get('Community.setting');

		if ($data['dynamic'] == 7) {
			// 参加投票
			$sum = $this->_dynamic->get_day_data_by_uid($data['m_uid'], $data['dynamic']);
			if ($sum['nums'] < $setting['cnvote_day']) {
				$data['score'] = $setting['cnvote_join'];
			}

		} elseif($data['dynamic'] == 3 || $data['dynamic'] == 10) {

			// 活动报名 活动签到
			$sum = $this->_dynamic->get_week_data_by_uid($data['m_uid'], $data['dynamic']);
			if ($sum['nums'] < $setting['event_week']) {
				if ($data['dynamic'] == 3) {
					$data['score'] = $setting['event_join'];
				} else {
					$data['score'] = $setting['event_sign'];
				}

			}
		} elseif($data['dynamic'] == 9) {

			$conds = array(
				'm_uid' => $data['m_uid'],
				'dynamic' => $data['dynamic'],
				'obj_id' => $data['obj_id'],
				'cp_identifier' => $data['cp_identifier']
			);

			$data_result = $this->_dynamic->get_day_view_data_by_conds($conds);
			if (empty($data_result)) {
				// 浏览记录
				$sum = $this->_dynamic->get_day_data_by_uid($data['m_uid'], $data['dynamic']);
				if ($sum['nums'] < $setting['view_day']) {
					$data['score'] = $setting['view_forum'];
				}
			}

		} elseif($data['dynamic'] == 2) {
			// 用户评论积分
			$sum = $this->_dynamic->get_day_data_by_uid($data['m_uid'], $data['dynamic']);
			if ($sum['nums'] < $setting['comment_day']) {
				$data['score'] = $setting['comment'];
			}

			// 处理话题的评论积分
			if ($data['cp_identifier'] == 'community') {
				// 查询条件
				$conds = array('obj_id' => $data['obj_id'], 'dynamic' => $data['dynamic'], 'cp_identifier' => 'community');
				// 查询当前话题回复的总数目
				$counts = $this->_dynamic->count_by_conds($conds);

				$conds['dynamic'] = 4;
				$comment_detail = $this->_dynamic->get_real_by_conds($conds);

				// 回复次数在5-15次之间
				if ($counts >5 && $counts < 16) {

					// 判断当前积分是否超过规定的封顶值 如果没有则根据规则增加积分
					if ($comment_detail['score'] > $setting['add_total']) {
						$update_data = array('score' => $setting['add_total']);
					} else {
						$counts = $comment_detail['score'] + $setting['add_reply_five'];
						$update_data = array('score' => $counts);
					}

				// 回复次数在15以上 40以下
				} elseif($counts > 15 && $counts < 41) {

					// 判断当前积分是否超过规定的封顶值 如果没有则根据规则增加积分
					if ($comment_detail['score'] > $setting['add_total']) {
						$update_data = array('score' => $setting['add_total']);
					}else {
						$counts = $comment_detail['score'] + $setting['add_reply_fiveth'];
						$update_data = array('score' => $counts);
					}
				// 回复次数超过40以上
				} elseif ($counts == 41) {
					$counts = $comment_detail['score'] + $setting['add_ext_fourth'];
					$update_data = array('score' => $counts);
				}

				// 更新发话题作者的积分数目
				$this->_dynamic->update_by_conds($conds, $update_data);
			}
		} elseif($data['dynamic'] == 4) {
			$data['score'] = $setting['add_forum'];
		}
	}

	/**
	 * 删除公共动态
	 * @param $conds
	 * @return bool
	 */
	public function del_dynamic($conds) {

		// 删除动态
		$this->_dynamic->delete_by_conds(array(
			'obj_id' => $conds['obj_id'],
			'cp_identifier' => $conds['cp_identifier'],
			'm_uid' => $conds['m_uid'],
			'dynamic' => $conds['dynamic'],
		));
		return true;
	}

	/**
	 * 删除点赞动态
	 * @param $conds
	 * @return bool
	 */
	public function del_likesdynamic($conds) {

		// 删除
		$this->_dynamic->delete_by_conds(array(
			'obj_id' => $conds['obj_id'],
			'cp_identifier' => $conds['cp_identifier'],
			'm_uid' => $conds['m_uid'],
			'dynamic' => $conds['dynamic'],
		));
		return true;
	}

	/**
	 * 条件查询单个动态数据是否存在
	 * @param $obj_id
	 * @param $uid
	 * @param $cp_identifier
	 * @param $dynamic
	 * @return bool
	 */
	public function get_dynamic_by_conds($data) {

		// 查询收藏的动态是否存在
		$result = $this->_dynamic->get_by_data($data);

		if (!empty($result)) {
			return false;
		}

		return true;
	}

	/**
	 * 条件查询点赞动态数据是否存在
	 * @param $obj_id
	 * @param $uid
	 * @param $cp_identifier
	 * @param $dynamic
	 * @return bool
	 */
	public function get_likedynamic_by_conds($data) {

		// 查询收藏的动态是否存在
		$result = $this->_dynamic->get_by_data($data);

		if (empty($result)) {
			return false;
		}

		return $result;
	}

	/**
	 * 通过主键删除收藏信息
	 * @param array $arr
	 * @return bool
	 */
	public function delete_dynamic_pks($arr = array()) {

		// 参数不能为空
		if (empty($arr)) {
			$this->_set_error('_ERROR_PARAMS_IS_NOT');
			return false;
		}

		$this->_dynamic->delete($arr);
		return true;
	}

	/**
	 * 查询用户记录详情
	 *
	 * @param array $conds
	 * @return array
	 *
	 */
	public function get_by_uid($conds) {

		$data = array(
			'm_uid' => $conds['uid'],
			'obj_id' => $conds['cid'],
			'cp_identifier' => $conds['cp_identifier'],
			'dynamic' => $conds['dynamic']
		);
		$list = $this->_dynamic->list_by_conds($data);

		return $list;
	}
}

//end
