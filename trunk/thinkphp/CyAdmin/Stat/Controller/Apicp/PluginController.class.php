<?php
/**
 * Created by PhpStorm.
 * User: zhoutao
 * Date: 16/2/25
 * Time: 上午11:10
 */

namespace Stat\Controller\Apicp;

use Common\Common\Cache;
use Common\Common\Pager;
use Stat\Model\StatPluginTotalModel;

class PluginController extends AbstractController {

	/** 自定义时间选择 */
	const DAYS_CUSTOM = -1;

	/**
	 * 应用纬度 头部数据
	 * @return bool
	 */
	public function PluginHeader_get() {

		$serv_plugin = D('Stat/StatPluginAllData', 'Service');

		// 获取页面头部数据
		$plugin_all_data = $serv_plugin->get_plugin_header();

		$result = array(
			'header' => $plugin_all_data
		);

		$this->_response($result);

		return true;
	}

	/**
	 * 应用纬度 图表数据
	 * @return bool
	 */
	public function PluginChart_get() {

		$data_type = I('get.type', 1, 'intval'); // 数据类型
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

		$serv_plugin = D('Stat/StatPluginAllData', 'Service');

		// 获取数据
		$name = '';
		$chart_count = array();
		$chart_days = array();
		list($name, $chart_days, $chart_count) = $serv_plugin->chart_data($start_time, $end_time, $data_type);

		$this->_response(
			array(
				'name' => $name,
				'days' => $chart_days,
				'count' => $chart_count
			)
		);

		return true;
	}

	/**
	 * 应用纬度 详情数据
	 * @return bool
	 */
	public function PluginDetail_get() {

		// 获取时间参数
		list($start_time, $end_time, $page_option, $page) = $this->_get_params();
		// 分页参数
		$page = I('get.page', self::DEAFULT_PAGE, 'intval');
		$limit = I('get.limit', self::DEAFULT_LIMIT, 'intval');
		list($start, $limit, $page) = page_limit($page, $limit);
		$page_option = array('start' => $start,'limit' => $limit);

		// 查询
		$serv_plugin_all_data = D('Stat/StatPluginAllData', 'Service');
		$list = array();
		$total = array();
		list($list, $total) = $serv_plugin_all_data->detail_data($start_time, $end_time, $page_option);

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
	 * 应用纬度 应用数据
	 * @return bool
	 */
	public function PluginList_get() {

		// 获取提交参数
		list($start_time, $end_time, $page_option, $page) = $this->_get_params();
		$cp_identifier = $this->_get_identifier();

		$serv_total = D('Stat/StatPluginTotal', 'Service');
		$list = $serv_total->list_by_time_or_identifier($start_time, $end_time, $cp_identifier, $page_option);
		$count = $serv_total->count_by_time_or_identifier($start_time, $end_time, $cp_identifier);

		$pagerOptions = array(
			'total_items' => $count,
			'per_page' => $page_option['limit'],
			'current_page' => $page,
			'show_total_items' => true,
		);
		$multi = Pager::make_links($pagerOptions);

		// 选项
		$select = StatPluginTotalModel::$_identifier_name;

		$result = array(
			'list' => $list,
			'multi' => $multi,
			'select' => $select,
		);

		$this->_response($result);

		return true;
	}

	/**
	 * 获取新增安装企业数据
	 * @return bool
	 */
	public function New_install_ep_list_get() {

		// 获取提交参数
		list($start_time, $end_time, $page_option, $page) = $this->_get_params();
		$cp_identifier = $this->_get_identifier();

		$serv_add = D('Stat/StatPluginAdd', 'Service');
		$list = $serv_add->list_new_install_ep($start_time, $end_time, $cp_identifier, $page_option);
		$count = $serv_add->count_new_install_ep($start_time, $end_time, $cp_identifier);

		$pagerOptions = array(
			'total_items' => $count,
			'per_page' => $page_option['limit'],
			'current_page' => $page,
			'show_total_items' => true,
		);
		$multi = Pager::make_links($pagerOptions);

		// 选项
		$select = StatPluginTotalModel::$_identifier_name;

		$result = array(
			'list' => $list,
			'multi' => $multi,
			'select' => $select,
		);

		$this->_response($result);

		return true;
	}

	/**
	 * 获取新增安装应用 和 企业数据
	 * @return bool
	 */
	public function New_install_plugin_get() {

		// 获取提交参数
		list($start_time, $end_time, $page_option, $page) = $this->_get_params();

		$serv_add = D('Stat/StatPluginAdd', 'Service');
		$list = $serv_add->list_new_install_plugin($start_time, $end_time, $page_option);
		$count = $serv_add->count_new_install_pliugin($start_time, $end_time);

		$pagerOptions = array(
			'total_items' => $count,
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
		$page_option = array('start' => $start,'limit' => $limit);

		return array(
			$start_time,
			$end_time,
			$page_option,
			$page,
		);
	}

	/**
	 * 获取提交的应用标识 identifier
	 * return 应用标识
	 * @return mixed
	 */
	protected function _get_identifier() {

		// 获取选择的应用
		$cp_identifier = I('get.identifier');
		// 判断是否为空或者没有这个标识
//		if (empty($cp_identifier) && !array_key_exists($cp_identifier ,StatPluginTotalModel::$_identifier_name)) {
//				$identifier_array = array_keys(StatPluginTotalModel::$_identifier_name);
//			$cp_identifier = $identifier_array[0];
//		}

		return $cp_identifier;
	}

	/**
	 * 导出应用数据详情
	 * @return bool
	 */
	public function Download_detail_get() {

		// 获取时间参数
		list($start_time, $end_time, $page_option, $page) = $this->_get_params();
		// 查询总数
		$serv = D('Stat/StatPluginAllData', 'Service');
		$total = $serv->count_by_time($start_time, $end_time);
		//空文件
		if ($total == 0) {
			$this->empty_field(array(
				'日期',
				'应用主数据',
				'应用总数据',
				'活跃应用数',
				'活跃企业数',
			  	'活跃员工数',
				'新增应用安装数'
			));
		}
		// 计算次数
		$times = ceil($total / self::DOWNLOAD_LIMIT);
		// 文件参数
		$name = 'detail';
		$title = array('日期','应用主数据', '应用总数据', '活跃应用数', '活跃企业数', '活跃员工数', '新增应用安装数');
		$field = array('date','count_index', 'count_all', 'active_plugin', 'active_ep', 'active_staff', 'new_install');


		$zip = new \ZipArchive();
		$path = get_sitedir() . 'excel/';
		$zipname = $path . '_' . $name . '_' . date('YmdHis', time());
		$zip->open($zipname . '.zip', \ZipArchive::CREATE);

		for ($i = 1; $i <= $times; $i ++) {
			// 分页参数
			list($start, $limit, $i) = page_limit($i, self::DOWNLOAD_LIMIT);
			// 分页参数
			$page_option = array($start, $limit);
			// 查询数据
			$list = $serv->list_by_time($start_time, $end_time, $page_option);

			$excel = '';
			$filepath = $this->_save_exl($list, $excel, $path, $name, $title, $field);

			if ($filepath) {
				$zip->addFile($filepath, $name . $i . '.xls');
			}
		}

		// 下载并清除文件
		$zip->close();
		$this->_put_header($zipname . '.zip');
		$this->_clear($path);
		return true;
	}

	/**
	 * 导出应用列表
	 * @return bool
	 */
	public function Download_plugin_get() {

		// 获取提交参数
		list($start_time, $end_time, $page_option, $page) = $this->_get_params();
		$identifier = $this->_get_identifier();
		// 查询总数
		$serv = D('Stat/StatPluginTotal', 'Service');
		$total = $serv->count_by_time_or_identifier($start_time, $end_time, $identifier);
		//空文件
		if ($total == 0) {
			$this->empty_field(array(
				'应用',
				'时间',
				'安装企业总数',
				'应用活跃人数',
				'应用活跃度',
				'应用主数据',
				'应用总数据',
				'人均贡献值',
				'新增安装企业数',
				'新增活跃员工数'
			));
		}
		// 计算次数
		$times = ceil($total / self::DOWNLOAD_LIMIT);
		// 文件参数
		$name = 'plugin_total';
		$title = array(
			'应用',
			'时间',
			'安装企业总数',
			'应用活跃人数',
			'应用活跃度',
			'应用主数据',
			'应用总数据',
			'人均贡献值',
			'新增安装企业数',
			'新增活跃员工数'
		);
		$field = array(
			'pg_name',
			'time',
			'install_count',
			'active_staff',
			'active_degree',
			'count_index',
			'count_all',
			'pre_devote',
			'new_install',
			'new_active_staff'
		);


		$zip = new \ZipArchive();
		$path = get_sitedir() . 'excel/';
		$zipname = $path . '_' . $name . '_' . date('YmdHis', time());
		$zip->open($zipname . '.zip', \ZipArchive::CREATE);

		for ($i = 1; $i <= $times; $i ++) {
			$list = array();
			// 分页参数
			list($start, $limit, $i) = page_limit($i, self::DOWNLOAD_LIMIT, self::DOWNLOAD_LIMIT);
			// 分页参数
			$page_option = array($start, $limit);
			// 查询数据
			$list = $serv->list_by_time_or_identifier($start_time, $end_time, $identifier, $page_option);
			$excel = '';
			$filepath = $this->_save_exl($list, $excel, $path, $name, $title, $field);

			if ($filepath) {
				$zip->addFile($filepath, $name . $i . '.xls');
			}
		}

		// 下载并清除文件
		$zip->close();
		$this->_put_header($zipname . '.zip');
		$this->_clear($path);

		return true;
	}

	/**
	 * 导出新装应用
	 * @return bool
	 */
	public function Download_new_install_get() {

		// 获取提交参数
		list($start_time, $end_time, $page_option, $page) = $this->_get_params();
		$identifier = $this->_get_identifier();
		// 查询总数
		$serv = D('Stat/StatPluginAdd', 'Service');
		$total = $serv->count_new_install_ep($start_time, $end_time, $identifier);
		//空文件
		if ($total == 0) {
			$this->empty_field(array(
				'应用名称',
				'安装时间',
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
				'注册及创建时间',
				'最后更新时间',
			));
		}
		// 计算次数
		$times = ceil($total / self::DOWNLOAD_LIMIT);
		// 文件参数
		$name = 'plugin_total';
		$title = array(
			'应用名称',
			'安装时间',
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
			'注册及创建时间',
			'最后更新时间',
		);
		$field = array(
			'pg_name',
			'time',
			'ep_name',
			'ep_mobilephone',
			'ep_industry',
			'customer_status',
			'ep_customer_level',
			'ep_companysize',
			'ep_ref',
			'bangding',
			'ca_name',
			'pay_status',
			'ep_created',
			'ep_updated',
		);


		$zip = new \ZipArchive();
		$path = get_sitedir() . 'excel/';
		$zipname = $path . '_' . $name . '_' . date('YmdHis', time());
		$zip->open($zipname . '.zip', \ZipArchive::CREATE);

		for ($i = 1; $i <= $times; $i ++) {
			// 分页参数
			list($start, $limit, $i) = page_limit($i, self::DOWNLOAD_LIMIT, self::DOWNLOAD_LIMIT);
			// 分页参数
			$page_option = array($start, $limit);
			// 查询数据
			$list = $serv->list_new_install_ep($start_time, $end_time, $identifier, $page_option);

			$excel = '';
			$filepath = $this->_save_exl($list, $excel, $path, $name, $title, $field);

			if ($filepath) {
				$zip->addFile($filepath, $name . $i . '.xls');
			}
		}

		// 下载并清除文件
		$zip->close();
		$this->_put_header($zipname . '.zip');
		$this->_clear($path);

		return true;
	}

	/**
	 * 导出新装应用企业
	 * @return bool
	 */
	public function Download_new_install_ep_get() {

		// 获取提交参数
		list($start_time, $end_time, $page_option, $page) = $this->_get_params();
		$identifier = $this->_get_identifier();
		// 查询总数
		$serv = D('Stat/StatPluginAdd', 'Service');
		$total = $serv->count_new_install_ep($start_time, $end_time, $identifier);
		//空文件
		if ($total == 0) {
			$this->empty_field(array(
				'应用名',
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
				'注册及创建时间',
				'最后更新时间',
			));
		}
		if ($total == 0) {
			$this->empty_field($this->new_pay_field);
		}
		// 计算次数
		$times = ceil($total / self::DOWNLOAD_LIMIT);
		// 文件参数
		$name = 'plugin_total';
		$title = array(
			'应用名',
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
			'注册及创建时间',
			'最后更新时间',
		);
		$field = array(
			'pg_name',
			'ep_name',
			'ep_mobilephone',
			'ep_industry',
			'customer_status',
			'ep_customer_level',
			'ep_companysize',
			'ep_ref',
			'bangding',
			'ca_name',
			'pay_status',
			'ep_created',
			'ep_updated',
		);


		$zip = new \ZipArchive();
		$path = get_sitedir() . 'excel/';
		$zipname = $path . '_' . $name . '_' . date('YmdHis', time());
		$zip->open($zipname . '.zip', \ZipArchive::CREATE);

		for ($i = 1; $i <= $times; $i ++) {
			// 分页参数
			list($start, $limit, $i) = page_limit($i, self::DOWNLOAD_LIMIT, self::DOWNLOAD_LIMIT);
			// 分页参数
			$page_option = array($start, $limit);
			// 查询数据
			$list = $serv->list_new_install_ep($start_time, $end_time, $identifier, $page_option);

			$excel = '';
			$filepath = $this->_save_exl($list, $excel, $path, $name, $title, $field);

			if ($filepath) {
				$zip->addFile($filepath, $name . $i . '.xls');
			}
		}

		// 下载并清除文件
		$zip->close();
		$this->_put_header($zipname . '.zip');
		$this->_clear($path);

		return true;
	}

	/**
	 * 保存单个xls
	 * @param $excel
	 * @param $path
	 * @param $name
	 * @param $title
	 * @param $field
	 * @return string
	 */
	protected function _save_exl($list, $excel, $path, $name, $title, $field) {

		$excel = new \Com\Excel();
		// 横坐标
		$data = array();
		$data[] = $title;

		foreach ($list as $_value) {
			$temp = array();
			// 导出的字段
			foreach ($field as $_field) {
				$temp[] = $_value[$_field];
			}
			$data[] = $temp;
			unset($temp);
		}

		// 填充表格信息
		$a_z = array();
		for ($i = 65; $i <=90; $i ++) {
			$a_z[] = chr($i);
		}
		for ($i = 1; $i <= count($data); $i ++) {
			$j = 0;
			foreach ($data[$i - 1] as $_key => $_val) {
				$excel->getActiveSheet()->setCellValue("$a_z[$j]$i", "$_val");
				$j ++;
			}
		}

		$write = new \PHPExcel_Writer_Excel5($excel);
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header('Content-Disposition:attachment;filename="'. $name . $i . '.xls"');
		header("Content-Transfer-Encoding:binary");

		if (!is_dir($path)) {
			mkdir($path);
		}

		$write->save($path . $name . $i . ".xls");
		$filepath = $path . $name . $i . '.xls';

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
	 * 单个企业数据 应用数据 昨日数据接口
	 * @return array
	 */
	public function PluginYestaday_get() {

		$params = I('get.');

		//判断是否获取到公司id
		if (is_null($params['ep_id'])) {
			return false;
		}

		//获取公司id
		$epid = $params['ep_id'];

		$serv = D('Stat/StatPluginDaily', 'Service');
		$result = $serv->get_by_cond_lastday($epid);
		return $this->_response($result);

	}

	/*
	 *
	 * 单个企业数据 数据详情
	 */
	 public function PluginView_get(){

		 $params = I('get.');

		 //判断是否获取到公司id
		 if (is_null($params['ep_id'])) {
			 return false;
		 }

		 //获取公司id
		 $ep_id = $params['ep_id'];

		 // 获取提交参数
		 list($start_time, $end_time, $page_option, $page) = $this->_get_params();

		 //查询
		 $serv = D('Stat/StatPluginDaily', 'Service');
		 $list = array();
		 $total = array();
		 list($list, $total) = $serv->list_by_epid_view($start_time, $end_time, $ep_id, $page_option);

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

	/*
	 * 应用/套件 数据详情
	 */
	public function PluginData_get(){

		$params = I('get.');

		//判断是否获取到公司id
		if (is_null($params['ep_id'])) {
			return false;
		}

		//获取公司id
		$ep_id = $params['ep_id'];

		// 获取提交参数
		list($start_time, $end_time, $page_option, $page) = $this->_get_params();


		//获得应用标识
		$_identifier = $params['identifier'];

		if(empty($_identifier)) {
			$serv_all = D('Stat/StatPluginDaily', 'Service');
			list($list,$total) = $serv_all->list_by_all_view($start_time, $end_time, $ep_id, $page_option);

		}
		else {
			$serv_daily = D('Stat/StatPluginDaily', 'Service');
			list($list, $total) = $serv_daily->list_by_identifier_view($start_time, $end_time, $ep_id, $_identifier, $page_option);
		}

		$pagerOptions = array(
			'total_items' => $total,
			'per_page' => $page_option['limit'],
			'current_page' => $page,
			'show_total_items' => true,
		);
		$multi = Pager::make_links($pagerOptions);
		// 选项
		$select = StatPluginTotalModel::$_identifier_name;

		$result = array(
			'list' => $list,
			'multi' => $multi,
			'select' => $select,
		);

		$this->_response($result);

		return true;
	}

	/*
	 * 应用/套件 获取分页
	 */
	public function PluginSelect_get(){

		$params = I('get.');

		//判断是否获取到公司id
		if (is_null($params['ep_id'])) {
			return false;
		}

		//获取公司id
		$ep_id = $params['ep_id'];

		// 获取提交参数
		list($start_time, $end_time, $page_option, $page) = $this->_get_params();


		//获得应用标识
		$_identifier = $params['identifier'];

		if(empty($_identifier)) {
			$serv_all = D('Stat/StatPluginDaily', 'Service');
			list($list,$total) = $serv_all->list_by_all_view($start_time, $end_time, $ep_id, $page_option);

		}
		else {
			$serv_daily = D('Stat/StatPluginDaily', 'Service');
			list($list, $total) = $serv_daily->list_by_identifier_view($start_time, $end_time, $ep_id, $_identifier, $page_option);
		}

		$pagerOptions = array(
			'total_items' => $total,
			'per_page' => $page_option['limit'],
			'current_page' => $page,
			'show_total_items' => true,
		);
		$multi = Pager::make_links($pagerOptions);
		// 选项
		$select = StatPluginTotalModel::$_identifier_name;

		$result = array(
			'list' => $list,
			'multi' => $multi,
			'select' => $select,
		);

		$this->_response($result);

		return true;
	}

	/**
	 * 单个企业 应用数据 图表数据
	 * @return bool
	 */
	public function PluginViewChart_get() {

		$data_type = I('get.field', 1, 'intval'); // 数据类型
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

		$serv_plugin = D('Stat/StatPluginDaily', 'Service');

		// 获取数据
		$name = '';
		$chart_count = array();
		$chart_days = array();
		list($name, $chart_days, $chart_count) = $serv_plugin->plugin_chart_data($start_time, $end_time, $data_type, $ep_id);
		$result = array(
			'name' => $name,
			'days' => $chart_days,
			'count' => $chart_count
		);

		$this->_response($result);

		return true;
	}

	/**
	 * 导出单个企业 应用数据详情
	 * @return bool
	 */
	public function Download_plugin_detail_get() {

		$params = I('get.');
		//获取时间范围
		$this->get_range_time($params);
		//判断是否获取到公司id
		if (empty($params['ep_id'])) {
			return false;
		}

		//获取公司id
		$ep_id = $params['ep_id'];

		// 获取时间参数
		list($start_time, $end_time, $page_option, $page) = $this->_get_params();
		//查询
		$serv = D('Stat/StatPluginDaily', 'Service');

		list($list, $total) = $serv->list_by_epid_view($start_time, $end_time, $ep_id);
		//空文件
		if ($total == 0) {
			$this->empty_field(array(
				'日期',
				'应用安装数',
				'应用主数据',
				'应用总数据',
				'应用活跃员工数',
				'新增活跃员工数',
			));
		}
		$list = array();
		// 计算次数
		$times = ceil($total / self::DOWNLOAD_LIMIT);
		// 文件参数
		$name = 'detail';
		$title = array(
			'日期',
			'应用安装数',
			'应用主数据',
			'应用总数据',
			'应用活跃员工数',
			'新增活跃员工数',
		);
		$field = array(
			'date',
			'install_plugin',
			'count_index',
			'count_all',
			'active_staff',
			'add_staff',
		);

		$zip = new \ZipArchive();
		$path = get_sitedir() . 'excel/';
		$zipname = $path . '_' . $name . '_' . date('YmdHis', time());
		$zip->open($zipname . '.zip', \ZipArchive::CREATE);


		for ($i = 1; $i <= $times; $i ++) {
			// 分页参数
			list($start, $limit, $i) = page_limit($i, self::DOWNLOAD_LIMIT, self::DOWNLOAD_LIMIT);
			// 分页参数
			$page_option = array($start, $limit);
			list($list, $total) = $serv->list_by_epid_view($start_time, $end_time, $ep_id, $page_option);
			$excel = '';
			$filepath = $this->_save_exl($list, $excel, $path, $name, $title, $field);

			if ($filepath) {
				$zip->addFile($filepath, $name . $i . '.xls');
			}
		}

		// 下载并清除文件
		$zip->close();
		$this->_put_header($zipname . '.zip');
		$this->_clear($path);
		return true;
	}

	/**
	 * 导出单个企业 套件/应用数据详情
	 * @return bool
	 */
	public function Download_plugin_data_get() {

		$params = I('get.');
		//获取时间范围
		$this->get_range_time($params);
		//判断是否获取到公司id
		if (is_null($params['ep_id'])) {
			return false;
		}

		//获取公司id
		$ep_id = $params['ep_id'];

		// 获取时间参数
		list($start_time, $end_time, $page_option, $page) = $this->_get_params();
		//获得应用标识
		$_identifier = $this->_get_identifier();
		$total = 0;
		if($_identifier == 'all') {
			$serv_all = D('Stat/StatPluginDaily', 'Service');
			list($list,$total) = $serv_all->list_by_all_view_out($start_time, $end_time, $ep_id);

		}
		else {
			$serv_daily = D('Stat/StatPluginDaily', 'Service');
			list($list, $total) = $serv_daily->list_by_identifier_view_out($start_time, $end_time, $ep_id, $_identifier);
		}
		//空文件
		if ($total == 0) {
			$this->empty_field(array(
				'应用名称',
				'日期',
				'新增活跃员工数',
				'应用活跃员工数',
				'应用活跃度',
				'应用主数据',
				'应用总数据',
				'人均贡献量',
			));
		}
		// 计算次数
		$times = ceil($total / self::DOWNLOAD_LIMIT);
		// 文件参数
		$name = 'detail';
		$title = array(
			'应用名称',
			'日期',
			'新增活跃员工数',
			'应用活跃员工数',
			'应用活跃度',
			'应用主数据',
			'应用总数据',
			'人均贡献量',
		);
		$field = array(
			'pg_name',
			'date',
			'add_staff',
			'active_staff',
			'active_percent',
			'count_index',
			'count_all',
			'pre_devote',
		);

		$zip = new \ZipArchive();
		$path = get_sitedir() . 'excel/';
		$zipname = $path . '_' . $name . '_' . date('YmdHis', time());
		$zip->open($zipname . '.zip', \ZipArchive::CREATE);


		for ($i = 1; $i <= $times; $i ++) {

			// 分页参数
			list($start, $limit, $i) = page_limit($i, self::DOWNLOAD_LIMIT, self::DOWNLOAD_LIMIT);
			// 分页参数
			$page_option = array($start, $limit);
			if($_identifier == 'all') {
				$serv_all = D('Stat/StatPluginDaily', 'Service');
				list($list,$total) = $serv_all->list_by_all_view_out($start_time, $end_time, $ep_id, $page_option);

			}
			else {
				$serv_daily = D('Stat/StatPluginDaily', 'Service');
				list($list, $total) = $serv_daily->list_by_identifier_view_out($start_time, $end_time, $ep_id, $_identifier, $page_option);
			}
			$excel = '';
			$filepath = $this->_save_exl($list, $excel, $path, $name, $title, $field);

			if ($filepath) {
				$zip->addFile($filepath, $name . $i . '.xls');
			}
		}

		// 下载并清除文件
		$zip->close();
		$this->_put_header($zipname . '.zip');
		$this->_clear($path);
		return true;
	}


}