<?php
/**
 * @Author: ppker
 * @Date:   2015-08-07 16:06:35
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-08-21 19:42:26
 * @Description 更新付费状态
 */
class voa_backend_cron_upstatus extends voa_backend_base {
	/** 参数 */
	private $__opts = array();
	// 数据库连接
	protected $_db;

	// 构造函数
	public function __construct($opts) {
		parent::__construct();
		$this->__opts = $opts;
	}

	// 各个状态数组数据
	protected $__array_status = array(
		1 => '已付费',
		2 => '已付费-即将到期',
		3 => '已付费-已到期',
		5 => '试用期-即将到期',
		6 => '试用期-已到期',
		7 => '试用期'
	);


	/**
	 * 入口函数
	 *
	 * @access public
	 * @return void
	 */
	public function main() {
		/** 连接数据库 */
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$this->_db = db::init($cfg);

		// 调用数据库
		$this->_db->query('USE vchangyi_cyadmin');
		$this->_db->query('SET NAMES "utf8"');
		$app_data = array(); // 应用设置数据
		$app_data = $this->mysql_get("SELECT * FROM cy_enterprise_appset"); 

		$now_time = time(); // 当前时间  
		// var_dump($now_time);die;
		$pro_conds = array( // 下标与我的得一一对应
			0 => 7, // 试用期
			1 => 5, // 试用期即将到期
			2 => 6, // 试用期已经到期
			//3 => 4, // 未付费
			3 => 1, // 已付费
			4 => 2, // 已付费即将到期
			5 => 3 // 已付费已到期
		);

		// 需要的数据 设置
		foreach ($app_data as $key => $v) {

			//var_dump(time());die;
			if($v['key'] == "syq_jjdq_set"){
				$sz_day = $v['value'] * 86400; // 试用期-即将到期 设置的天数 转化成时间戳	
			}elseif ($v['key'] == 'trydate') {
				$qx_day = $v['value'] * 86400; // 试用期限-时间戳
			}elseif ($v['key'] == 'yff_jjdq_set') {
				$yff_day = $v['value'] * 86400; // 已付费-即将到期--时间戳
			}

		}

		try {

			//var_dump(time()-86400*10-1000);die;

			// 循环判断各种付费状态 和当前时间进行对比 然后对符合条件的企业进行批量更新状态
			foreach ($pro_conds as $k1 => $v1) {
				if($k1 == 0){ // 试用期  都是从试用期开始的, 试用期转成 试用期即将到期
					$real_time = $now_time - $sz_day; //条件时间戳
					$sql = sprintf("select `pay_id` from cy_company_paysetting where `pay_status` = %u and date_start <= %u and `status` < 3 ORDER BY pay_id DESC", $v1, $real_time);
					// 批量更新状态
					// var_dump($sql);die;
					$re = $this->go_update($sql, $pro_conds[$k1+1], $v1, $this->__array_status);

				}elseif($k1 == 1){ //试用期即将到期 改成试用期已到期
					// 条件时间戳
					$real_time = $now_time;
					$sql = sprintf("select `pay_id` from cy_company_paysetting where `pay_status` = %u and date_end <= %u and `status` < 3 ORDER BY pay_id DESC", $v1, $real_time);
					//var_dump($sql);die;
					// 批量更新状态
					$re = $this->go_update($sql, $pro_conds[$k1+1], $v1, $this->__array_status);

				}elseif($k1 == 3){ // 已付费的 状态更新，更新到已付费即将到期
					// 条件时间戳
					$real_time = $now_time - $yff_day;
					$sql = sprintf("select `pay_id` from cy_company_paysetting where `pay_status` = %u and date_start <= %u and `status` < 3 ORDER BY pay_id DESC", $v1, $real_time);
					// 批量更新状态
					//var_dump($sql);die;
					$re = $this->go_update($sql, $pro_conds[$k1+1], $v1, $this->__array_status);

				}elseif($k1 == 4){ // 已付费即将到期 状态更新，更新到已付费-已到期
					// 条件时间戳
					$real_time = $now_time;
					$sql = sprintf("select `pay_id` from cy_company_paysetting where `pay_status` = %u and date_end <= %u and `status` < 3 ORDER BY pay_id DESC", $v1, $real_time);
					// 批量更新状态
					// var_dump($sql);die;
					$re = $this->go_update($sql, $pro_conds[$k1+1], $v1, $this->__array_status);
				}

			}

		}catch (Exception $e) {
			$this->_log($e->getMessage());
		}


	}

	/**
	 * [mysql_get myql运行sql获取数据结果]
	 * @param  [type] $sql [description]
	 * @return [type]      [description]
	 */
	public function mysql_get($sql){
		$resource = $this->_db->query($sql);
		$re_data = array();
		while($Row = mysql_fetch_assoc($resource)){
			$re_data[] = $Row;
		}
		return $re_data;
	}
	
	/**
	 * [go_update 对符合条件的数据进行更新各种状态]
	 * @param  [type] $sql [description]
	 * @return [type]      [description]
	 */
	public function go_update($sql, $new_statue, $v1, $array_status){

		$pay_status0 = array(); // 满足企业的数据集
		$pay_status0 = $this->mysql_get($sql);
		$pay_id_array = array_column($pay_status0,'pay_id');
		$re = '';
		foreach ($pay_id_array as $kk1 => $vv1) {
			$sql = sprintf("UPDATE `cy_company_paysetting` SET `pay_status`=%u WHERE (`pay_id`=%u and `pay_status` = %u)", $new_statue, $vv1, $v1);
			//var_dump($sql);die;
			$re = $this->_db->query($sql); // 如果不成功 则进行的提示？
			$do_log = "记录ID：". $vv1 ."; 操作时间：".date('Y-m-d H:i:s')." ; 操作前状态：".$array_status[$v1]."----操作后状态：".$array_status[$new_statue];
			// 记录日志
			logger::log($do_log, logger::LOGGER_LEVEL_ERROR);
		}
		return $re;
	}

}
