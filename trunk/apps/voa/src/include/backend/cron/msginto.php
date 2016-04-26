<?php
/**
 * @Author: ppker
 * @Date:   2015-08-07 16:06:35
 * @Last Modified by:   ChangYi
 * @Last Modified time: 2015-10-20 23:53:10
 * @QQ 1366525100
 * @Description 发送消息
 */
class voa_backend_cron_msginto extends voa_backend_base {
	/** 参数 */
	private $__opts = array();
	// 数据库连接
	protected $_db;

	// 构造函数
	public function __construct($opts) {
		parent::__construct();
		$this->__opts = $opts;
	}

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

		//var_dump($app_data);die;

		$now_time = time(); // 当前时间 

		// var_dump($now_time);die; 
		$now_time = date("Y-m-d",$now_time);

		$left_time = strtotime($now_time . " 00:00:00"); // 当天0点的时间
		$right_time = strtotime($now_time . " 23:59:59")+1; // 当天23点59分59秒的时间
		
		$mb_message = array(); // 模板数据
		$mb_message = $this->mysql_get("SELECT * FROM cy_enterprise_message ORDER BY meid DESC");


		$notice_data = array();
		$sz_day = null; // 试用期-即将到期
		$qx_day = null; // 试用期限
		$yff_day = null; // 已付费即将到期

		foreach ($app_data as $key => $v) {
			if($v['key'] == "notice"){
				$notice_data = unserialize($v['value']); // 应用设置的数据
				//continue;
			}elseif ($v['key'] == "syq_jjdq_set") {
				$sz_day = $v['value'] * 86400; // 试用期-即将到期 设置的天数 转化成时间戳	
				//continue;
			}elseif ($v['key'] == 'trydate') {
				$qx_day = $v['value'] * 86400; // 试用期限
				//continue;
			}elseif ($v['key'] == 'yff_jjdq_set') {
				$yff_day = $v['value'] * 86400; // 已付费-即将到期
				//continue;
			}
			

		}

		//------------------------ 各种付费状态 循环遍历 发送消息
		$pro_conds = array( // 下标与我的得一一对应
			0 => 7, // 试用期
			1 => 5, // 试用期即将到期
			2 => 6, // 试用期已经到期
			//3 => 4, // 未付费
			3 => 1, // 已付费
			4 => 2, // 已付费即将到期
			5 => 3 // 已付费已到期
		);
		$mb_title = array_column($mb_message, 'title', 'meid'); // 转化数据 方便获取
		try {
			if($notice_data['notice_state']){
				$insert_message_syq = array('oa' => array(), 'back_oa' => array()); // 试用期的数据
				$insert_message_syq_jjdq = array('oa' => array(), 'back_oa' => array()); // 试用期即将到期的数据
				$insert_message_syq_ydq = array('oa' => array(), 'back_oa' => array()); // 试用期已到期的数据
				$insert_message_yff = array('oa' => array(), 'back_oa' => array()); // 已付费的数据
				$insert_message_yff_jjdq = array('oa' => array(), 'back_oa' => array()); // 已付费即将到期的数据
				$insert_message_yff_ydq = array('oa' => array(), 'back_oa' => array()); // 已付费已到期的数据


				foreach ($notice_data['notice_state'] as $kk => $vv_status) {
					// 每种情况对应的数据
					$notice_mod = $notice_data['notice_mod'][$kk]; // 对应的套件id
					$k_day = $notice_data['agodate'][$kk]; // 对应的前多少天
					$mb_id = $notice_data['meid'][$kk]; // 对应的消息模板
					$trydate_time = $k_day * 86400; // 设置的前多少天的 时间戳

					if($vv_status == 0){ // 试用期的情况,是 后几天的
						// 试用期发消息
						$real_left_time = $left_time - $trydate_time;

						$real_right_time = $right_time - $trydate_time;
						// 还要考虑到套件的结合 cpg_id 若等于0 则是试用期的情况,所以要分情况来搞定

						$sql = sprintf("select `ep_id` from cy_company_paysetting where `pay_status` = %u and date_start BETWEEN %s AND %s and `status` < 3 ORDER BY pay_id DESC", $pro_conds[$vv_status], $real_left_time, $real_right_time);
						// 生产数据,试用期的data
						$insert_message_syq = $this->get_syq_data($sql, $mb_id, $mb_title, $pro_conds[$vv_status], $notice_mod); //试用期的数据

					}elseif($vv_status == 1){ //试用期即将到期 
						// 试用期即将到期 前多少天  此时还是 试用期啊
						// 组装查询条件
						

						$real_left_time = $left_time - $sz_day + $trydate_time;
						$real_right_time = $right_time - $sz_day + $trydate_time;
						$sql = sprintf("select `ep_id` from cy_company_paysetting where `pay_status` = %u and date_start BETWEEN %s AND %s and `status` < 3 ORDER BY pay_id DESC", $pro_conds[$vv_status-1], $real_left_time, $real_right_time);
						
						$insert_message_syq_jjdq = $this->get_syq_data($sql, $mb_id, $mb_title, $pro_conds[$vv_status], $notice_mod); // 试用期-即将到期数据

					}elseif($vv_status == 2){ // 试用期已到期
						// 试用期已到期 前多少天 此时还是试用期即将到期的状态
						// 组装查询条件
						$real_left_time = $left_time - $qx_day + $trydate_time;
						$real_right_time = $right_time - $qx_day + $trydate_time;

						$sql = sprintf("select `ep_id` from cy_company_paysetting where `pay_status` = %u and date_start BETWEEN %s AND %s and `status` < 3 ORDER BY pay_id DESC", $pro_conds[$vv_status-1], $real_left_time, $real_right_time);						
						$insert_message_syq_ydq = $this->get_syq_data($sql, $mb_id, $mb_title, $pro_conds[$vv_status], $notice_mod); // 试用期-已到期的数据

					}elseif($vv_status == 3){ //已付费啊已付费 后多天
						// 组装查询条件
						$real_left_time = $left_time - $trydate_time;
						$real_right_time = $right_time - $trydate_time;

						$sql = sprintf("select `ep_id` from cy_company_paysetting where `pay_status` = %u and `cpg_id` = %u and date_start BETWEEN %s AND %s and `status` < 3 ORDER BY pay_id DESC", $pro_conds[$vv_status], $notice_mod, $real_left_time, $real_right_time);
						// 生产数据
						$insert_message_yff = $this->get_syq_data($sql, $mb_id, $mb_title, $pro_conds[$vv_status], $notice_mod); // 已付费的数据
						

					}elseif($vv_status == 4){ // 已付费 即将到期 前多少天 此时还是已付费状态
						$real_left_time = $left_time + $trydate_time - $yff_day;
						$real_right_time = $right_time + $trydate_time - $yff_day;
						$sql = sprintf("select `ep_id` from cy_company_paysetting where `pay_status` = %u and `cpg_id` = %u and date_start BETWEEN %s AND %s and `status` < 3 ORDER BY pay_id DESC", $pro_conds[$vv_status-1], $notice_mod, $real_left_time, $real_right_time);
						// 生产数据
						$insert_message_yff_jjdq = $this->get_syq_data($sql, $mb_id, $mb_title, $pro_conds[$vv_status], $notice_mod); //已付费-即将到期的数据				

					}elseif($vv_status == 5){ // 已付费，已到期的前多少天, 此时是已付费-即将到期的状态
						// 组装查询条件
						$real_left_time = $left_time + $trydate_time;
						$real_right_time = $right_time + $trydate_time;
						$sql = sprintf("select `ep_id` from cy_company_paysetting where `pay_status` = %u and `cpg_id` = %u and date_end BETWEEN %s AND %s and `status` < 3 ORDER BY pay_id DESC", $pro_conds[$vv_status-1], $notice_mod, $real_left_time, $real_right_time);
						// 生产数据
						
						$insert_message_yff_ydq = $this->get_syq_data($sql, $mb_id, $mb_title, $pro_conds[$vv_status], $notice_mod); //已付费-已到期的数据
					}

				}
			}
			// 把各个情况的数据组合到一起
			$into_data = array_merge($insert_message_syq['oa'], $insert_message_syq_jjdq['oa'], $insert_message_syq_ydq['oa'], $insert_message_yff['oa'], $insert_message_yff_jjdq['oa'], $insert_message_yff_ydq['oa']);

			$into_back = array_merge($insert_message_syq['back_oa'], $insert_message_syq_jjdq['back_oa'], $insert_message_syq_ydq['back_oa'], $insert_message_yff['back_oa'], $insert_message_yff_jjdq['back_oa'], $insert_message_yff_ydq['back_oa']);

			// 插入数据库 发送消息
			if($into_data){
				$this->insert_mesg_data($into_data);
			}

			if ($into_back) $this->insert_back_data($into_back); // 总后台消息数据	 

		} catch (Exception $e) {
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
	 * [insert_mesg_data 分批插入数据表数据 100条 每次100条]
	 * @param  [type] $into_data [description]
	 * @return [type]            [description]
	 */
	public function insert_mesg_data($into_data){

		// 分批插入数据表数据 100条 每次100条
		$total = count($into_data);
		$num = ceil($total/100); // 进一法
		$yu = $total%100;
		$end_sql1 = "INSERT INTO cy_enterprise_message_log (epid, meid, realman, title, status, created, updated) VALUES";
		for ($i=1; $i <$num; $i++) { 
			$end_sql = "INSERT INTO cy_enterprise_message_log (epid, meid, realman, title, status, created, updated) VALUES";
			
			for ($i1=0; $i1 < 100 ; $i1++) { 
				$val = array_values($into_data[$i*$i1]);
				array_push($val, '1', time(), time());
				$values = "'".implode($val, "','")."'";
				$end_sql .= "(".$values."),";
			}
			$end_sql = substr($end_sql, 0, -1);
			$re = $this->_db->query($end_sql); // 执行sql
			$do_log = "发送成败：".$re." ----第".$i."00条消息 ----发送时间：".date('Y-m-d H:i:s');
			logger::log($do_log, logger::LOGGER_LEVEL_ERROR);

			//var_dump($end_sql);die;
		}
		$over_num = $num -1;

		if($yu > 0){
			$i_start  = $over_num*100;
			$i_end = $i_start + $yu;
			for ($i2=$i_start; $i2 < $i_end; $i2++) { 
				$val1 = array_values($into_data[$i2]);
				array_push($val1, '1', time(), time());
				$values = "'".implode($val1, "','")."'";
				$end_sql1 .= "(".$values."),";
			}
			$end_sql1 = substr($end_sql1, 0, -1);
			$re = $this->_db->query($end_sql1); //执行sql
			$do_log = "发送成败：".$re." ----剩余的".$yu."条消息 ----发送时间：".date('Y-m-d H:i:s');
			logger::log($do_log, logger::LOGGER_LEVEL_ERROR);
		}
	}

	/**
	 * [insert_back_data 分批插入数据表数据 100条 每次100条]   待合并方法
	 * @param  [type] $into_data [description]
	 * @return [type]            [description]
	 */
	public function insert_back_data($into_data){

		// 分批插入数据表数据 100条 每次100条
		$total = count($into_data);
		$num = ceil($total/100); // 进一法
		$yu = $total%100;
		$end_sql1 = "INSERT INTO cy_enterprise_overdue (epid, meid, overdue_status, suid, status, created, updated) VALUES";
		for ($i=1; $i <$num; $i++) { 
			$end_sql = "INSERT INTO cy_enterprise_overdue (epid, meid, overdue_status, suid, status, created, updated) VALUES";
			
			for ($i1=0; $i1 < 100 ; $i1++) { 
				$val = array_values($into_data[$i*$i1]);
				array_push($val, '1', time(), time());
				$values = "'".implode($val, "','")."'";
				$end_sql .= "(".$values."),";
			}
			$end_sql = substr($end_sql, 0, -1);
			$re = $this->_db->query($end_sql); // 执行sql
			$do_log = "发送成败：".$re." ----第".$i."00条消息 ----发送时间：".date('Y-m-d H:i:s');
			logger::log($do_log, logger::LOGGER_LEVEL_ERROR);

		}
		$over_num = $num -1;

		if($yu > 0){
			$i_start  = $over_num*100;
			$i_end = $i_start + $yu;
			for ($i2=$i_start; $i2 < $i_end; $i2++) { 
				$val1 = array_values($into_data[$i2]);
				array_push($val1, '1', time(), time());
				$values = "'".implode($val1, "','")."'";
				$end_sql1 .= "(".$values."),";
			}
			$end_sql1 = substr($end_sql1, 0, -1);
			$re = $this->_db->query($end_sql1); //执行sql
			$do_log = "发送成败：".$re." ----剩余的".$yu."条消息 ----发送时间：".date('Y-m-d H:i:s');
			logger::log($do_log, logger::LOGGER_LEVEL_ERROR);
		}
	}

	/**
	 * [get_syq_data 生产待发消息的数据]
	 * @param  [type] $sql            [description]
	 * @param  [type] $mb_id           [description]
	 * @param  [type] $mb_title        [description]
	 * @param  [type] $dq_status       [description]
	 * @param  [type] $notice_mod      [description]
	 * @return [type]                  [description]
	 */
	public function get_syq_data($sql, $mb_id, $mb_title, $dq_status, $notice_mod){
		
		$profile_data = array();
		$profile_data = $this->mysql_get($sql);

		// 组装数据
		$ppro_data = array_column($profile_data, 'ep_id');
		$insert_message = array(); // 需要插入的消息数据

		$insert_back = array(); // 需要插入的总后台的消息
		foreach ($ppro_data as $kk => $vv) {
			$insert_message[$kk]['epid'] = $vv;
			$insert_message[$kk]['meid'] = $mb_id;
			$insert_message[$kk]['realman'] = "系统发送";
			$insert_message[$kk]['title'] = $mb_title[$mb_id];
		}

		// 继续构造总后台消息数据
		foreach ($ppro_data as $ke => $ve) {
			$insert_back[$ke]['epid'] = $ve;
			$insert_back[$ke]['meid'] = $mb_id;
			$insert_back[$ke]['overdue_status'] = $dq_status;
			$insert_back[$ke]['suid'] = $notice_mod; // 套件id
		}

		// 合并吧孩子
		return array(
			'oa' => $insert_message,
			'back_oa' => $insert_back
		);
	}

}
