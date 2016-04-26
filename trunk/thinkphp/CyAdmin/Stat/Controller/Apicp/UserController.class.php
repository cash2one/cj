<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 16/1/30
 * Time: 上午10:51
 */
namespace Stat\Controller\Apicp;

use Common\Common\Cache;
use Common\Common\Pager;

class UserController extends AbstractController {
	/** 自定义时间选择 */
	const DAYS_CUSTOM = - 1;

	const RANGE_SEVEN = 7;//最近7天
	const RANGE_THIRTY = 30;//最近30天
	//1:小客户2:中型客户3:大型客户4:VIP客户
	public $level = array(
		1 => '小客户',
		2 => '中型客户',
		3 => '大型客户',
		4 => 'VIP客户',
	);
	//客户状态
	public $customer_status = array(
		'1' => '新增客户',
		'2' => '初步沟通',
		'3' => '见面拜访',
		'4' => '确定意向',
		'5' => '正式报价',
		'6' => '商务谈判',
		'7' => '签约成交',
		'8' => '售后服务',
		'9' => '停滞',
		'10' => '流失',
	);
	//趋势图名称
	public $chart_name = array(
		'company_count' => '新增企业',
		'add_member' => '新增员工',
		'active_company' => '活跃企业',
		'pay_percent' => '付费转化率',
		'lose_percent' => '用户流失率',
	);

	public $new_company_field = array(
		'公司名称',
		'手机号',
		'所在行业',
		'客户状态',
		'客户等级',
		'企业规模',
		'客户来源',
		'是否绑定',
		'负责人',
		'付费状态',
		'创建及注册时间',
		'最后更新时间',
	);
	public $new_pay_field = array(
		'付费时间',
		'公司名称',
		'手机号',
		'所在行业',
		'客户状态',
		'客户等级',
		'企业规模',
		'客户来源',
		'是否绑定',
		'负责人',
		'付费状态',
		'创建及注册时间',
		'最后更新时间',
	);

	/**
	 * 用户趋势图，表数据
	 */
	public function User_data_get() {

		$params = I('get.');
		//参数处理
		list($params, $page, $limit, $page_option, $act) = $this->format_params($params);

		$serv_company = D('Stat/StatCompany', 'Service');

		//其他天数的公司信息
		$view_list = $serv_company->list_by_conds_cp($params, $page_option);
		$view_list = $this->_format_view_list($view_list);
		$chart_data = array();

		//趋势图
		$chart_data = $this->get_add_company_chart($params, $act);

		//分页
		$multi = $this->get_multi($limit, $page, $params);

		$result = array(
			'picture_data' => array(),
			'view_list' => $view_list,
			'chart_data' => $chart_data,
			'multi' => $multi,
		);

		//返回值
		$this->_response($result);
	}

	/**
	 * 负责人数据导出
	 * @return bool
	 */
	public function Dump_adminer_company() {

		$params = I('get.');
		//获取时间范围
		$this->get_range_time($params);
		$limit = 500;
		$ca_id = array();
		if (!empty($params['ca_id'])) {
			$ca_id = explode(',', $params['ca_id']);
		}
		if ($params['adminer_type'] == 'pay') {
			$serv_adminer_pay = D('Stat/StatAdminerPayRecord', 'Service');
			//总数
			$total = $serv_adminer_pay->count_by_time_adminer($params, $ca_id);
		} else {
			$serv_adminer_company = D('Stat/StatAdminerCompanyRecord', 'Service');
			//总数
			$total = $serv_adminer_company->count_by_time_adminer($params, $ca_id);
		}
		if ($total == 0) {
			if ($params['adminer_type'] == 'pay') {
				$this->empty_field($this->new_pay_field);
			} else {
				$this->empty_field($this->new_company_field);
			}
		}

		// 实例化压缩类
		$zip = new \ZipArchive();
		$path = get_sitedir() . 'excel/';
		$zipname = $path . 'company' . date('YmdHis', time());
		$zip->open($zipname . '.zip', \ZipArchive::CREATE);
		//rmkdir($path);
		$serv_stat_plugin_add = D('Stat/StatPluginAdd', 'Service');

		//循环次数
		$times = ceil($total / $limit);
		//根据总数循环格式数据
		for ($i = 1; $i <= $times; $i ++) {
			// 分页参数
			list($start, $limit, $i) = page_limit($i, $limit, $limit);
			// 分页参数
			$page_option = array($start, $limit);
			$list = array();
			//联表查询付费公司信息
			if ($params['adminer_type'] == 'pay') {
				$list = $serv_adminer_pay->list_by_time_adminer($params, $ca_id, $page_option);
			} else {
				$list = $serv_adminer_company->list_by_time_adminer($params, $ca_id, $page_option);
			}

			//格式信息
			if (!empty($list)) {
				$serv_stat_plugin_add->get_ca_name_pay_status($list);
				$list = $this->_format_add_company($list);
			}
			if (!empty($list)) {
				$result = $this->get_new_company_dump($params['adminer_type'], $list, $i);
				if ($result) {
					$zip->addFile($result, 'new_pay' . $i . '.xls');
				}
				unset($page_option, $start);
			}
		}

		//下载并清除文件
		$zip->close();
		$this->__put_header($zipname . '.zip');
		$this->__clear($path);

		return true;
	}

	/**
	 * 负责人付费公司接口
	 */
	public function Adminer_new_pay() {

		$params = I('get.');
		//获取时间范围
		$this->get_range_time($params);

		// 判断是否为空
		$page = !empty($params['page']) ? $params['page'] : 1;
		$limit = !empty($params['limit']) ? $params['limit'] : 10;

		// 分页参数
		list($start, $limit, $page) = page_limit($page, $limit);
		// 分页参数
		$page_option = array($start, $limit);
		$ca_id = array();
		if (!empty($params['ca_id'])) {
			$ca_id = explode(',', $params['ca_id']);
		}

		if ($params['adminer_type'] == 'pay') {
			$serv_adminer_pay = D('Stat/StatAdminerPayRecord', 'Service');
			$list = $serv_adminer_pay->list_by_time_adminer($params, $ca_id, $page_option);
			//分页
			$total = $serv_adminer_pay->count_by_time_adminer($params, $ca_id);
		} else {
			$serv_adminer_company = D('Stat/StatAdminerCompanyRecord', 'Service');
			$list = $serv_adminer_company->list_by_time_adminer($params, $ca_id, $page_option);
			//分页
			$total = $serv_adminer_company->count_by_time_adminer($params, $ca_id);
		}

		if (!empty($list)) {
			$list = $this->_format_add_company($list);
			$serv_stat_plugin_add = D('Stat/StatPluginAdd', 'Service');
			$serv_stat_plugin_add->get_ca_name_pay_status($list);
		}


		//统计条件查出数量
		$pagerOptions = array(
			'total_items' => $total,
			'per_page' => $limit,
			'current_page' => $page,
			'show_total_items' => true,
		);

		$multi = Pager::make_links($pagerOptions);
		$result = array(
			'list' => $list,
			'multi' => $multi,
		);

		return $this->_response($result);
	}

	/**
	 * 导出新增付费公司数据
	 * @return bool
	 */
	public function Dump_new_pay_get() {

		$params = I('get.');
		//获取时间范围
		$this->get_range_time($params);
		$limit = 500;
		//查询公司表
		//查询公司表
		$serv_paysetting = D('Common/CompanyPaysetting', 'Service');

		$pay_list_date = $serv_paysetting->list_new_pay($params, array());
		$pay_company_list = array();
		//去重查询新付费公司
		if (!empty($pay_list_date)) {
			$ep_list = array_unique(array_column($pay_list_date, 'ep_id'));
			foreach ($ep_list as $ep_id) {
				$pay_record_count = $serv_paysetting->count_pay_record($params, $ep_id);
				if ($pay_record_count == 0) {
					$pay_company_list[] = $ep_id;
				}
			}
		}
		//分页
		$count = count($pay_company_list);
		if ($count == 0) {
			$this->empty_field($this->new_pay_field);
		}
		$serv_enterprise_profile = D('Common/EnterpriseProfile', 'Service');
		$serv_stat_plugin_add = D('Stat/StatPluginAdd', 'Service');

		// 实例化压缩类
		$zip = new \ZipArchive();
		$path = get_sitedir() . 'excel/';
		$zipname = $path . 'company' . date('YmdHis', time());
		$zip->open($zipname . '.zip', \ZipArchive::CREATE);
		//rmkdir($path);
		//循环次数
		$times = ceil($count / $limit);
		//根据总数循环格式数据
		for ($i = 1; $i <= $times; $i ++) {
			// 分页参数
			list($start, $limit, $i) = page_limit($i, $limit, $limit);
			// 分页参数
			$page_option = array($start, $limit);
			$list = array();
			if (!empty($pay_company_list)) {
				//联表查询付费公司信息
				$serv_enterprise_profile = D('Common/EnterpriseProfile', 'Service');
				$pay_list = $serv_enterprise_profile->list_pay_company_info($pay_company_list, $params, $page_option);

				//格式信息
				if (!empty($pay_list)) {
					$serv_stat_plugin_add->get_ca_name_pay_status($pay_list);
					$list = $this->_format_add_company($pay_list);
				}
			}
			//格式信息
			$serv_stat_plugin_add->get_ca_name_pay_status($list);
			$list = $this->_format_add_company($list);
			if (!empty($list)) {
				$result = $this->get_new_company_dump('pay', $list, $i);
				if ($result) {
					$zip->addFile($result, 'new_pay' . $i . '.xls');
				}
				unset($page_option, $start);
			}
		}

		//下载并清除文件
		$zip->close();
		$this->__put_header($zipname . '.zip');
		$this->__clear($path);

		return true;
	}

	/**
	 * 导出新增公司数据
	 * @return bool
	 */
	public function Dump_new_company_get() {

		$params = I('get.');
		//获取时间范围
		$this->get_range_time($params);
		$limit = 500;

		//查询负责人表
		$serv_company_adminer = D('Stat/StatCompanyAdminer', 'Service');

		//分页
		//查询公司表
		$serv_profile = D('Common/EnterpriseProfile', 'Service');
		$count = $serv_profile->count_by_date($params);
		if ($count == 0) {
			$this->empty_field($this->new_company_field);
		}
		// 实例化压缩类
		$zip = new \ZipArchive();
		$path = get_sitedir() . 'excel/';
		$zipname = $path . 'company' . date('YmdHis', time());
		$zip->open($zipname . '.zip', \ZipArchive::CREATE);
		//rmkdir($path);
		//循环次数
		$times = ceil($count / $limit);
		//根据总数循环格式数据
		for ($i = 1; $i <= $times; $i ++) {
			// 分页参数
			list($start, $limit, $i) = page_limit($i, $limit, $limit);
			// 分页参数
			$page_option = array($start, $limit);

			//数据查询
			$list = $serv_profile->list_by_date($params, $page_option);
			$serv_stat_plugin_add = D('Stat/StatPluginAdd', 'Service');
			$serv_stat_plugin_add->get_ca_name_pay_status($list);
			$list = $this->_format_add_company($list);
			if (!empty($list)) {
				$result = $this->get_new_company_dump('company', $list, $i);
				if ($result) {
					$zip->addFile($result, 'company' . $i . '.xls');
				}
				unset($page_option, $start);
			}
		}

		//下载并清除文件
		$zip->close();
		$this->__put_header($zipname . '.zip');
		$this->__clear($path);

		return true;
	}

	/**
	 * 文件
	 * @param $view_list
	 * @return string
	 */
	public function get_new_company_dump($act = 'company', $list, $n) {

		$excel = new \Com\Excel();
		$wid = 13;
		// xls 横坐标
		for ($i = 0; $i <= $wid; $i ++) {
			if ($i < 26) {
				$letter[] = chr($i + 65);
			} else {
				$ascii = floor($i / 26) - 1;
				$letter[] = chr($ascii + 65) . chr(($i % 26) + 65);
			}
		}
		if ($act == 'pay') {
			$data[0] = $this->new_pay_field;

			// 默认数据
			foreach ($list as $key_mem => $val) {
				//固定字段
				$data[] = array(
					$val['_created'],
					$val['ep_name'],
					$val['ep_mobilephone'],
					$val['ep_industry'],
					$this->customer_status[$val['customer_status']],
					$this->level[$val['ep_customer_level']],
					$val['ep_companysize'],
					$val['ep_ref'],
					$val['_ep_wxcorpid'],
					$val['ca_name'],
					$val['pay_status'],
					rgmdate($val['ep_created']),
					empty($val['ep_last_operation']) ? '' : rgmdate($val['ep_last_operation']),
				);
			}
		} else {
			$data[0] = $this->new_company_field;

			// 默认数据
			foreach ($list as $key_mem => $val) {
				//固定字段
				$data[] = array(
					$val['ep_name'],
					$val['ep_mobilephone'],
					$val['ep_industry'],
					$this->customer_status[$val['customer_status']],
					$this->level[$val['ep_customer_level']],
					$val['ep_companysize'],
					$val['ep_ref'],
					$val['_ep_wxcorpid'],
					$val['ca_name'],
					$val['pay_status'],
					rgmdate($val['ep_created']),
					empty($val['ep_last_operation']) ? '' : rgmdate($val['ep_last_operation']),
				);
			}
		}


		// 填充表格信息
		for ($i = 1; $i <= count($data); $i ++) {
			$j = 0;
			foreach ($data[$i - 1] as $key => $value) {
				$excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
				$j ++;
			}
		}
		// 创建Excel输入对象
		$write = new \PHPExcel_Writer_Excel5($excel);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header('Content-Disposition:attachment;filename="company' . $n . '.xls"');
		header("Content-Transfer-Encoding:binary");

		$path = get_sitedir() . 'excel/';
		if (!is_dir($path)) {
			mkdir($path);
		}

		$write->save($path . "follow" . $n . ".xls");
		$filepath = $path . 'follow' . $n . '.xls';

		return $filepath;
	}

	/**
	 * 导出跟进人数据
	 * @return bool
	 */
	public function Dump_follow_list_get() {

		$params = I('get.');
		//获取时间范围
		$this->get_range_time($params);
		$adminer = array();
		if (!empty($params['adminer'])) {
			$adminer = explode(',', $params['adminer']);
			$adminer = array($params['adminer']);
		}

		$limit = 500;

		//查询负责人表
		$serv_company_adminer = D('Stat/StatCompanyAdminer', 'Service');

		//分页
		$count = $serv_company_adminer->count_by_date_adminer($params, $adminer);        //统计条件查出数量
		if ($count == 0) {
			$this->empty_field(array(
				'时间',
				'负责人',
				'新增企业数',
				'新增员工数',
				'新增活跃员工数',
				'活跃企业数',
				'活跃员工数',
				'企业流失数',
				'企业流失率',
				'激活企业数',
				'激活率',
				'新增付费企业数',
				'付费转化率',
				'总员工数',
				'总企业数',
			));
		}
		// 实例化压缩类
		$zip = new \ZipArchive();
		$path = get_sitedir() . 'excel/';
		$zipname = $path . 'company' . date('YmdHis', time());
		$zip->open($zipname . '.zip', \ZipArchive::CREATE);
		//rmkdir($path);
		//循环次数
		$times = ceil($count / $limit);
		//根据总数循环格式数据
		for ($i = 1; $i <= $times; $i ++) {
			// 分页参数
			list($start, $limit, $i) = page_limit($i, $limit, $limit);
			// 分页参数
			$page_option = array($start, $limit);
			//其他天数的公司信息
			$list = $serv_company_adminer->list_by_date_adminer($params, $adminer, $page_option);
			if (!empty($list)) {
				$result = $this->get_follow_dump($list, $i);
				if ($result) {
					$zip->addFile($result, 'follow' . $i . '.xls');
				}
				unset($page_option, $start);
			}
		}

		//下载并清除文件
		$zip->close();
		$this->__put_header($zipname . '.zip');
		$this->__clear($path);

		return true;
	}

	/**
	 * 文件
	 * @param $view_list
	 * @return string
	 */
	public function get_follow_dump($list, $n) {

		$excel = new \Com\Excel();
		$wid = 16;
		// xls 横坐标
		for ($i = 0; $i <= $wid; $i ++) {
			if ($i < 26) {
				$letter[] = chr($i + 65);
			} else {
				$ascii = floor($i / 26) - 1;
				$letter[] = chr($ascii + 65) . chr(($i % 26) + 65);
			}
		}
		//负责人数据
		$cache = &\Common\Common\Cache::instance();
		$adminer = $cache->get('Common.adminer');

		foreach ($adminer as $_adminer) {
			$adminer_list[$_adminer['ca_id']] = $_adminer['ca_realname'];
		}
		$data[0] = array(
			'时间',
			'负责人',
			'新增企业数',
			'新增员工数',
			'新增活跃员工数',
			'活跃企业数',
			'活跃员工数',
			'企业流失数',
			'企业流失率',
			'激活企业数',
			'激活率',
			'新增付费企业数',
			'付费转化率',
			'总员工数',
			'总企业数',
		);

		// 默认数据
		foreach ($list as $key_mem => $val) {
			$activation_percent = $val['activation_percent'] * 100 . '%';
			$pay_percent = $val['pay_percent'] * 100 . '%';
			$lose_percent = $val['lose_percent'] * 100 .'%';
			//固定字段
			$data[] = array(
				rgmdate($val['time'], 'Y-m-d'),
				$adminer_list[$val['ca_id']],
				$val['company_count'],
				$val['add_member'],
				0,
				$val['active_company'],
				$val['active_member'],
				$val['lose_number'],
				$lose_percent,
				$val['activation_count'],
				$activation_percent,
				$val['pay_count'],
				$pay_percent,
				$val['active_company'],
				$val['all_company'],
			);

		}

		// 填充表格信息
		for ($i = 1; $i <= count($data); $i ++) {
			$j = 0;
			foreach ($data[$i - 1] as $key => $value) {
				$excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
				$j ++;
			}
		}
		// 创建Excel输入对象
		$write = new \PHPExcel_Writer_Excel5($excel);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header('Content-Disposition:attachment;filename="follow' . $n . '.xls"');
		header("Content-Transfer-Encoding:binary");

		$path = get_sitedir() . 'excel/';
		if (!is_dir($path)) {
			mkdir($path);
		}

		$write->save($path . "follow" . $n . ".xls");
		$filepath = $path . 'follow' . $n . '.xls';

		return $filepath;
	}

	/**
	 * 导出公司列表
	 * @return bool
	 */
	public function Dump_company_list_get() {

		$params = I('get.');
		//获取时间范围
		$this->get_range_time($params);

		$limit = 500;
		$serv_company = D('Stat/StatCompany', 'Service');
		//统计条件查出数量
		$count = $serv_company->count_by_conds_cp($params);
		//空文件
		if ($count == 0) {
			$this->empty_field(array(
				'时间',
				'新增企业数',
				'新增员工数',
				'新增活跃员工数',
				'活跃企业数',
				'活跃员工数',
				'企业流失数',
				'企业流失率',
				'激活企业数',
				'激活率',
				'新增付费企业数',
				'付费转化率',
				'总员工数',
				'总企业数',
			));
		}
		// 实例化压缩类
		$zip = new \ZipArchive();
		$path = get_sitedir() . 'excel/';
		$zipname = $path . 'stat' . date('YmdHis', time());
		$zip->open($zipname . '.zip', \ZipArchive::CREATE);
		//rmkdir($path);
		//循环次数
		$times = ceil($count / $limit);
		//根据总数循环格式数据
		for ($i = 1; $i <= $times; $i ++) {
			// 分页参数
			list($start, $limit, $i) = page_limit($i, $limit, $limit);
			// 分页参数
			$page_option = array($start, $limit);

			//其他天数的公司信息
			$view_list = $serv_company->list_by_conds_cp($params, $page_option);
			$view_list = $this->_format_view_list($view_list);
			if (!empty($view_list)) {
				$result = $this->get_company_dump($view_list, $i);
				if ($result) {
					$zip->addFile($result, 'company' . $i . '.xls');
				}
				unset($page_option, $start);
			}
		}

		//下载并清除文件
		$zip->close();
		$this->__put_header($zipname . '.zip');
		$this->__clear($path);

		return true;
	}

	/**
	 * 文件
	 * @param $view_list
	 * @return string
	 */
	public function get_company_dump($view_list, $n) {

		$excel = new \Com\Excel();
		$wid = 13;
		// xls 横坐标
		for ($i = 0; $i <= $wid; $i ++) {
			if ($i < 26) {
				$letter[] = chr($i + 65);
			} else {
				$ascii = floor($i / 26) - 1;
				$letter[] = chr($ascii + 65) . chr(($i % 26) + 65);
			}
		}
		$data[0] = array(
			'时间',
			'新增企业数',
			'新增员工数',
			'新增活跃员工数',
			'活跃企业数',
			'活跃员工数',
			'企业流失数',
			'企业流失率',
			'激活企业数',
			'激活率',
			'新增付费企业数',
			'付费转化率',
			'总员工数',
			'总企业数',
		);

		// 默认数据
		foreach ($view_list as $key_mem => $val) {
			//固定字段
			$data[] = array(
				rgmdate($val['time'], 'Y-m-d'),
				$val['company_count'],
				$val['add_member'],
				0,
				$val['active_company'],
				$val['active_member'],
				$val['lose_number'],
				$val['lose_percent'] . '%',
				$val['activation_count'],
				$val['activation_percent'] . '%',
				$val['pay_count'],
				$val['pay_percent'] . '%',
				$val['active_company'],
				$val['all_company'],
			);

		}

		// 填充表格信息
		for ($i = 1; $i <= count($data); $i ++) {
			$j = 0;
			foreach ($data[$i - 1] as $key => $value) {
				$excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
				$j ++;
			}
		}
		//var_dump($data);die;
		// 创建Excel输入对象
		$write = new \PHPExcel_Writer_Excel5($excel);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header('Content-Disposition:attachment;filename="member' . $n . '.xls"');
		header("Content-Transfer-Encoding:binary");

		$path = get_sitedir() . 'excel/';
		if (!is_dir($path)) {
			mkdir($path);
		}

		$write->save($path . "company" . $n . ".xls");
		$filepath = $path . 'company' . $n . '.xls';

		return $filepath;
	}

	/**
	 * 生成空excel文件
	 */
	public function empty_field($title_string) {

		// 标题栏样式定义
		$options = array(
			'title_background_color' => 'FFFFFFFF',
		);
		// 下载的文件名
		$filename = 'excel';
		// 标题文字 和 标题栏宽度
		$title_width = array();

		// 默认数据
		$row_data = array();

		// 载入 Excel 类
		$excel = new \Com\Excel();
		$excel->make_excel_download($filename, $title_string, $title_width, $row_data, $options);

		return true;
	}

	/**
	 * 下载输出至浏览器
	 * @param $zipname
	 */
	private function __put_header($zipname) {

		if (!file_exists($zipname)) {
			exit("下载失败");
		}

		$file = fopen($zipname, "r");
		Header("Content-type: application/octet-stream");
		Header("Accept-Ranges: bytes");
		Header("Accept-Length: " . filesize($zipname));
		Header("Content-Disposition: attachment; filename=" . basename($zipname));
		echo fread($file, filesize($zipname));
		$buffer = 1024;
		while (!feof($file)) {
			$file_data = fread($file, $buffer);
			echo $file_data;
		}

		fclose($file);
	}

	/**
	 * 清理产生的临时文件
	 */
	private function __clear($path) {

		$dh = opendir($path);
		while ($file = readdir($dh)) {
			if ($file != "." && $file != "..") {
				unlink($path . $file);
			}
		}

		return true;
	}

	/**
	 * 转换时间方法
	 * @param $params
	 * @return bool
	 */
	public function get_range_time(&$params) {

		if ($params['range'] == self::RANGE_SEVEN) {
			$params['s_time'] = rgmdate(NOW_TIME - 86400 * self::RANGE_SEVEN, 'Y-m-d');
			$params['e_time'] = rgmdate(NOW_TIME, 'Y-m-d');
		} elseif ($params['range'] == self::RANGE_THIRTY) {
			$params['s_time'] = rgmdate(NOW_TIME - 86400 * self::RANGE_THIRTY, 'Y-m-d');
			$params['e_time'] = rgmdate(NOW_TIME, 'Y-m-d');
		}

		return true;
	}

	/**
	 * 头部信息接口
	 */
	public function Header_get() {

		$serv_company = D('Stat/StatCompany', 'Service');
		//昨天企业信息
		$company_info = $serv_company->get_company();
		//获取前天企业信息
		$yesterday_company_info = $serv_company->get_yesterday_company();

		//表头信息
		$header_data = $serv_company->get_header_data_new($company_info, $yesterday_company_info);

		$result = array(
			'header' => $header_data,
		);

		$this->_response($result);
	}

	/**
	 * 格式数据
	 * @param $list array 待格式的数据
	 * @return bool
	 */
	protected function _format_view_list($list) {

		if (empty($list)) {
			return true;
		}
		//汇总记录
		//$last_record = array('');
		foreach ($list as &$val) {
			$val['lose_percent'] = $val['lose_percent'] * 100;
			$val['pay_percent'] = $val['pay_percent'] * 100;
			$val['_time'] = rgmdate($val['time'], 'Y-m-d');
			$val['activation_percent'] = $val['activation_percent'] * 100;

			/*$last_record['_time'] = '汇总';
			$last_record['company_count'] += $val['company_count'];
			$last_record['add_member'] += $val['add_member'];
			$last_record['active_company'] += $val['active_company'];
			$last_record['lose_number'] += $val['lose_number'];
			$last_record['pay_percent'] += $val['pay_percent'];
			$last_record['count_member'] += $val['count_member'];
			$last_record['all_company'] += $val['all_company'];
			$last_record['activation_count'] += $val['activation_count'];
			$last_record['activation_percent'] += $val['activation_percent'];
			$last_record['pay_count'] += $val['pay_count'];*/
		}

		//$list[] = $last_record;
		return $list;
	}

	/**
	 * 新增公司接口
	 */
	public function New_company_get() {

		$params = I('get.');
		//获取时间范围
		$this->get_range_time($params);
		// 判断是否为空
		$page = !empty($params['page']) ? $params['page'] : 1;
		$limit = !empty($params['limit']) ? $params['limit'] : 10;

		// 分页参数
		list($start, $limit, $page) = page_limit($page, $limit);
		// 分页参数
		$page_option = array($start, $limit);

		//查询公司表
		$serv_profile = D('Common/EnterpriseProfile', 'Service');

		$list = $serv_profile->list_by_date($params, $page_option);

		if (!empty($list)) {
			$serv_stat_plugin_add = D('Stat/StatPluginAdd', 'Service');
			$serv_stat_plugin_add->get_ca_name_pay_status($list);
			$list = $this->_format_add_company($list);
		}

		//分页
		$total = $serv_profile->count_by_date($params);
		//统计条件查出数量
		$pagerOptions = array(
			'total_items' => $total,
			'per_page' => $limit,
			'current_page' => $page,
			'show_total_items' => true,
		);

		$multi = Pager::make_links($pagerOptions);
		$result = array(
			'list' => $list,
			'multi' => $multi,
		);

		$this->_response($result);
	}


	/**
	 * 新增付费公司接口
	 */
	public function New_pay_get() {

		$params = I('get.');
		//获取时间范围
		$this->get_range_time($params);

		// 判断是否为空
		$page = !empty($params['page']) ? $params['page'] : 1;
		$limit = !empty($params['limit']) ? $params['limit'] : 10;

		// 分页参数
		list($start, $limit, $page) = page_limit($page, $limit);
		// 分页参数
		$page_option = array($start, $limit);

		//查询公司表
		$serv_paysetting = D('Common/CompanyPaysetting', 'Service');

		$pay_list_date = $serv_paysetting->list_new_pay($params, array());
		$pay_company_list = array();
		//去重查询新付费公司
		if (!empty($pay_list_date)) {
			$ep_list = array_unique(array_column($pay_list_date, 'ep_id'));
			foreach ($ep_list as $ep_id) {
				$pay_record_count = $serv_paysetting->count_pay_record($params, $ep_id);
				if ($pay_record_count == 0) {
					$pay_company_list[] = $ep_id;
				}
			}
		}
		$pay_company_list = array_unique($pay_company_list);
		$list = array();
		if (!empty($pay_company_list)) {
			//联表查询付费公司信息
			$serv_enterprise_profile = D('Common/EnterpriseProfile', 'Service');
			$pay_list = $serv_enterprise_profile->list_pay_company_info($pay_company_list, $params, $page_option);

			//格式信息
			if (!empty($pay_list)) {
				$serv_stat_plugin_add = D('Stat/StatPluginAdd', 'Service');
				$serv_stat_plugin_add->get_ca_name_pay_status($pay_list);
				$list = $this->_format_add_company($pay_list);
			}
		}

		//分页
		$total = count($pay_company_list);
		//统计条件查出数量
		$pagerOptions = array(
			'total_items' => $total,
			'per_page' => $limit,
			'current_page' => $page,
			'show_total_items' => true,
		);

		$multi = Pager::make_links($pagerOptions);
		$result = array(
			'list' => $list,
			'multi' => $multi,
		);

		$this->_response($result);
	}

	/**
	 * 格式信息
	 * @param $list array 要格式的数组
	 */
	protected function _format_add_company($list) {

		if (empty($list)) {
			return true;
		}
		foreach ($list as &$val) {
			//是否绑定
			$val['_ep_wxcorpid'] = !empty($val['ep_wxcorpid']) ? '已绑定' : '未绑定';
			$val['_level'] = $this->level[$val['ep_customer_level']];
			$val['_created'] = rgmdate($val['ep_created']);
			$val['_updated'] = empty($val['ep_last_operation']) ? '' : rgmdate($val['ep_last_operation']);
			$val['_customer_status'] = $this->customer_status[$val['customer_status']];
			$val['_time'] = isset($val['created']) ? rgmdate($val['created'], 'Y-m-d') : '';
		}

		return $list;
	}

	/**
	 * 处理参数
	 * @param $params array 参数
	 * @return array
	 */
	public function format_params($params) {

		//获取时间范围
		$this->get_range_time($params);

		$act = $params['act'];
		// 判断是否为空
		$page = !empty($params['page']) ? $params['page'] : 1;
		$limit = !empty($params['limit']) ? $params['limit'] : 10;

		// 分页参数
		list($start, $limit, $page) = page_limit($page, $limit);
		// 分页参数
		$page_option = array($start, $limit);

		return array($params, $page, $limit, $page_option, $act);
	}

	/**
	 * 分页
	 * @param $limit int 每页显示数量
	 * @param $page int 当前页
	 */
	public function get_multi($limit, $page, $params) {

		//统计条件查出数量
		$serv_stat_company = D('Stat/StatCompany', 'Service');
		$total = $serv_stat_company->count_by_conds_cp($params);
		$pagerOptions = array(
			'total_items' => $total,
			'per_page' => $limit,
			'current_page' => $page,
			'show_total_items' => true,
		);

		$multi = Pager::make_links($pagerOptions);

		return $multi;
	}

	/**
	 * 获取新增公司趋势图数据
	 * @param $params
	 * @param $page_option
	 * @return array
	 */
	public function get_add_company_chart($params, $field_name) {

		$serv_company = D('Stat/StatCompany', 'Service');
		$conds_time['s_time'] = $params['s_time'];
		$conds_time['e_time'] = $params['e_time'];
		$time_list = array();
		$data_list = array();
		//数据列表
		$record_list = $serv_company->list_by_conds_cp($conds_time);
		if (!empty($record_list)) {
			foreach ($record_list as $val) {
				$time_list[] = rgmdate($val['time'], 'Y-m-d');
				if ($field_name == 'pay_percent' || $field_name == 'lose_percent') {
					$value = $val[$field_name] * 100;
				} else {
					$value = $val[$field_name];
				}
				$data_list[] = $value;
			}
		}
		$chart_name = $this->chart_name[$field_name];

		return array(
			'chart_name' => $chart_name,
			'time_list' => $time_list,
			'data_list' => $data_list,
		);
	}

	/**
	 * 负责人数据接口
	 */
	public function Follow_get() {

		$params = I('get.');
		$params['act'] = '';
		//参数处理
		list($params, $page, $limit, $page_option, $act) = $this->format_params($params);

		$adminer = array();
		if (!empty($params['adminer'])) {
			$adminer = explode(',', $params['adminer']);
			$adminer = array($params['adminer']);
		}

		// 判断是否为空
		if (empty($params['page'])) {
			$page = 1;
			$params['page'] = 1;
		}
		if (empty($params['limit'])) {
			$limit = 10;
			$params['limit'] = 10;
		}
		// 分页参数
		list($start, $limit, $page) = page_limit($page, $limit);
		// 分页参数
		$page_option = array($start, $limit);

		//查询负责人表
		$serv_company_adminer = D('Stat/StatCompanyAdminer', 'Service');
		$list = $serv_company_adminer->list_by_date_adminer($params, $adminer, $page_option);

		//分页
		$total = $serv_company_adminer->count_by_date_adminer($params, $adminer);
		//统计条件查出数量
		$pagerOptions = array(
			'total_items' => $total,
			'per_page' => $limit,
			'current_page' => $page,
			'show_total_items' => true,
		);

		$multi = Pager::make_links($pagerOptions);
		//格式数据
		if (!empty($list)) {
			$serv_company_adminer->format_adminer($list);
		}

		//负责人数据
		$cache = &\Common\Common\Cache::instance();
		$adminer = $cache->get('Common.adminer');

		$result = array(
			'list' => $list,
			'adminer' => $adminer,
			'multi' => $multi,
		);

		$this->_response($result);
	}

	/**
	 * 单个企业数据 用户数据 昨日数据接口
	 * @return array
	 */
	public function UserHeader_get() {

		$params = I('get.');

		//判断是否获取到公司id
		if (is_null($params['ep_id'])) {
			return false;
		}

		//获取公司id
		$epid = $params['ep_id'];

		$serv = D('Stat/StatMemberAll', 'Service');
		$result = $serv->get_by_conds_lastday($epid);

		return $this->_response($result);

	}

	/*
	 * 单个企业数据 数据详情
	 */
	public function UserDetail_get() {

		$params = I('get.');

		//判断是否获取到公司id
		if (is_null($params['ep_id'])) {
			return false;
		}

		//获取公司id
		$ep_id = $params['ep_id'];

		// 获取提交参数
		list($start_time, $end_time, $page_option, $page) = $this->_get_params();

		// 查询
		$serv_user_detail = D('Stat/StatMemberAll', 'Service');
		$list = array();
		$total = array();
		list($list, $total) = $serv_user_detail->list_by_conds_det($start_time, $end_time, $ep_id, $page_option);

		$pagerOptions = array(
			'total_items' => $total,
			'per_page' => $page_option['limit'],
			'current_page' => $page,
			'show_total_items' => true,
		);
		$multi = Pager::make_links($pagerOptions);

		$result = array(
			'list' => $list,
			'multi' => $multi,
		);

		$this->_response($result);

		return true;
	}

	/**
	 * 单个企业 用户数据 图标数据
	 * @return bool
	 */
	public function UserViewChart_get() {

		$data_type = I('get.act', 1, 'intval'); // 数据类型
		$days = I('get.range', self::WEEK_DAYS, 'intval');
		$start = I('get.s_time');
		$end = I('get.e_time');

		$params = I('get.');
		//判断是否获取到公司id
		if (empty($params['ep_id'])) {
			return false;
		}

		//获取公司id
		$ep_id = $params['ep_id'];

		// 自定义时间 转换时间范围
		$start_time = rstrtotime($start);
		$end_time = rstrtotime($end) + 86400;
		if ($days == self::DAYS_CUSTOM && (empty($start_time) && empty($end_time))) {
			return false;
		}

		// 选择的 时间范围
		if ($days != self::DAYS_CUSTOM) {
			$start_time = 0;
			$end_time = 0;
			$end_time = $this->_today_time;
			// 如果没有天数
			if (empty($days)) {
				$start_time = $this->_default_start_time;
			} else {
				// 如果没有选择开始时间
				if (empty($start_time)) {
					$start_time = $this->_today_time - 86400 * $days;
				} else {
					$start_time = $start_time - 86400 * $days;
				}
			}
		}

		$serv_plugin = D('Stat/StatMemberAll', 'Service');

		// 获取数据
		$name = '';
		$chart_count = array();
		$chart_days = array();
		list($name, $chart_days, $chart_count) = $serv_plugin->user_charts_data($start_time, $end_time, $data_type, $ep_id);

		$result = array(
			'name' => $name,
			'days' => $chart_days,
			'count' => $chart_count,
		);
		$this->_response($result);

		return true;
	}

	/**
	 * 获取提交的参数: 开始时间 start 结束时间 end 页数 page 每页数量 limit
	 * return 开始时间 结束时间 分页参数 当前页数
	 * @return array|bool
	 */
	protected function _get_params() {

		// 获取开始 结束时间
		$days = I('get.days', self::WEEK_DAYS, 'intval');
		$start = I('get.start');
		$end = I('get.end');

		// 自定义时间 转换时间范围
		$start_time = rstrtotime($start);
		$end_time = rstrtotime($end) + 86400;
		if ($days == self::DAYS_CUSTOM && (empty($start_time) && empty($end_time))) {
			return false;
		}

		// 选择的 时间范围
		if ($days != self::DAYS_CUSTOM) {
			$start_time = 0;
			$end_time = 0;
			$end_time = $this->_today_time;
			// 如果没有天数
			if (empty($days)) {
				$start_time = $this->_default_start_time;
			} else {
				// 如果没有选择开始时间
				if (empty($start_time)) {
					$start_time = $this->_today_time - 86400 * $days;
				} else {
					$start_time = $start_time - 86400 * $days;
				}
			}
		}

		// 分页参数
		$page = I('get.page', self::DEAFULT_PAGE, 'intval');
		$limit = I('get.limit', self::DEAFULT_LIMIT, 'intval');
		list($start, $limit, $page) = page_limit($page, $limit);
		$page_option = array('start' => $start, 'limit' => $limit);

		return array(
			$start_time,
			$end_time,
			$page_option,
			$page,
		);
	}

	//导出用户数据 详情数据接口
	public function Dump_user_detail_get() {

		// 获取开始 结束时间
		$days = I('get.days', self::WEEK_DAYS, 'intval');
		$start = I('get.start');
		$end = I('get.end');

		// 自定义时间 转换时间范围
		$start_time = rstrtotime($start);
		$end_time = rstrtotime($end) + 86400;
		if ($days == self::DAYS_CUSTOM && (empty($start_time) && empty($end_time))) {
			return false;
		}

		// 选择的 时间范围
		if ($days != self::DAYS_CUSTOM) {
			$start_time = 0;
			$end_time = 0;
			$end_time = $this->_today_time;
			// 如果没有天数
			if (empty($days)) {
				$start_time = $this->_default_start_time;
			} else {
				// 如果没有选择开始时间
				if (empty($start_time)) {
					$start_time = $this->_today_time - 86400 * $days;
				} else {
					$start_time = $start_time - 86400 * $days;
				}
			}
		}

		$params = I('get.');
		//获取时间范围
		$this->get_range_time($params);
		//判断是否获取到公司id
		if (is_null($params['ep_id'])) {
			return false;
		}

		//获取公司id
		$ep_id = $params['ep_id'];

		$limit = 500;
		// 查询
		$serv_user_detail = D('Stat/StatMemberAll', 'Service');
		list($list, $count) = $serv_user_detail->list_by_conds_det($start_time, $end_time, $ep_id);
        $list = array();
		//空文件
		if ($count == 0) {
			$this->empty_field(array(
				'日期',
				'新增员工数',
				'活跃员工数',
				'已关注员工数',
				'未关注员工数',
				'企业员工总数',
			));
		}
		// 实例化压缩类
		$zip = new \ZipArchive();
		$path = get_sitedir() . 'excel/';
		$zipname = $path . 'member' . date('YmdHis', time());
		$zip->open($zipname . '.zip', \ZipArchive::CREATE);
		//rmkdir($path);
		//循环次数
		$times = ceil($count / $limit);
		//根据总数循环格式数据
		for ($i = 1; $i <= $times; $i ++) {
			// 分页参数
			list($start, $limit, $i) = page_limit($i, $limit, $limit);
			// 分页参数
			$page_option = array($start, $limit);
			list($list, $count) = $serv_user_detail->list_by_conds_det($start_time, $end_time, $ep_id, $page_option);

			//其他天数的公司信息

			if (!empty($list)) {
				$result = $this->get_detail_dump($list, $i);
				if ($result) {
					$zip->addFile($result, 'member' . $i . '.xls');
				}
				unset($page_option, $start);
			}
		}

		//下载并清除文件
		$zip->close();
		$this->__put_header($zipname . '.zip');
		$this->__clear($path);

		return true;
	}

	/**
	 * 文件
	 * @param $view_list
	 * @return string
	 */
	public function get_detail_dump($list, $n) {

		$excel = new \Com\Excel();
		$wid = 13;
		// xls 横坐标
		for ($i = 0; $i <= $wid; $i ++) {
			if ($i < 26) {
				$letter[] = chr($i + 65);
			} else {
				$ascii = floor($i / 26) - 1;
				$letter[] = chr($ascii + 65) . chr(($i % 26) + 65);
			}
		}
		$data[0] = array(
			'日期',
			'新增员工数',
			'活跃员工数',
			'已关注员工数',
			'未关注员工数',
			'企业员工总数',
		);

		// 默认数据
		foreach ($list as $key_mem => $val) {
			//固定字段
			$data[] = array(
				rgmdate($val['time'], 'Y-m-d'),
				$val['add'],
				$val['active_count'],
				$val['attention'],
				$val['unattention'],
				$val['all'],
			);

		}

		// 填充表格信息
		for ($i = 1; $i <= count($data); $i ++) {
			$j = 0;
			foreach ($data[$i - 1] as $key => $value) {
				$excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
				$j ++;
			}
		}

		// 创建Excel输入对象
		$write = new \PHPExcel_Writer_Excel5($excel);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header('Content-Disposition:attachment;filename="member' . $n . '.xls"');
		header("Content-Transfer-Encoding:binary");

		$path = get_sitedir() . 'excel/';
		if (!is_dir($path)) {
			mkdir($path);
		}

		$write->save($path . "company" . $n . ".xls");
		$filepath = $path . 'company' . $n . '.xls';

		return $filepath;
	}

}