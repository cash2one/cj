<?php

/**
 * voa_c_admincp_office_thread_base
 * 企业后台/同事社区/基本控制器
 * Create By gaosong
 * $Author$
 * $Id$
 */
class voa_c_admincp_office_travel_base extends voa_c_admincp_office_base
{

	// 插件名称
	protected $_pluginname = 'travel';
	// 表格名称
	protected $_tname = '';
	// uda's ptname
	protected $_ptname = array();

	/**
	 * excel 的列名定义，注意排序顺序(商品)
	 * @var array
	 */
	public $_excel_fields = array(
			'classname' => array('name'=>'分类', 'width'=>14,),
			'goodsnum' => array('name'=>'编号', 'width'=>18,),
			'price' => array('name'=>'价格', 'width'=>14,),
			'amount' => array('name'=>'库存', 'width'=>30,),
			'subject' => array('name'=>'标题', 'width'=>14,),
			'created' => array('name'=>'录入时间', 'width'=>14,),
			'percentage' => array('name'=>'提成比例%', 'width'=>12,)
	);

	/**
	 * excel 的列名定义，注意排序顺序(业绩与提成)
	 * @var array
	 */
	public $_excel_fields_turnover = array(
			'salename' => array('name'=>'员工姓名', 'width'=>14),
			'cd_id' => array('name'=>'部门', 'width'=>18),
			'price' => array('name'=>'业绩', 'width'=>30),
			'profit' => array('name'=>'提成', 'width'=>14)

	);

	/**
	 * excel 的列名定义，注意排序顺序(订单)
	 * @var array
	*/
	public $_excel_fields_order = array(
			'ordersn' => array('name'=>'订单编号', 'width'=>30),
			'_created' => array('name'=>'下单时间', 'width'=>18),
			'amount' => array('name'=>'金额(元)', 'width'=>14),
			'_order_status' => array('name'=>'订单状态', 'width'=>14),
			'customer_name' => array('name'=>'客户姓名', 'width'=>14),
			'mobile' => array('name'=>'客户电话', 'width'=>14),
			'address' => array('name'=>'收货地址', 'width'=>50),
			'goods_names' => array('name'=>'商品名称', 'width'=>50),
			'goods_goodsnum' => array('name'=>'货号', 'width'=>14),
			'style_name' => array('name'=>'规格', 'width'=>14),
			'salename' => array('name'=>'所属Kol', 'width'=>14),
			'num' => array('name'=>'数量', 'width'=>14),
			'nums' => array('name'=>'总数量', 'width'=>14)
	);


	public function __construct() {

		parent::__construct();
	}

	protected function _before_action($action) {

		if (!parent::_before_action($action)) {
			return false;
		}
		/** 读取站点配置 */
		$this->_p_sets = voa_h_cache::get_instance()->get('plugin.'.$this->_pluginname.'.setting', 'oa');

		$this->_init_ptname();
		return true;
	}

	protected function _after_action($action) {

		parent::_after_action($action);
		return true;
	}

	protected function _init_ptname() {

		$this->_ptname = array(
				'plugin' => $this->_pluginname,
				'table' => $this->_p_sets['goods_table_name']
		);
	}

	/**
	 * 输出 phpExcel 需要用到的已格式化了的数据
	 * @param array $data
	 * @param string $departments
	 * @param string $jobs
	 * @return array
	 */
	protected function _excel_data($data, $departments = false, $jobs = false){
		$init_fields = $this->_excel_fields;
		$field2colnum = array();//字段与excel列字母对应关系
		$titleString = array();//excel 标题栏文字
		$titleWidth = array();//excel 标题栏宽度
		$excelData = array();//excel 行数据
		$ord = 65;//第一列字母A的ASCII码值
		foreach ($init_fields AS $key=>$arr) {
			$colCode = chr($ord);
			$field2colnum[$key] = $colCode;
			$titleString[$colCode] = $arr['name'];
			$titleWidth[$colCode] = $arr['width'];
			$ord++;
		}

		$i = 0;
		$departments = false;
		$jobs = false;
		$serv_st = new voa_s_oa_travel_styles();
		foreach ($data AS $row) {
			//读取规格
			$tmpstyle = $serv_st->list_by_conds(array(
					'goodsid' => $row['dataid'],
					'state' => voa_d_oa_travel_styles::STATE_USING
			));
			if(!empty($tmpstyle)){
				$tmpstyle = array_values($tmpstyle);
				$row['amount'] = $tmpstyle[0]['amount'];
			}
			foreach ($field2colnum AS $k => $col) {
				$excelData[$i][$col] = isset($row[$k]) ? $row[$k] : '';
			}
			$i++;
		}
		return array($titleString,$titleWidth,$excelData);
	}

	/**
	 * 输出 phpExcel 需要用到的已格式化了的数据(业绩与提成列表)
	 * @param array $data
	 * @param string $departments
	 * @param string $jobs
	 * @return array
	 */
	protected function _excel_data_turnover($data, $departments = false, $jobs = false){
		$init_fields = $this->_excel_fields_turnover;
		$field2colnum = array();//字段与excel列字母对应关系
		$titleString = array();//excel 标题栏文字
		$titleWidth = array();//excel 标题栏宽度
		$excelData = array();//excel 行数据
		$ord = 65;//第一列字母A的ASCII码值
		foreach ($init_fields AS $key=>$arr) {
			$colCode = chr($ord);
			$field2colnum[$key] = $colCode;
			$titleString[$colCode] = $arr['name'];
			$titleWidth[$colCode] = $arr['width'];
			$ord++;
		}

		$i = 0;
		$departments = false;
		$jobs = false;
		//读取部门列表
		$departments = $this->_department_list();
		foreach ($data AS $row) {
			$row['cd_id'] = $departments[$row['cd_id']]['cd_name'];
			foreach ($field2colnum AS $k => $col) {
				$excelData[$i][$col] = isset($row[$k]) ? $row[$k] : '';
			}
			$i++;
		}
		return array($titleString,$titleWidth,$excelData);
	}

	/**
	 * 输出 phpExcel 需要用到的已格式化了的数据(订单列表)
	 * @param array $data
	 * @param string $departments
	 * @param string $jobs
	 * @return array
	 */
	protected function _excel_data_order($data, $departments = false, $jobs = false){
		$init_fields = $this->_excel_fields_order;
		$field2colnum = array();//字段与excel列字母对应关系
		$titleString = array();//excel 标题栏文字
		$titleWidth = array();//excel 标题栏宽度
		$excelData = array();//excel 行数据
		$ord = 65;//第一列字母A的ASCII码值
		foreach ($init_fields AS $key=>$arr) {
			$colCode = chr($ord);
			$field2colnum[$key] = $colCode;
			$titleString[$colCode] = $arr['name'];
			$titleWidth[$colCode] = $arr['width'];
			$ord++;
		}
		$i = 0;
		$departments = false;
		$jobs = false;
		foreach ($data AS $row) {
			$row['amount'] = $row['amount']/100;
			foreach ($field2colnum AS $k => $col) {
				$excelData[$i][$col] = isset($row[$k]) ? $row[$k] : '';
			}
			$i++;
		}
		return array($titleString,$titleWidth,$excelData);
	}
	
	/**
	 * 下载输出至浏览器
	 */
	protected function put_header($zipname){
		if(!file_exists($zipname))exit("下载失败");
		$file = fopen($zipname,"r");
		Header("Content-type: application/octet-stream");
		Header("Accept-Ranges: bytes");
		Header("Accept-Length: ".filesize($zipname));
		Header("Content-Disposition: attachment; filename=".basename($zipname));
		echo fread($file, filesize($zipname));
		$buffer=1024;
		while (!feof($file)) {
			$file_data=fread($file,$buffer);
			echo $file_data;
		}
		fclose($file);
	}
	
	/**
	 * 清理产生的临时文件
	 */
	protected function clear($path){
		$dh=opendir($path);
		while ($file=readdir($dh)) {
			if($file!="." && $file!="..") {
				unlink($path.$file);
			}
		}
	}
}
