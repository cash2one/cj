<?php
/**
 * Created by PhpStorm.
 * 活动提醒
 * User: xbs
 * Date: 16/2/22
 * Time: 21:24
 */

namespace UcRpc\Service;
use Common\Common\Cache;
use Common\Common\WxqyMsg;
use Common\Model\MemberModel;
use Think\Log;

class EventRemindService extends AbstractService {

	// 构造方法
	public function __construct() {
		parent::__construct();

	}

	/**
	 * 活动提醒
	 * @param int $in
	 */
	public function eventRemind($in='2') {

		Log::record('活动提醒开始', Log::INFO);

		try{
			$model_plugin = D('Common/CommonPlugin');
			$serv_e = D("Event/Event");
			$serv_p = D("Event/EventPartake");

			// 读取插件信息
			$plugin = $model_plugin->get_by_identifier('event');

			// 如果 agentid 为空
			if (empty($plugin['cp_agentid'])) {
				return true;
			}

			// 更新 pluginid, agentid 配置
			cfg('pluginid', $plugin['cp_pluginid']);
			cfg('agentid', $plugin['cp_agentid']);

			//系统后时间
			$settingTime = '+' . ($in-1) . ' day';
			$nextstartdayTimeTamp = strtotime(date('Y-m-d',strtotime($settingTime)));
			if($in == 2){
				$nextstartdayTimeTamp = strtotime(date('Y-m-d', time()));
			}
			$settingTime = '+' . $in . ' day';
			$nextenddayTimeTamp = strtotime(date('Y-m-d',strtotime($settingTime)));

			//获取明天的未结束的活动
			$orderby = array('start_time' => 'ASC');
			$eventtivityresult = $serv_e->fetch_next_date_list($nextstartdayTimeTamp, $nextenddayTimeTamp);
			foreach($eventtivityresult as $val){
				//查询报名人员
				$conds = array(
					'acid' => $val['eid'],
					'type' => '1,4'
				);
				$eventUserList = $serv_p->list_by_params($conds);
				$eventUidList = array_column($eventUserList, 'm_uid');
				////封装推送数据
				$arry = array(
					'eid' => $val['eid'],
					'title' => $val['title'],//活动主题
					'start_date' => date('m-d H:i', $val['start_time']),//活动开始时间
					'start_end_date' => date('m-d H:i', $val['end_time']),//活动结束时间
					'address' => $val['province'].$val['city'].$val['area'].$val['street'],
					'img' => $this->_get_attachment($val['thumb']),
					'pluginid' => $plugin['cp_pluginid'],
					'agentid' => $plugin['cp_agentid']
				);
				$this->__send($eventUidList, $arry);
			}

		}catch(\Exception $e){
			Log::record('活动提醒异常：', Log::ERR);
			Log::record($e->getMessage(), Log::ERR);
			return false;
		}
	}

	/**
	 * 发送消息
	 * @param $uids
	 * @param $arry
	 * @return bool
	 */
	private function __send($uids, $arry) {

		Log::record('活动消息推送开始(crontab)------活动ID：' . $arry['eid'], Log::INFO);

		$wxqyMsg = WxqyMsg::instance();

		$cache         = &\Common\Common\Cache::instance();
		$sets          = $cache->get('Common.setting');
		$face_base_url = cfg('PROTOCAL') . $sets ['domain'];
		$url = $face_base_url . "/previewh5/micro-community/index.html#/app/page/activity/activity-detail?id={$arry['eid']}";

		$desc = "活动时间:".$arry['start_date']. '到'.$arry['start_end_date']."\n活动地点:".$arry['address'];

		/*推送指定人员*/
		$speed = 100;//每次插入100条
		$batch_count = 0;
		$size = count($uids);
		do{

			$from = $batch_count * $speed;
			$batch_count++;
			$tmp = array_slice($uids, $from, $speed, true);

			$result = $wxqyMsg->send_news('[活动]'.$arry['title'], $desc, $url, $tmp, '', $arry['img'], $arry['agentid'], $arry['pluginid']);

			Log::record('活动推送指定人员结果(crontab),result-------' . $result, Log::INFO);

		} while ($size > ($speed * $batch_count));

		return true;
	}

	/**
	 * 封面
	 * @param $atid
	 * @return mixed
	 */
	protected function _get_attachment($atid){

		$at_id = (int)$atid;
		if(!$at_id){
			return false;
		}

		//组合URL
		$cache = &\Common\Common\Cache::instance();
		$sets = $cache->get('Common.setting');
		$face_base_url = cfg('PROTOCAL') . $sets ['domain'];
		$face_base_url = rtrim($face_base_url,'/');
		$attach_url = $face_base_url.'/attachment/read/'.$at_id;

		return $attach_url;
	}


}
