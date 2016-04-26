<?php
/**
 * voa_c_admincp_office_travel_order
 * 企业后台/微办公管理/营销CRM/订单列表
 * Create By linshiling
 * $Author$
 * $Id$
 */
/*ini_set('display_errors', 1);
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
header('Content-Type:text/html;charset=utf-8');
ini_set('date.timezone','Asia/Shanghai');
function d($d) {echo '<pre>';print_r($d);echo '</pre>';}*/
class voa_c_admincp_office_travel_order extends voa_c_admincp_office_travel_base {

	/** 可允许的动作集 */
	private $__action_names = array(
			'downloadtpl' => '下载模板文件',
			'batch' => '批量导入',
			'uploadexcel' => '上传 Excel 文件',
			'import' => '导入快递单数据',
			'batchsubmit' => '批量提交',
			'resubmit' => '重新提交'
	);

	/** 模板字段定义 */
	private $__fields = array (
			'ordersn' => array('name' => '订单编号'),
			'express' => array('name' => '快递公司'),
			'expressn' => array('name' => '快递单号')
	);

	/** 临时存储目录路径 */
	private $__tmp_path = '';
	/** 临时导入的数据储存路径 */
	private $__tmp_data_path = '';

	public function execute() {

		$this->view->set('pluginid', $this->_module_plugin_id);
		$act = $this->request->get('act');
		//加载子动作
		if($act)  {
			$this->$act();
			exit;
		}

		$searchDefault = array(
				'orderid' => '',
				'ordersn' => '',
				'goods_name' => '',
				'created_begintime' => '',
				'created_endtime' => '',
				'order_status'	=>	'',
				'mobile'	=>	'',
				'sale_name'	=>	'',
				'customer_name'	=>	''
		);
		$searchBy = array();
		$conditions = array();
		$this->_parse_search_cond($searchDefault, $searchBy, $conditions);
		$issearch = $this->request->get('issearch') ? 1 : 0;

		$limit = 12;   // 每页显示数量
		$page = $this->request->get('page');   // 当前页码
		if (!is_numeric($page) || $page < 1) {
			$page = 1;
		}

		// 载入uda类
		$uda = &uda::factory('voa_uda_frontend_travel_order');

		// 实际查询条件
		$conditions = $issearch ? $conditions : array();
		$list = array();
		$total = 0;
		if (!$uda->get_order_list($conditions, $page, $limit, $list, $total)) {
			$this->message('error', $uda_search->errmsg.'[Err:'.$uda_search->errcode.']');
			return;
		}
		foreach ($list as & $l)
		{
			$l['_amount'] = ($l['amount'] / 100).'元';
			$l['_created'] = rgmdate($l['created'], 'Y-m-d H:i');
			$l['_status'] = $uda->status($l['order_status']);
		}

		// 分页链接信息
		$multi = '';
		if ($total > 0) {
			// 输出分页信息
			$multi = pager::make_links(array(
				'total_items' => $total,
				'per_page' => $limit,
				'current_page' => $page,
				'show_total_items' => true,
			));
		}

		// 注入模板变量
		$this->view->set('total', $total);
		$this->view->set('list', $list);
		$this->view->set('multi', $multi);
		$this->view->set('issearch', $this->request->get('issearch'));
		$this->view->set('searchBy', array_merge($searchDefault, $searchBy));
		$prev = '/admincp/office/travel/order/pluginid/'.$this->_module_plugin_id.'/';
		$this->view->set('prev', $prev);
		$this->view->set('status', $uda->status());


		// 输出模板
		$this->output('office/customize/order');
	}

	/**
	 * 重构搜索条件
	 * @param array $searchDefault 初始条件
	 * @param array $searchBy 输入的查询条件
	 * @param array $conditons 组合的查询
	 */
	protected function _parse_search_cond($searchDefault, &$searchBy, &$conditons) {
		foreach ( $searchDefault AS $_k=>$_v ) {
			if ( isset($_GET[$_k]) && $_v != $this->request->get($_k) ) {
				$searchBy[$_k] = $this->request->get($_k);
				if ($_k == 'created_begintime') {
					$conditons['created>?'] = rstrtotime($this->request->get($_k));
				} elseif ($_k == 'created_endtime') {
					$conditons['created<?'] = rstrtotime($this->request->get($_k)) + 86400;
				} elseif ($_k == 'goods_name') {
					$conditons['goods_name LIKE ?'] = '%'.($this->request->get($_k)).'%';
				}else {
					$conditons[$_k] = ($this->request->get($_k));
				}
			}
		}
		return true;
	}

	/**
	 * 编辑订单
	 *
	 */
	public function detail()
	{
		$id = $this->request->get('id');
		//载入uda类
		$uda = &uda::factory('voa_uda_frontend_travel_order');
		$rs = $uda->get_order($id, $order);
		if (!$rs) {
			$this->message('error', '无此订单');
			return;
		}
		$order['created'] && $order['_created'] = rgmdate($order['created']);
		$order['pay_time'] && $order['_pay_time'] = rgmdate($order['pay_time']);
		$order['complete_time'] && $order['_complete_time'] = rgmdate($order['complete_time']);
		if (!empty($order['price'])) {
			$order['_price'] = $order['price'] / 100;
		}
		$order['_amount'] = $order['amount'] / 100;
		$order['_status'] = $uda->status($order['order_status']);

		//获取规格
		if (!empty($order['style_id'])) {
			$s = new voa_d_oa_travel_styles();
			$sty = $s->get($order['style_id']);
			$order['guige'] = $sty['stylename'];
		}

		//获取产品列表
		$g = new voa_d_oa_travel_ordergoods();
		$goods_list = $g->list_by_conds(array('order_id' => $id));
		$uids = array();
		if ($goods_list) {
			foreach ($goods_list as & $g)
			{
				$g['_price'] = $g['price'] / 100;
				$g['_amount'] = $g['price'] * $g['num'] / 100;
				$uids[$g['saleuid']] = $g['saleuid'];
			}
		}

		/** 用户信息 */
		$servm = &service::factory('voa_s_oa_member', array('pluginid' => 0));
		$users = $servm->fetch_all_by_ids(array_keys($uids));

		$prev = '/admincp/office/travel/order/pluginid/'.$this->_module_plugin_id.'/';
		$this->view->set('prev', $prev);
		$this->view->set('order', $order);
		$this->view->set('users',$users);
		$this->view->set('goods_list', $goods_list);
		$this->view->set('status', $uda->status());

		// 输出模板
		$this->output('office/customize/order_edit');
	}

	/**
	 * 删除订单
	 *
	 */
	public function delete()
	{
		$id = $this->request->get('id');
		//载入uda类
		$uda = &uda::factory('voa_uda_frontend_travel_order');
		$rs = $uda->get_order($id, $order);
		if (!$rs) {
			echo json_encode(array('state' => 0, 'msg' => '无此订单'));
			return;
		}
		if($order['status'] == 3) {
			echo json_encode(array('state' => 1, 'msg' => '此订单已删除'));
			return;
		}
		$rs = $uda->delete($id);
		if($rs) {
			echo json_encode(array('state' => 1, 'msg' => '删除成功'));
		}else{
			echo json_encode(array('state' => 0, 'msg' => '删除失败'));
		}
		exit;
	}

	/**
	 * 修改状态,并保存日志
	 *
	 */
	public function log()
	{

		$orderid = (int) $this->request->get('orderid');
		$new_status = (int) $this->request->get('new_status');
		$memo = $this->request->get('memo');
		if(!$orderid || !$new_status) {
			echo json_encode(array('state' => 0, 'msg' => '参数错误'));
			exit;
		}
		//载入uda类
		$uda = &uda::factory('voa_uda_frontend_travel_order');
		$order = array();
		$rs = $uda->get_order($orderid, $order);
		if (!$rs) {
			echo json_encode(array('state' => 0, 'msg' => '无此订单'));
			exit;
		}
		$old_status = $order['order_status'];
		if($new_status == $old_status) {
			echo json_encode(array('state' => 1, 'msg' => '已经修改'));
			exit;
		}

		$rs = $uda->change_status($order, $new_status, $old_status, $memo, $this->_user['ca_id'], $this->_user['ca_username']);
		if($rs) {
			echo json_encode(array('state' => 1, 'msg' => '修改状态成功'));
		}else{
			echo json_encode(array('state' => 0, 'msg' => $uda->errmsg));
		}
		exit;
	}

	/**
	 * 记录修改快递日志
	 */
	public function log_express()
	{

		$orderid = (int) $this->request->get('orderid');
		$express = $this->request->get('express');//快递公司
		$expressn = $this->request->get('expressn');//快递单号
		$memo = $this->request->get('memo').'<br />';
		if(!$orderid || !$express ||!$expressn) {
			echo json_encode(array('state' => 0, 'msg' => '参数错误'));
			exit;
		}
		//载入uda类
		$uda = &uda::factory('voa_uda_frontend_travel_order');
		$order = array();
		$rs = $uda->get_order($orderid, $order);
		if (!$rs) {
			echo json_encode(array('state' => 0, 'msg' => '无此订单'));
			exit;
		}

		$old_express = $order['express'];
		if ($old_express != $express) {
			$memo =$memo.$old_express.'改为'.$express.'<br />';
			$order['express'] = $express;
		}

		$old_expressn = $order['expressn'];
		if ($old_expressn != $expressn) {
			$memo =$memo.$old_expressn.'改为'.$expressn;
			$order['expressn'] = $expressn;
		}

		$rs = $uda->change_status($order, $order['order_status'], $order['order_status'], $memo, $this->_user['ca_id'], $this->_user['ca_username']);
		if($rs) {
			echo json_encode(array('state' => 1, 'msg' => '修改状态成功'));
		}else{
			echo json_encode(array('state' => 0, 'msg' => $uda->errmsg));
		}
		exit;
	}



	/**
	 * 加载操作日志
	 *
	 */
	public function loadlog()
	{
		$orderid = (int) $this->request->get('orderid');
		if(!$orderid) {
			echo json_encode(array('state' => 0, 'msg' => '参数错误'));
			exit;
		}
		//载入uda类
		$uda = &uda::factory('voa_uda_frontend_travel_order');
		$list = array();
		$rs = $uda->loadlog($orderid, $list);
		if(!$rs) {
			echo json_encode(array('state' => 0, 'msg' => $uda->errmsg));
			exit;
		}
		echo json_encode(array('state' => 1, 'msg' => $list));
	}

	/**
	 * 订单导入
	 */
	private function imporder()
	{
		$ac= $this->request->get('ac');
		if (isset($this->__action_names[$ac])) {

			// 上传以及处理文件时的临时目录路径
			$this->__tmp_path = voa_h_func::get_sitedir(startup_env::get('domain'));
			if (!in_array(substr($this->__tmp_path, -1), array('\\', '/'))) {
				$this->__tmp_path .= DIRECTORY_SEPARATOR;
			}
			$this->__tmp_path .= 'temp'.DIRECTORY_SEPARATOR;

			// 临时数据储存路径文件位置
			$this->__tmp_data_path = $this->__tmp_path.'data_order.php';


			$ac = '__' . $ac;
			return $this->$ac();
		}

		//下载模板
		$this->view->set('download_tpl_url', $this->cpurl($this->_module, $this->_operation, 'order', $this->_module_plugin_id, array('act' => 'imporder','ac' => 'downloadtpl'),false));
		$this->view->set('batch_url', $this->cpurl($this->_module, $this->_operation, 'order', $this->_module_plugin_id, array('act' => 'imporder','ac' => 'batch'), false));
		$this->view->set('uploadexcel_url', $this->cpurl($this->_module, $this->_operation, 'order', $this->_module_plugin_id, array('act' => 'imporder','ac' => 'uploadexcel'),false));
		$this->view->set('import_url', $this->cpurl($this->_module, $this->_operation, 'order', $this->_module_plugin_id, array('act' => 'imporder','ac' => 'import'),false));
		$this->view->set('resubmit_url', $this->cpurl($this->_module, $this->_operation, 'order', $this->_module_plugin_id, array('act' => 'imporder','ac' => 'resubmit'),false));
		$prev = '/admincp/office/travel/order/pluginid/'.$this->_module_plugin_id.'/';
		$this->view->set('prev', $prev);

		// 输出模板
		$this->output('office/customize/order_imp');
	}

	/**
	 * 下载批量模板
	 */
	private function __downloadtpl() {

		// 标题栏样式定义
		$options = array(
				'title_text_color' => 'FFFFFF00',
				'title_background_color' => 'FF808000',
		);
		// 下载的文件名
		$filename = '畅移云工作_快递单批量导入';
		// 标题文字 和 标题栏宽度
		$title_width = array('20','20','15');
		$title_string = array();

		foreach ($this->__fields as $field) {
			$title_string[] = $field['name'];
		}

		// 默认数据
		$row_data = array();
		// 载入 Excel 类
		excel::make_excel_download($filename, $title_string, $title_width, $row_data, $options);
		return;
	}

	/**
	 * 批量添加快递单
	 */
	private function __batch() {

		$post = $this->request->postx();

		$submit['ordersn'] = isset($post['ordersn']) ? $post['ordersn'] : '';
		$submit['express'] = isset($post['express']) ? $post['express'] : '';
		$submit['expressn'] = isset($post['expressn']) ? $post['expressn'] : '';


		if (empty($submit['ordersn'])) {
			$this->_json_message('1000', '订单编号为空');
		}

		if (empty($submit['express'])) {
			$this->_json_message('1000', '快递公司为空');
		}

		if (empty($submit['expressn'])) {
			$this->_json_message('1000', '快递编号为空');
		}

		//载入uda类
		$uda = &uda::factory('voa_uda_frontend_travel_order');
		$order = $uda->get_by_conds(array('ordersn'=>$submit['ordersn']));

		if (!$order) {
			$this->_json_message('1000', '订单编号错误');
		}

		if ($order['order_status']== voa_d_oa_travel_order::$PAY_SECCESS
				||$order['order_status']== voa_d_oa_travel_order::$PAY_SEND) {
			$submit['order_status'] = voa_d_oa_travel_order::$PAY_SEND;

			//导入成功，发送消息(服务号发送消息)
			if ($order['expressn'] != $submit['expressn']
					||$order['ordersn'] != $submit['ordersn']||$order['express'] != $submit['express']) {
				$order['expressn'] = $submit['expressn'];//设置快递单号
				$order['express'] = $submit['express'];//设置快递公司
				$order['ordersn'] = $submit['ordersn'];//设置快递编号
				$uda->send_msg($order, 'import', startup_env::get('wbs_uid'), $this->session);
			}

			if ($uda->update($order['orderid'],$submit)) {
				$this->_json_message();
			} else {
				$this->_json_message('1000', '导入失败');
			}
		}else{
			$this->_json_message('1000', '订单状态错误');
		}
	}

	/**
	 * 上传 excel 文件
	 * @return void
	 */
	private function __uploadexcel() {

		$current_config = array();
		// 储存根目录
		$current_config['save_dir_path'] = $this->__tmp_path;
		if (!is_dir($current_config['save_dir_path'])) {
			rmkdir($current_config['save_dir_path'], 0777, true);
		}
		// 允许上传的附件类型
		$current_config['allow_files'] = array('xls');
		// 储存附件的文件名格式
		$current_config['file_name_format'] = 'auto';
		// 允许上传的文件最大尺寸
		$current_config['max_size'] = config::get(startup_env::get('app_name').'.attachment.max_size');
		// 源文件名
		$current_config['source_name'] = isset($_POST['fileName']) ? $_POST['fileName'] : 'x.xsl';
		// 储存格式
		$current_config['file_name_format'] = '{yyyy}{mm}{dd}{hh}{ii}{ss}{rand:8}';
		// 上传文件
		$upload = new upload('upload', $current_config, 'upload');
		// 上传后的文件信息
		$result = $upload->get_file_info();
		if (!empty($result['error_code'])) {
			$this->_json_message($result['error_code'], $result['error']);
			return true;
		}

		// 上传的文件位置
		$file = $result['file_path'];

		// 解析 Excel 文件
		$excel = new excel();
		$excel_parse_data = $excel->parse_xsl($file, 0, $this->__fields, 0, 1);
		if (!$excel_parse_data) {
			$this->_json_message($excel->errcode, $excel->errmsg);
		}
		@unlink($file);


		// 写入临时储存
		rfwrite($this->__tmp_data_path, "<?php\r\n\$excel_data = ".var_export($excel_parse_data, true).";");


		list($field, $list) = $excel_parse_data;


		if (($c1 = count($field)) != ($c2 = count($this->__fields))) {
			$this->_json_message(1010, '导入的用户列表文件格式不正确('.$c1.'/'.$c2.')，请使用模板导入');
		}

		$output = $this->__create_data_list($field, $list);

		$this->_json_message(0, 'OK', $output);
	}

	/**
	 * 将数据整理为批量导入需要的格式
	 * @param array $field 字段定义
	 * @param array $list 数据列表
	 * @return array
	 */
	private function __create_data_list($field, $list) {

		// “忽略”列，键名定义
		$key_ignore = '_ignore';
		// “导入结果”列，键名定义
		$key_result = '_result';

		// 标题栏总宽度
		//$width_total = 0;
		$_fields = array();
		$_fields[] = array('key' => $key_ignore, 'name' => '忽略', 'width' => 12);
		$_fields = array_merge($_fields, $this->__fields);
		$_fields[] = array('key' => $key_result, 'name' => '导入结果', 'width' => 120);
		//foreach ($_fields as $_key => $_ini) {
		//    $width_total = $width_total + $_ini['width'];
		//}
		unset($_key, $_ini);

		// 取得标题栏列的名称和宽度比例
		$field_name = array();
		foreach ($_fields as $_key => $_ini) {
			$field_name[] = array(
					'key' => $_key,
					'name' => $_ini['name'],
					'width' => ''//round($_ini['width']/$width_total, 2) * 100
			);
		}
		unset($_key, $_ini, $width_total);

		$list2 = array();
		foreach ($list as $_key => $_val) {
			if(empty($_val[0]) && empty($_val[1]) && empty($_val[2])) {
				continue;
			}
			$list2[]= $_val;
		}

		// 重新整理导入的数据列表
		$data_list = array();
		foreach ($list2 as $_key => $_val) {
			foreach ($_val as $_k => $_v) {
				$data_list[$_key][$field[$_k]] = $_v !== null ? $_v : '';
			}
		}
		unset($_key, $_k, $_val, $_v);
		// 重新整理列表
// 		foreach ($list as $_key => &$_val) {
// 			foreach ($_val as $_k => &$_v) {

// 				if ($_v === null) {
// 					$_v = '';
// 				}
// 			}
// 			unset($_v, $_k);
// 		}
// 		unset($_key, $_val);


		$output = array(
				'total' => count($list2),
				'key_ignore' => $key_ignore,
				'key_result' => $key_result,
				'field' => $field,
				'field_name' => $field_name,
				'list' => $list2,
				'data_list' => $data_list
		);
		return $output;
	}


	/**
	 * 批量提交数据，用于处理编辑后的错误数据，类似execel导入的后半部的处理过程
	 * @return void
	 */
	private function __resubmit() {

		// 字段定义
		$field = array_keys($this->__fields);
		// 读取上传的数据
		$data = array();
		foreach ($field as $_k) {
			$data[$_k] = $this->request->post($_k);
		}

		unset($_k, $_v);
		if (empty($data)) {
			$this->_admincp_error_message(1001, '没有待导入的数据');
			return false;
		}

		// 请求忽略的数据
		$ignore = (array)$this->request->post('ignore');

		// 整理格式，以名称为标准
		$name_key = array_search('name', $field);
		if (!isset($data[$field[$name_key]])) {
			$this->_admincp_error_message(1002, '数据异常');
			return false;
		}

		// 整理数据
		$list = array();
		foreach ($data[$field[$name_key]] as $_id => $_val) {
			if (isset($ignore[$_id])) {
				continue;
			}
			foreach ($field as $_field_id => $_field) {
				if (isset($data[$_field][$_id])) {
					$list[$_id][$_field_id] = $data[$_field][$_id];
				}
			}
		}

		if (empty($list)) {
			$this->_admincp_error_message('1003', '没有待导入的新数据');
			return false;
		}

		// 输出批量导入需要的格式
		$output = $this->__create_data_list($field, $list);

		$this->_json_message(0, 'OK', $output);
		return true;
	}

	/**
	 * 订单导出
	 */
	private function putout(){
		$limit = 1000;
		$zip = new ZipArchive();
		$path = voa_h_func::get_sitedir().'excel/';
		$zipname= $path.'order'.date('YmdHis',time());
		list($list,$total,$page) = $this->read_data('voa_uda_frontend_travel_order',1,$limit);
		if	(ceil($total/$limit) == 1)	{
			$this->putout_excel($list);exit;
		}
		if (!file_exists($zipname))	{
			$zip->open($zipname.'.zip',ZipArchive::OVERWRITE);
			for	($i=1; $i<=ceil($total/$limit); $i++)	{
				if($i != 1)list($list,$total,$page) = $this->read_data('voa_uda_frontend_travel_order',$i,$limit);
				//生成excel文件
				$result = $this->create_excel($list,$i,$path);
				//将生成的excel文件写入zip文件
				if($result)$zip->addFile($result,$i.'.xls');
			}
			$zip->close();
			//输出至浏览器
			$this->put_header($zipname.'.zip');
			//清理
			$this->clear($path);
		}
	}

	/**
	 * 读取数据
	 * @param string $table
	 * @return array
	 */
	private function read_data($table,$page = 1,$limit = 1000){
		$total = 0;
		$list = array();
		$params = array();
		$uda = &uda::factory($table);
		if (!$uda->get_list($params, $page, $limit, $list, $total)) {
			$this->_error_message($uda->errmsg);
			return true;
		}
		//赋值订单对应产品
		foreach($list as $k => $v) {
			$tmp = array(
				'tmp_goods_names'=>'',
				'tmp_goodsnum'=>'',
				'tmp_style_name'=>'',
				'tmp_salename'=>'',
				'tmp_goods_num'=>'',
				'tmp_count'=>0
			);
			foreach($v['goods_list'] as $k1 => $v1) {
				//获取货号
				$goods_data = &uda::factory('voa_uda_frontend_goods_data');
				$tmp_goodsdata = array();
				if(@$goods_data->get_one($v1['goods_id'],$tmp_goodsdata)){
					$tmp['tmp_goodsnum'] .= "$tmp_goodsdata[proto_3]\n";
				}
				$tmp['tmp_goods_names'] .= "$v1[goods_name]\n";
				$tmp['tmp_style_name'] .= "$v1[style_name]\n";
				$tmp['tmp_salename'] .= "$v1[salename]\n";
				$tmp['tmp_goods_num'] .= "$v1[num]\n";
				$tmp['tmp_count'] = $tmp['tmp_count'] + $v1['num'];
			}
			$list[$k]['goods_names'] = $tmp['tmp_goods_names'];
			$list[$k]['goods_goodsnum'] = $tmp['tmp_goodsnum'];
			$list[$k]['style_name'] = $tmp['tmp_style_name'];
			$list[$k]['salename'] = $tmp['tmp_salename'];
			$list[$k]['num'] = $tmp['tmp_goods_num'];
			$list[$k]['nums'] = $tmp['tmp_count'];
 			unset($list[$k]['goods_list']);
		}
		unset($tmp);
		unset($tmp_goodsdata);
		return array($list,$total,$page);
	}

	/**
	 * 生成excel
	 * @param array $list
	 */
	private function create_excel($list,$i,$tmppath){
		if(!is_dir($tmppath)) mkdir($tmppath,'0777');
		$options = array();
		$attrs = array();
		list($title_string, $title_width, $row_data) = $this->_excel_data_order($list);
		excel::make_tmp_excel_download('订单列表', $tmppath.$i.'.xls', $title_string, $title_width, $row_data, $options, $attrs);
		return $tmppath.$i.'.xls';
	}

	/**
	 * 导出excel
	 * @param array $list
	 */
	private function putout_excel($list){
		$options = array();
		$attrs = array();
		list($title_string, $title_width, $row_data) = $this->_excel_data_order($list);
		excel::make_excel_download('订单列表', $title_string, $title_width, $row_data, $options, $attrs);
	}
}
