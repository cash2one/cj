<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 下午1:13
 */
namespace Home\Service;

class StatMemberAllService extends AbstractService {

	public function __construct() {

		parent::__construct();
		$this->_d = D('Home/StatMemberAll');
		$this->_d_company = D('Home/StatCompany');
	}

	/*
	 * 读取昨日数据
	 * @param $epid 公司id
	 * @param $time 当前时间戳
	 */
	public function list_by_conds_lastlay($ep_id){


		// 获取昨日数据
		$conds = array(
			'time<?' => $this->_tomorrow_time,
			'time>?' => $this->_today_time,
		);
		$yestoday_data = $this->_d->list_by_conds_lastlay($ep_id,$conds);

		// 获取前天数据
		$conds = array(
			'time<?' => $this->_yesterday_time,
			'time>?' => $this->_today_time,
		);
		$before_yestaday = $this->_d->list_by_conds_lastlay($ep_id,$conds);

		return $this->format_lastday($yestoday_data,$before_yestaday);

	}

	/*
	 * 读取数据详情
	 */
	public function list_by_conds_detail($ep_id,$page_option){

		$this->format_detail();
		$this->_d->list_by_conds_detail($ep_id,$page_option);
	}

	/*
	 * 格式昨日数据
	 */
	public function format_lastday($yestaday,$befor){

		//新增员工数百分比
		$add_percent = (($befor['add'] - $yestaday['add'])/100).'%';
		//活跃员工数百分比
		$active_percent = (($befor['active_count'] - $yestaday['active_count'])/100).'%';
		//以关注员工数百分比
		$attention_percent = (($befor['attention'] - $yestaday['attention'])/100).'%';
		//未关注员工数百分比
		$unattention_percent = (($befor['unattention'] - $yestaday['unattention'])/100).'%';
		//企业员工总数百分比
		$all_percent = (($befor['all'] - $yestaday['all'])/100).'%';

		$list = array(

			'header' => array(
				array(
					'name' => '新增员工数',
					'count' => $yestaday['add'],
					'percent' => $add_percent
				),
				array(
					'name' => '活跃员工数',
					'count' => $yestaday['active_count'],
					'percent' => $active_percent
				),
				array(
					'name' => '以关注员工数',
					'count' => $yestaday['attention'],
					'percent' => $attention_percent
				),
				array(
					'name' => '未关注员工数',
					'count' => $yestaday['unattention'],
					'percent' => $unattention_percent
				),
				array(
					'name' => '企业员工总数',
					'conut' => $yestaday['all'],
					'percent' => $all_percent
				)

			)
		);

		return $list;
	}

	/*
	 * 格式数据详情
	 */
	public function format_detail(){


	}




}