<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/1/26
 * Time: 下午2:34
 * 应用纬度CRM数据
 */

namespace UcRpc\Service;

use Common\Model\MemberModel;
use Common\Model\CommonPluginModel;

class PluginCrmstatService extends AbstractService {

	/** 数量 */
	protected $_number = 0;
	/** 通讯录人员数量 */
	protected $_people_number = 0;
	/** 新增人员数量 */
	protected $_new_people_number = 0;
	/** 昨天的零点 */
	protected $_yesterday_time = 0;
	/** 今天的零点 */
	protected $_today_time = 0;
	/** 激活 */
	const ACTIVATION = 1;
	/** 未激活 */
	const UNACTIVATION = 0;

	// 构造方法
	public function __construct() {

		parent::__construct();
	}

	public function main() {

		$main = array();
		// 昨天和今天的零点
		$this->_today_time = rstrtotime(rgmdate(NOW_TIME, 'Y-m-d'), '00:00:00');
		$this->_yesterday_time = $this->_today_time - 86400;

		// 获取开启的应用
		$open_plugin_list = $this->_list_by_plugin_open();
		// 获取关注的人员数
		$this->_people_number = $this->_user_number();
		$main['mem_number'] = $this->_people_number;
		// 获取新增员工数 和 uid
		//		list($new_uid, $this->_new_people_number) = $this->_new_user_number();

		// 开启的应用数
		$main['open_plugin_number'] = count($open_plugin_list);
		// 获取主数据
		$this->_plugin_main_data_number($open_plugin_list);
		$main['main_data_number'] = $this->_number;

		// 获取总数据
		$this->_single_plugin_total($open_plugin_list);
		$main['plugin_total'] = $this->_number;

		// 总数据人均贡献量
		$main['capita_contribution'] = round($this->_number / $this->_people_number, 4);
		// 安装的应用
		$main['install_plugin'] = $open_plugin_list;

		//已关注员工
		$main['attention'] = $this->get_attention('attention');

		//未关注员工
		$main['unattention'] = $this->get_attention('unattention');

		return $main;
	}

	/**
	 * 获取已关注人员的数量
	 * @return mixed
	 */
	public function get_attention($act) {

		$member = D('Common/Member');

		if ($act == 'attention') {
			$conds_mem['m_qywxstatus'] = MemberModel::QYSTATUS_SUBCRIBE;
		} elseif ($act == 'unattention') {
			$conds_mem['m_qywxstatus'] = MemberModel::QYSTATUS_UNSUBCRIBE;
		}

		//统计数量
		$count = $member->count_by_conds($conds_mem);

		return $count;
	}

	/**
	 * 获取开启的应用
	 * @return int
	 */
	protected function _list_by_plugin_open() {

		$serv_plugin = D('Common/CommonPlugin', 'Service');
		$list = $serv_plugin->list_by_conds(array('cp_available' => CommonPluginModel::AVAILABLE_OPEN));

		return $list;
	}

	/**
	 * 获取应用主数据
	 * @return int
	 */
	protected function _plugin_main_data_number(&$list) {

		$this->_number = 0;

		/** 获取所有已开启的应用主数据 */
		// 不规则的应用主数据存放(文件名不规则，有多个主数据表)
		$other = array(
			'sign' => array(
				'SignRecord',
				'SignLocation',
				'SignDetail',
			),
			'chatgroup' => array(
				'ChatgroupRecord',
			),
			'train' => array(
				'TrainArticle',
			),
			'showroom' => array(
				'ShowroomArticle',
			),
			'invite' => array(
				'InvitePersonnel',
			),
			'blessingredpack' => array(
				'BlessingRedpack',
			),
			'exam' => array(
				'ExamPaper',
			),
			'jobtrain' => array(
				'JobtrainArticle',
			),
			'reimburse' => array(
				'Reimburse',
				'ReimburseBill',
			),
		);
		// 排除在统计外
		/*$exclude = array(
			'redpack',
			'registration',
			'conference',
			'travel',
		);*/
		$exclude = array(
			'namecard', // => '名片夹',
			'weekreport', // => '周报',
			'todo',  //=> '代办事项',
			'vote', // => '微评选',
			'plan', // => '日程报告',
			'secret', // => '秘密',
			'notice', // => '通知公告',
			'footprint', // => '销售轨迹',
			'file', // => '文件',
			'productive', // => '活动反馈',
			'travel', // => '微分销',
			'train', // => '培训',
			'superreport', // => '超级报表',
			'redpack', // => '企业红包',
			'sale', // => '销售管理',
		);
		// 表字段前缀
		$prefield = array(
			'project' => array(
				'Project' => 'p_',
			),
			'minutes' => array(
				'Minutes' => 'mi_',
			),
			'vnote' => array(
				'Vnote' => 'vn_',
			),
			'askfor' => array(
				'Askfor' => 'af_',
			),
			'reimburse' => array(
				'Reimburse' => 'rb_',
				'ReimburseBill' => 'rbb_',
			),
			'dailyreport' => array(
				'Dailyreport' => 'dr_',
			),
			'sign' => array(
				'SignRecord' => 'sr_',
				'SignLocation' => 'sl_',
				'SignDetail' => 'sd_',
			),
			'meeting' => array(
				'Meeting' => 'mt_',
			),
			'askoff' => array(
				'Askoff' => 'ao_',
			),
			'inspect' => array(
				'Inspect' => 'ins_',
			),
			'chatgroup' => array(
				'ChatgroupRecord' => 'cgr_',
			),
		);

		// 开始统计
		foreach ($list as &$_plugin) {
			// 如果被排除
			if (in_array($_plugin['cp_identifier'], $exclude)) {
				continue;
			}
			if ($_plugin['cp_identifier'] == 'addressbook') {
				//单独统计
				$model_addressbook = D('Common/Member');
				// 获取当天数据
				$time_cond = array(
					'm_created > ?' => $this->_yesterday_time,
					'm_created < ?' => $this->_today_time,
				);
				$main_data = $model_addressbook->count_by_conds($time_cond);
				$_plugin['main_data_number'] = $main_data;
				$this->_number += $_plugin['main_data_number'];
				// 获取之前的数据
				$ago_time_cond = array(
					'm_created <?' => $this->_yesterday_time,
				);
				$ago_num = $model_addressbook->count_by_conds($ago_time_cond);

				// 判断是否刚使用(激活)
				if ($_plugin['main_data_number'] > 0 && $ago_num == 0) {
					$_plugin['is_activation'] = self::ACTIVATION;
				} else {
					$_plugin['is_activation'] = self::UNACTIVATION;
				}
				continue;
			}
			$plugin_name = ucfirst($_plugin['cp_identifier']);
			$main_data_number = 0;
			// 如果在不规则的应用范围里
			if (isset($other[$_plugin['cp_identifier']])) {
				// 应用特例
				if ($plugin_name == 'Blessingredpack') {
					$plugin_name = 'BlessingRedpack';
				}
				if ($plugin_name == 'Chatgroup') {
					$plugin_name = 'ChatGroup';
				}

				//多个主数据表
				foreach ($other[$_plugin['cp_identifier']] as $_table_name) {
					$service = D($plugin_name . '/' . $_table_name);

					// 是否有表字段前缀
					$created_field = 'created';
					if (isset($prefield[$_plugin['cp_identifier']][$_table_name])) {
						$created_field = $prefield[$_plugin['cp_identifier']][$_table_name] . 'created';
					}

					\Think\Log::record(var_export($plugin_name, true));

					// 获取当天数据
					$time_conds = array(
						$created_field . ' > ?' => $this->_yesterday_time,
						$created_field . ' < ?' => $this->_today_time,
					);
					if ($_table_name == 'Reimburse' || $_table_name == 'ReimburseBill') {
						$number = $service->count_num();
					} else {
						$number = $service->count_by_conds($time_conds);
					}

					// 应用主数据
					$main_data_number += $number;
					$_plugin['main_data_number'] = $main_data_number;
				}

			} else {
				if ($plugin_name == 'Chatgroup') {
					$service = D('ChatGroup/' . $plugin_name);
					$plugin_name = 'ChatGroup';
				} else {
					$service = D($plugin_name . '/' . $plugin_name);
				}

				// 是否有表字段前缀
				$created_field = 'created';
				//第一个代表主表
				if (isset($prefield[$_plugin['cp_identifier']][$plugin_name])) {
					$created_field = $prefield[$_plugin['cp_identifier']][$plugin_name] . 'created';
				}
				\Think\Log::record(var_export($plugin_name, true));

				// 获取当天数据
				$time_conds = array(
					$created_field . ' > ?' => $this->_yesterday_time,
					$created_field . ' < ?' => $this->_today_time,
				);
				$number = $service->count_by_conds($time_conds);

				// 应用主数据
				$_plugin['main_data_number'] = $number;

			}
			// 获取之前的数据
			$ago_time_conds = array(
				$created_field . '<?' => $this->_yesterday_time,
			);
			$ago_number = $service->count_by_conds($ago_time_conds);

			// 判断是否刚使用(激活)
			if ($_plugin['main_data_number'] > 0 && $ago_number == 0) {
				$_plugin['is_activation'] = self::ACTIVATION;
			} else {
				$_plugin['is_activation'] = self::UNACTIVATION;
			}

			unset($created_field);
			unset($is_activation);
			// 总主数据
			$this->_number += $_plugin['main_data_number'];
		}

		return true;
	}

	/**
	 * 获取企业应用总数据
	 * @return int
	 */
	protected function _single_plugin_total(&$list) {

		// 现有的应用扩展表
		$rule = array(
			// 活动
			'activity' => array(
				//'ActivityInvite',
				//'ActivityNopartake',
				//'ActivityOutsider',
				'ActivityPartake',
			),
			// 任务
			'project' => array(
				//'ProjectCopy',
				//'ProjectDraft',
				//'ProjectMem',
				'ProjectProc',
			),
			// 会议记录
			'minutes' => array(
				//'MinutesDraft',
				//'MinutesMem',
				'MinutesPost',
			),
			// 备忘录
			'vnote' => array(
				//'VnoteDraft',
				'VnoteMem',
				//'VnotePost',
			),
			// 审批
			'askfor' => array(
				//				'AskforComment',
				//'AskforCustomdata',
				//'AskforDraft',
				'AskforProc',
				//'AskforProcRecord',
				//				'AskforReply',
				//'AskforTemplate',
			),
			// 同事社区
			'thread' => array(
				//'ThreadLikes',
				//'ThreadPermitUser',
				'ThreadPost',
				'ThreadPostReply',
			),
			// 报销
			'reimburse' => array(
				//'ReimburseBill',
				//'ReimburseBillSubmit',
				//'ReimburseDraft',
				//'ReimbursePost',
				'ReimburseProc',
			),
			// 工作报告
			'dailyreport' => array(
				//'DailyreportDraft',
				//'DailyreportMem',
				'DailyreportPost',
				//'DailyreportRead',
			),
			// 考勤
			/*'sign' => array(
				//'SignBatch',
				//'SignDepartment',
				//'SignDetail',
				//'SignLocation',
				//'SignRecordLocation',
			),*/
			// 订会议室
			'meeting' => array(
				//'MeetingDraft',
				'MeetingMem',
				//'MeetingRoom',
			),
			// 请假
			/*'askoff' => array(
				'AskoffDraft',
				'AskoffPost',
				'AskoffProc',
			),*/
			// 巡店
			'inspect' => array(
				//'InspectDraft',
				//'InspectItem',
				//'InspectMem',
				'InspectScore',
				//'InspectTasks',
				//'InspectTasksLog',
			),
			// 移动派单
			'workorder' => array(
				//'WorkorderDetail',
				'WorkorderLog',
				//'WorkorderReceiver',
			),
			// 培训
			/*'train' => array(
				'TrainArticleContent',
				'TrainArticleMember',
				'TrainArticleRight',
				'TrainArticleSearch',
			),*/
			// 陈列
			/*'showroom' => array(
				'ShowroomArticleContent',
				'ShowroomArticleMember',
				'ShowroomArticleRight',
				'ShowroomArticleSearch',
			),*/
			// 新闻公告
			'news' => array(
				//'NewsCategory',
				//'NewsCheck',
				//'NewsComment',
				//'NewsContent',
				'NewsLike',
				'NewsRead',
				//'NewsRight',
				//				'NewsRight2',
			),
			// 投票调研
			'nvote' => array(
				//'NvoteDepartment',
				'NvoteMem',
			),
			// 快递助手
			'express' => array(
				'ExpressMem',
			),
			// 红包
			'blessingredpack' => array(
				//'BlessingRedpackDepartment',
				'BlessingRedpackLog',
				//'BlessingRedpackMember',
			),
			// 同事聊天
			/*'chatgroup' => array(
				//'ChatgroupMember',
				'ChatgroupRecord',
			),*/
			//考试
			'exam' => array(
				//'ExamPaper',
				//'ExamPaperDetail',
				//'ExamTi',
				'ExamTiTj',
				//'ExamTiku',
				//'ExamTj',
			),
			//培训
			'jobtrain' => array(
				//'JobtrainArticle',
				//'JobtrainCategory',
				'JobtrainColl',
				'JobtrainComment',
				'JobtrainCommentZan',
				//'JobtrainRight',
				//'JobtrainStudy',
			)
			// 邀请人员(暂无)
		);
		// 有表前缀
		$prefield = array(
			'project' => array(
				//'ProjectCopy' => 'p_',
				//'ProjectDraft' => 'pd_',
				//'ProjectMem' => 'pm_',
				'ProjectProc' => 'pp_',
			),
			'minutes' => array(
				//'MinutesDraft' => 'mid_',
				//'MinutesMem' => 'mim_',
				'MinutesPost' => 'mip_',
			),
			'vnote' => array(
				//'VnoteDraft' => 'vnd_',
				'VnoteMem' => 'vnm_',
				//'VnotePost' => 'vnp_',
			),
			'askfor' => array(
				//				'AskforComment' => 'afc_',
				//'AskforCustomdata' => 'afcd_',
				'AskforProc' => 'afp_',
				//'AskforProcRecord' => 'rafp_',
				//				'AskforReply' => 'afr_',
				'AskforTemplate' => 'aft_',
			),
			'reimburse' => array(
				//'ReimburseBill' => 'rbb_',
				//'ReimburseBillSubmit' => 'rbbs_',
				//'ReimburseDraft' => 'rbd_',
				//'ReimbursePost' => 'rbpt_',
				'ReimburseProc' => 'rbpc_',
			),
			'dailyreport' => array(
				'DailyreportDraft' => 'drd_',
				'DailyreportMem' => 'drm_',
				'DailyreportPost' => 'drp_',
			),
			'meeting' => array(
				//'MeetingDraft' => 'mtd_',
				'MeetingMem' => 'mm_',
				//'MeetingRoom' => 'mr_',
			),
			/*'askoff' => array(
				'AskoffDraft' => 'aod_',
				'AskoffPost' => 'aopt_',
				'AskoffProc' => 'aopc_',
			),*/
			'inspect' => array(
				//'InspectDraft' => 'insd_',
				//'InspectItem' => 'insi_',
				//'InspectMem' => 'insm_',
				'InspectScore' => 'isr_',
				//'InspectTasks' => 'it_',
				//'InspectTasksLog' => 'it_',
			),
			'chatgroup' => array(
				'ChatgroupMember' => 'cgm_',
				'ChatgroupRecord' => 'cgr_',
			),
			/*'sign' => array(
				'SignLocation' => 'sl_',
				'SignDetail' => 'sd_',
			),*/
		);

		// 遍历开启的应用
		foreach ($list as &$_plugin) {
			//不统计的应用
			if (in_array($_plugin['cp_identifier'], array(
				'namecard', // => '名片夹',
				'weekreport', // => '周报',
				'todo',  //=> '代办事项',
				'vote', // => '微评选',
				'plan', // => '日程报告',
				'secret', // => '秘密',
				'notice', // => '通知公告',
				'footprint', // => '销售轨迹',
				'file', // => '文件',
				'productive', // => '活动反馈',
				'travel', // => '微分销',
				'train', // => '培训',
				'superreport', // => '超级报表',
				'redpack', // => '企业红包',
				'sale', // => '销售管理',
			))) {
				continue;
			}
			//通讯录单独统计
			if ($_plugin['cp_identifier'] == 'addressbook') {
				$model_add = D('Common/Member');
				$time_cond = array(
					'm_updated > ?' => $this->_yesterday_time,
					'm_updated < ?' => $this->_today_time,
				);
				$_plugin['plugin_total'] = $model_add->count_by_conds($time_cond);
				$this->_number = $this->_number + $_plugin['plugin_total'] - $_plugin['main_data_number'];
				// 应用人均贡献量
				$_plugin['capita_contribution'] = round($_plugin['plugin_total'] / $this->_people_number, 4);

				continue;
			}
			$plugin_name = ucfirst($_plugin['cp_identifier']);

			if ($plugin_name == 'Chatgroup') {
				$plugin_name == 'ChatGroup';
			}
			$plugin_total = 0;
			// 获取扩展表数据
			if (isset($rule[$_plugin['cp_identifier']])) {
				foreach ($rule[$_plugin['cp_identifier']] as $_table) {
					if ($plugin_name == 'Blessingredpack') {
						$plugin_name = 'BlessingRedpack';
					}
					if ($plugin_name == 'Chatgroup') {
						$service = D('ChatGroup/' . $_table);
					} else {

						$service = D($plugin_name . '/' . $_table);
					}

					\Think\Log::record(var_export($_table, true));
					$created_field = 'created';
					// 是否有表前缀
					if (isset($prefield[$_plugin['cp_identifier']]) && !empty($prefield[$_plugin['cp_identifier']][$_table])) {
						$created_field = $prefield[$_plugin['cp_identifier']][$_table] . 'created';
					}
					// 获取当天数据
					$time_conds = array(
						$created_field . '>?' => $this->_yesterday_time,
						$created_field . '<?' => $this->_today_time,
					);
					//任务应用特殊条件
					if ($_table == 'ProjectProc') {
						$time_conds['pp_status > ?'] = 1;
					}
					if ($_table == 'AskforProc') {
						$time_conds['afp_condition >?'] = 1;
					}

					if ($_table == 'NvoteMem') {
						$time_conds = array(
							'updated > ?' => $this->_yesterday_time,
							'updated < ?' => $this->_today_time,
						);
					}
					if ($_table == 'ActivityPartake') {
						$time_conds = array(
							'updated > ?' => $this->_yesterday_time,
							'updated < ?' => $this->_today_time,
						);
					}
					if ($_table == 'ExpressMem') {
						$time_conds = array(
							'updated > ?' => $this->_yesterday_time,
							'updated < ?' => $this->_today_time,
						);
					}

					//报销
					if ($_table == 'ReimburseProc' || $_table == 'MeetingMem') {
						$number = $service->count_data();
					} else {
						$number = $service->count_by_conds($time_conds);
					}
					// 应用总数据
					$plugin_total += $number;
					$_plugin['plugin_total'] = $plugin_total;
					// 统计总数据
					$this->_number += $number;
				}
				//扩展表总数据加上每个应用的主数据
				$_plugin['plugin_total'] += $_plugin['main_data_number'];
				// 应用人均贡献量
				$_plugin['capita_contribution'] = round($_plugin['plugin_total'] / $this->_people_number, 4);
			} else {
				//主数据等于总数据的应用
				$_plugin['plugin_total'] = $_plugin['main_data_number'];
				$_plugin['capita_contribution'] = round($_plugin['plugin_total'] / $this->_people_number, 4);
			}
			unset($plugin_total, $number);
		}

		return true;
	}

	/**
	 * 获取应用活跃用户数量
	 * @return int
	 */
	protected function _active_user_number() {


		return true;
	}

	/**
	 * 获取通讯录人数
	 * @return mixed
	 */
	protected function _user_number() {

		$serv_mem = D('Common/Member');
		//		$conds = array(
		//			'm_qywxstatus' => MemberModel::QYSTATUS_UNSUBCRIBE,
		//		);
		$count = $serv_mem->count();

		return $count;
	}

	/**
	 * 新增员工uid 和 人数
	 * @return array
	 */
	protected function _new_user_number() {

		$serv_mem = D('Common/Member');
		$conds = array(
			'm_created>?' => $this->_yesterday_time,
			'm_created<?' => $this->_today_time,
			//			'm_qywxstatus' => MemberModel::QYSTATUS_UNSUBCRIBE,
		);
		$list = $serv_mem->list_by_conds($conds);
		$new_uid = array_column($list, 'm_uid');
		$count = count($new_uid);

		return array(
			$new_uid,
			$count,
		);
	}
}
