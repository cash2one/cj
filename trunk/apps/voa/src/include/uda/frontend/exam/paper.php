<?php
/**
 * voa_uda_frontend_exam_paper
 * 统一数据访问/试卷相关操作
 * Create By wogu
 * $Author$
 * $Id$
 */

class voa_uda_frontend_exam_paper extends voa_uda_frontend_base {
	/** service 类 */
	private $__service = null;
	public function __construct() {
		parent::__construct();
		if ($this->__service == null) {
			$this->__service = new voa_s_oa_exam_paper();
		}
	}

	public function edit_setting(array $request, &$result, $args) {
		$fields = array(
			'is_all' => array(
				'is_all', parent::VAR_INT,
				array(),
				null, false
			),
			'cover_id' => array(
				'cover_id', parent::VAR_INT,
				array($this->__service, 'validator_cover_id'),
				null, false,
			),
			'begin_time' => array(
				'begin_time', parent::VAR_STR,
				array($this->__service, 'validator_begin_time'),
				null, false,
			),
			'end_time' => array(
				'end_time', parent::VAR_STR,
				array($this->__service, 'validator_end_time'),
				null, false,
			),
			'begin_date' => array(
				'begin_date', parent::VAR_STR,
				array($this->__service, 'validator_begin_time'),
				null, false,
			),
			'end_date' => array(
				'end_date', parent::VAR_STR,
				array($this->__service, 'validator_end_time'),
				null, false,
			),
			'paper_time' => array(
				'paper_time', parent::VAR_INT,
				array($this->__service, 'validator_paper_time'),
				null, false,
			),
			'is_notify' => array(
				'is_notify', parent::VAR_INT,
				array(),
				null, false,
			),
			'notify_begin' => array(
				'notify_begin', parent::VAR_INT,
				array(),
				null, false,
			),
			'notify_end' => array(
				'notify_end', parent::VAR_INT,
				array(),
				null, false,
			),
			'notifynow' => array(
				'notifynow', parent::VAR_INT,
				array(),
				null, false,
			),
			'pass_score' => array(
				'pass_score', parent::VAR_INT,
				array(),
				null, false,
			),
			'intro' => array(
				'intro', parent::VAR_STR,
				array($this->__service, 'validator_intro'),
				null, false,
			),
		);

		// 检查过滤，参数，在返回和预览的时候不检查
		if( !in_array( $request['submitype'], array('preview', 'goback') ) ){
			if ( !$this->extract_field($this->__request, $fields, $request) ) {
				return false;
			}
		}else{
			$this->_default_field($this->__request, $fields, $request);
		}


		$this->__request['m_uids'] = '';
		$this->__request['cd_ids'] = '';
		$m_uids = $cd_ids = array();
		if(!$this->__request['is_all']) {
			if(is_array($request['m_uids'])) {
				foreach ($request['m_uids'] as $uid) {
					if(!is_numeric($uid)) return false;
					$m_uids[] = $uid;
				}

				$this->__request['m_uids'] = implode(',', $m_uids);
			}

			if(is_array($request['cd_ids'])) {
				foreach ($request['cd_ids'] as $cd_id) {
					if(!is_numeric($cd_id)) return false;
					$cd_ids[] = $cd_id;
				}
				$this->__request['cd_ids'] = implode(',', $cd_ids);
			}

			if(empty($m_uids) && empty($cd_ids)) {
				return false;
			}

			// 获取部门人员 分批插入改造
			/*
			if(!empty($cd_ids)) {
				$s_md = new voa_s_oa_member_department();
				$tmp = $s_md->fetch_all_by_conditions(array('cd_id' => array($cd_ids, 'IN')));
				foreach ($tmp as $v) {
					$m_uids[] = $v['m_uid'];
				}
				$m_uids = array_unique($m_uids);
			}
			*/
			// 获取范围内人员的openid 分批插入改造
			/*
			$s_member = new voa_s_oa_member();
			$members = $s_member->fetch_all_by_ids($m_uids);
			foreach($members as $member) {
				$tousers[] = $member['m_openid'];
			}
			*/
		} else {
			// 在is_all的时候获取全部人员 分批插入改造
			/*
			$s_member = new voa_s_oa_member();
			$members = $s_member->fetch_all();
			foreach($members as $member) {
				$m_uids[] = $member['m_uid'];
				$tousers[] = $member['m_openid'];
			}
			*/
		}

		try {
			$this->__service->begin();

			$paper = array(
				'is_all' => $this->__request['is_all'],
				'cover_id' => $this->__request['cover_id'],
				'paper_time' => $this->__request['paper_time'],
				'is_notify' => $this->__request['is_notify'],
				'notify_begin' => $this->__request['notify_begin'],
				'notify_end' => $this->__request['notify_end'],
				'notifynow' => $this->__request['notifynow'],
				'pass_score' => $this->__request['pass_score'],
				'intro' => $this->__request['intro'],
				'm_uids' => $this->__request['m_uids'],
				'cd_ids' => $this->__request['cd_ids'],
				'status' => $request['pubsubmit'] ? 1 : 0 // 发布的时候设置状态
			);

			if($this->__request['begin_date']&&$this->__request['begin_time']&&$this->__request['end_date']&&$this->__request['end_time']){
				$paper['begin_time']=rstrtotime($this->__request['begin_date'].' '.$this->__request['begin_time']);
				$paper['end_time']=rstrtotime($this->__request['end_date'].' '.$this->__request['end_time']);
			}
			if($paper['begin_time']<startup_env::get('timestamp')||$paper['begin_time']>$paper['end_time']){
				//$paper['begin_time']=$paper['end_time']=0;
			}

			if(!empty($request['pubsubmit'])){
				$paper['flag']=0;
			}

			/*xavi mod
			* 预览和草稿的时候只更新设置，并不发试卷通知和插入试卷
			* 发布的时候才插入试卷和发通知
			* 预览和返回的时候的特殊处理
			*/

			if( in_array( $request['submitype'], array('preview', 'goback') ) ){
				// 如果为空则不更新该字段
				$oldpaper = $this->__service->get_by_id($args['id']);
				foreach ($paper as $_k => $_v) {
					if(empty($_v)){
						unset($paper[$_k]);
					}
				}
				$paper['status']=$oldpaper['status'];
			}


			$this->__service->update($args['id'], $paper);

			$result = $paperinfo = $this->__service->get_by_id($args['id']);

			if(!empty($request['pubsubmit'])){

				$s_member = new voa_s_oa_member();
				$s_department = new voa_s_oa_common_department();
				// 插入考试记录
				$part_size = 1000; // 插入块大小
				// 分块获取m_uids
				if(!$this->__request['is_all']) {

					$users = $s_member->fetch_all_by_ids($m_uids);
					$depms = $s_department->fetch_all_by_key($cd_ids);
					$touser = implode('|', array_column($users, 'm_openid'));
					$toparty = implode('|', array_column($depms, 'cd_qywxid'));

					// 非全公司
					if(!empty($cd_ids)) {
						// 递归获取所有部门id
						$all_depms = $s_department->fetch_all();
						$tree_cd_cids = $cd_ids;
						foreach ($cd_ids as $cbid) {
							$tree_cd_cids = array_merge($tree_cd_cids, $this->get_tree_cd_cids($all_depms, $cbid));
						}
						$tree_cd_cids = array_unique($tree_cd_cids);
						//logger::error(var_export($tree_cd_cids, true));

						$s_md = new voa_s_oa_member_department();
						$member_count = $s_md->count_by_conditions(array('cd_id' => array($tree_cd_cids, 'IN')));
						$total_pages = ceil($member_count / $part_size);
						for ($p=1; $p <= $total_pages; $p++) {
							$start = ($p-1)*$row;
							$tmp = $s_md->fetch_all_by_conditions(array('cd_id' => array($tree_cd_cids, 'IN')), array(), $start, $part_size);
							foreach ($tmp as $v) {
								$m_uids[] = $v['m_uid'];
							}
							unset($tmp);
						}
						$m_uids = array_unique($m_uids);
					}
				}else{
					// 全公司
					$touser = '@all';
					$toparty = '';
					$member_count = $s_member->count_all();
					$members = $s_member->fetch_all();
					$total_pages = ceil($member_count / $part_size);
					for ($p=1; $p <= $total_pages; $p++) {
						$start = ($p-1)*$row;
						$tmp = $s_member->fetch_all_by_conditions(array(), array(), $start, $part_size);
						foreach ($tmp as $v) {
							$m_uids[] = $v['m_uid'];
						}
						unset($tmp);
					}
				}
				if(!empty($m_uids)) {
					$s_tj = new voa_s_oa_exam_tj();
					$s_tj->delete_by_paper_id($args['id']);
					// 分块m_uids
					$total_pages = ceil(count($m_uids)/$part_size);
					for ($p=1; $p <= $total_pages; $p++) {
						$tmp = array_splice($m_uids, 0, $part_size);
						$data = array();
						foreach ($tmp as $m_uid) {
							$data[] = array(
								'm_uid' => $m_uid,
								'paper_id' => $args['id'],
								'paper_name' => $paperinfo['name'],
								'total_score' => $paperinfo['total_score'],
								'pass_score' => $paperinfo['pass_score'],
								'ti_num' => $paperinfo['ti_num'],
								'begin_time' => $paper['begin_time'],
								'end_time' => $paper['end_time'],
								'paper_time' => $this->__request['paper_time'],
								'departments' => implode(',', $paperinfo['departments']),
								'intro' => $paperinfo['intro'],
								'status' => 0 // 0:未参加 1:已开始考试 2:考试完成
							);
						}
						unset($tmp);
						if(!empty($data)){
							$s_tj->insert_multi($data);
						}

					}
				}
				/*
				 * 计划任务提醒
				*/
				$taskid_start = md5('exam_start_' . $args['id']);
				$taskid_stop = md5('exam_stop_' . $args['id']);
				$taskid_over = md5('exam_over_' . $args['id']);
				$domain = $args['domain'];
				$type_start = 'exam_start';
				$type_stop = 'exam_stop';
				$type_over = 'exam_over';
				$rpc = voa_h_rpc::phprpc(config::get('voa.uc_url') . 'OaRpc/Rpc/Crontab');
				$rpc->Del_by_taskid_domain_type($taskid_start, $domain, $type_start);
				$rpc->Del_by_taskid_domain_type($taskid_stop, $domain, $type_stop);
				$rpc->Del_by_taskid_domain_type($taskid_over, $domain, $type_over);

				if($this->__request['is_notify']) {
					if($this->__request['notify_begin'] > 0) {
						$rpc->Add(array(
							'taskid' => $taskid_start,
							'domain' => $domain,
							'type' => $type_start,
							'params' => array('papaer_id'=>$args['id']),
							'runtime' => $paper['begin_time'] - $this->__request['notify_begin'] * 60,
							'times' => 1
						));
					}

					if($this->__request['notify_end'] > 0) {
						$rpc->Add(array(
							'taskid' => $taskid_stop,
							'domain' => $domain,
							'type' => $type_stop,
							'params' => array('papaer_id'=>$args['id']),
							'runtime' => $paper['end_time'] - $this->__request['notify_end'] * 60,
							'times' => 1
						));
					}
				}

				$rpc->Add(array(
					'taskid' => $taskid_over,
					'domain' => $domain,
					'type' => $type_over,
					'params' => array('papaer_id'=>$args['id']),
					'runtime' => $paper['end_time'],
					'times' => 1
				));

				// 是否立即发送通知
				if($this->__request['notifynow']) {
					// 发送消息
					$exam_sets = voa_h_cache::get_instance()->get('plugin.exam.setting', 'oa');
					startup_env::set('pluginid', $exam_sets['pluginid']);
					startup_env::set('agentid', $exam_sets['agentid']);
					$msg_url = 'http://' . $args['domain'] . '/Exam/Frontend/Index/PaperDetail?paper_id=' . $paperinfo['id'];

					$msg_title = '【考试通知】'.$paperinfo['name'];
					$msg_desc = msubstr($paperinfo['intro'], 0, 60);
					$msg_picurl = voa_h_attach::attachment_url($paperinfo['cover_id']);
					voa_h_qymsg::push_news_send_queue($this->session, $msg_title, $msg_desc, $msg_url, $touser, $toparty, $msg_picurl);
				}

			}

			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollback();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		return true;
	}

	/**
	 * 新增试卷
	 * @param array $request 请求的参数
	 * @param array $result (引用结果)新增的试卷
	 * @param array $args 其他额外的参数（扩展用）
	 * @return boolean
	 */
	public function add_paper(array $request, &$result, $args) {
		// 定义参数请求规则
		$fields = array(
			'type' => array(
				'type', parent::VAR_INT,
				array($this->__service, 'validator_type'),
				null, false
			),
			'name' => array(
				'name', parent::VAR_STR,
				array($this->__service, 'validator_name'),
				null, false,
			),
			'tiku' => array(
				'tiku', parent::VAR_ARR,
				array($this->__service, 'validator_tiku'),
				null, false,
			),
			'use_all' => array(
				'use_all', parent::VAR_INT,
				array(),
				null, false
			),
		);

		if($request['type'] != 0) {
			$fields['rules'] = array(
				'rules', parent::VAR_ARR,
				array($this->__service, 'validator_rules'),
				null, false,
			);
		}

		// 检查过滤，参数
		if (!$this->extract_field($this->__request, $fields, $request)) {
			return false;
		}

		try {
			$this->__service->begin();

			$paper = array(
				'type' => $this->__request['type'],
				'name' => $this->__request['name'],
				'tiku' => implode(',', $this->__request['tiku']),
				'rules' => isset($this->__request['rules']) ? serialize($this->__request['rules']) : '',
				'use_all' => $this->__request['use_all'] == 1 ? 1 : 0
			);

			$s_detail = new voa_s_oa_exam_paperdetail();
			$rebuild = false; // 是否需要重新生成试题
			if($args['id']) {
				$paperinfo = $this->__service->get($args['id']);
				// 如果试卷类型不同或题库不同，则清空试卷
				if(($paperinfo['type'] != $paper['type'])
					|| ($paperinfo['tiku'] != $paper['tiku'])
					|| ($paper['type'] == 0 && $paper['use_all'] != $paperinfo['use_all'])) {
					$s_detail->delete_by_paperid($args['id']);
					$rebuild = true;
					$paperinfo['status'] = 0;
				}

				$paper['status'] = $paperinfo['status'];
				$this->__service->update($args['id'], $paper);
				$result = $paper;
				$result['id'] = $args['id'];
			} else {
				$paper['username'] = $args['username'];
				$paper['status'] = 0;
				$result = $this->__service->insert($paper);
				$rebuild = true;
			}

			// 使用所有题库
			if($this->__request['type'] == 0 && $paper['use_all'] == 1 && $rebuild) {
				$s_ti = new voa_s_oa_exam_ti();
				$allti = $s_ti->list_by_tiku_ids($this->__request['tiku']);
				$tis = array();
				$total_score = 0;
				$i=$j=0;
				foreach ($allti as $ti) {
					if($i!=$ti['tiku_id']){
						$i=$ti['tiku_id'];
						$j++;
					}
					$tis[] = array(
						'paper_id' => $result['id'],
						'ti_id' => $ti['id'],
						'orderby' => $ti['orderby'] +$j * 1000,
						'score' => $ti['score']
					);
					$total_score += $ti['score'];
				}
				$s_detail->insert_multi($tis);

				// 更新试卷总分
				$s_paper = new voa_s_oa_exam_paper();
				$s_paper->update_by_conds(array('id' => $result['id']), array('total_score' => $total_score, 'ti_num' => count($tis), 'status'=>0));
			} elseif($this->__request['type'] == 2) { // 随机生成题目
				// 计算总分及总题目
				$total_score = $ti_num = 0;
				foreach ($this->__request['rules'] as $type => $rule) {
					$ti_num += $rule['num'];
					$total_score += $rule['num'] * $rule['score'];
				}
				// 更新试卷总分
				$s_paper = new voa_s_oa_exam_paper();
				$s_paper->update_by_conds(array('id' => $result['id']), array('total_score' => $total_score, 'ti_num' => $ti_num, 'status'=>0));
			}

			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollback();
			logger::error($e);
			throw new service_exception($e->getMessage(), $e->getCode());
		}
		return true;
	}

	public function delete_paper($ids) {
		try {
			$this->__service->begin();
			$s_detail = new voa_s_oa_exam_paperdetail();
			$conds = array(
				'paper_id' => $ids
			);
			$s_detail->delete_by_conds($conds);
			$this->__service->delete($ids);
			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollBack();
			return $this->set_errmsg(voa_errcode_oa_exam::DELETE_PAPER_FAILED);
		}
		return true;
	}

	public function stop_paper($id, $reason, $agentid, $domain, $username) {
		try {
			$this->__service->begin();
			$this->__service->update_by_conds(array('id' => $id), array('status' => 2, 'reason' => $reason, 'reason_user' => $username, 'reason_time' => startup_env::get('timestamp') ));

			// 发送消息
			$paper = $this->__service->get($id);
			if($paper) {
				$m_uids = empty($paper['m_uids']) ? array() : explode(',', $paper['m_uids']);
				if(!empty($paper['cd_ids'])) {
					$m_uids = array_merge($m_uids, $this->get_uids_by_cdids(explode(',', $paper['cd_ids'])));
				}

				$m_uids = array_unique($m_uids);
				$s_member = new voa_s_oa_member();
				$members = $s_member->fetch_all_by_ids($m_uids);
				foreach($members as $member) {
					$tousers[] = $member['m_openid'];
				}

				$serv_qy = voa_wxqy_service::instance();
				$url = 'http://' . $domain . '/Exam/Frontend/Index/PaperDetail?paper_id=' . $paper['id'];
				$picurl = voa_h_attach::attachment_url($paper['cover_id']);
				$data = array(
					'title' => '【考试提醒】您有一门考试已提前终止',
					'description' => "试卷名称：{$paper['name']}\n原因：".msubstr($reason, 0, 60),
					'url' => $url,
					'picurl' => $picurl,
				);

				$serv_qy->post_news($data, $agentid, $tousers);
			}

			$this->__service->commit();
		} catch (Exception $e) {
			$this->__service->rollBack();
			return $this->set_errmsg(voa_errcode_oa_exam::DELETE_PAPER_FAILED);
		}
		return true;
	}

	/**
	 * 根据条件查找列表
	 * @param array $conds 条件数组
	 * @param int|array $pager 分页参数
	 */
	public function list_paper(&$result, $conds, $pager) {
		$result['list'] =  $this->_list_paper_by_conds($conds, $pager);
		$result['total'] = $this->_count_paper_by_conds($conds);
		return true;
	}

	protected function get_uids_by_cdids($cd_ids) {
		$m_uids = array();
		$s_md = new voa_s_oa_member_department();
		$tmp = $s_md->fetch_all_by_conditions(array('cd_id' => array($cd_ids, 'IN')));
		foreach ($tmp as $v) {
			$m_uids[] = $v['m_uid'];
		}

		return $m_uids;
	}

	/**
	 * 根据条件查找
	 * @param array $conds 条件数组
	 * @param int|array $pager 分页参数
	 * @return array $list
	 */
	protected function _list_paper_by_conds($conds, $pager) {
		$list = array();
		$list = $this->__service->list_by_conds($conds, $pager, array('id' => 'DESC'));
		$s_department = new voa_s_oa_common_department();
		$s_member = new voa_s_oa_member();
		if(!empty($list)) {
			foreach ($list as &$v) {
				if(!empty($v['begin_time'])) {
					$v['begin_time_show'] = rgmdate('Y-m-d H:i:s', $v['begin_time']);
					$v['end_time_show'] = rgmdate('Y-m-d H:i:s', $v['end_time']);
					$v['time'] = intval(($v['end_time'] - $v['begin_time']) / 60);
				}

				if($v['status'] == 0) {
					$v['status_show'] = '草稿';
				} elseif($v['status'] == 2) {
					$v['status_show'] = '已终止';
				} else {
					$currtime = time();
					if($currtime < $v['begin_time']) {
						$v['status_show'] = '未开始';
					} elseif($currtime > $v['end_time']) {
						$v['status_show'] = '已结束';
					} else {
						$v['status_show'] = '已开始';
					}
				}

				if($v['is_all'] == 1) {
					$v['departments'] = '全部人员';
				} else {
					if(!empty($v['cd_ids'])){
						$departments =  $s_department->fetch_all_by_cd_ids(explode(',', $v['cd_ids']));
						foreach ($departments as $value) {
							$v['departments'][] = $value['cd_name'];
						}
						$v['departments'] = implode(',', $v['departments']);
					}
					if(!empty($v['m_uids'])){
						$members = $s_member->fetch_all_by_ids(explode(',', $v['m_uids']));
						foreach ($members as $value) {
							$v['members'][] = $value['m_username'];
						}
						$v['members'] = implode(',', $v['members']);
					}


				}
			}
		}
		return $list;
	}

	/**
	 * 根据条件计算数据数量
	 * @param array $conds
	 * @return number
	 */
	protected function _count_paper_by_conds($conds) {
		$total = $this->__service->count_by_conds($conds);
		return $total;
	}

	/**
	 * 预览和上一部的默认字段
	 * @param array $conds
	 * @return number
	 */
	protected function _default_field(&$to, $fields, $from = array()) {
		foreach ($fields as $_k => $_f) {

			list($k, $type, $method, $method_err, $ignore_null) = (array)$_f;
			// 取 $k 对应的值
			$k = (string)$k;

			// 如果来源键值为数字, 则说明未指定来源键值
			if ($_k === (int)$_k) {
				$_k = $k;
			}

			$val = isset($from[$_k]) ? $from[$_k] : '';
			// 类型强制转换
			switch ($type) {
				case self::VAR_ARR: $val = empty($val) ? array() : (array)$val; break;
				case self::VAR_INT: $val = (int)$val; break;
				case self::VAR_STR: $val = (string)$val; break;
				case self::VAR_ABS: $val = (int)$val; $val < 0 && $val = 1; break;
				default: $val = (string)$val; break;
			}

			// 赋值
			$to[$k] = $val;

		}
	}

	private function get_tree_cd_cids($data, $id){
		$arr = array();
		foreach ( $data as $key=>$item ) {
			if ( $item['cd_upid']==$id){
				$arr[]=$item['cd_id'];
				unset($data[$key]);
				$arr = array_merge ( $arr, $this->get_tree_cd_cids( $data,$item['cd_id'] ) );
			}
		}
		return $arr;
	}
}
