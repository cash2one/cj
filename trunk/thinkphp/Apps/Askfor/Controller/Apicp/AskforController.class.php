<?php
/**
 * Created by PhpStorm.
 * User: lixue
 * Date: 15/11/5
 * Time: 下午2:04
 */
namespace Askfor\Controller\Apicp;

use Askfor\Model\AskforProcModel;
use Askfor\Service\AskforProcService;
use Askfor\Model\AskforModel;
use Common\Common\Pager;
use Common\Common\Request;

class AskforController extends AbstractController {

	const ASKING = 1; // 审批中
	const ASKPASS = 2; // 审核通过
	const TURNASK = 3; // 转审批
	const ASKFAIL = 4; // 审批不通过
	const COPYASK = 5; // 抄送
	const DRAFT = 5; // 草稿
	const PRESSASK = 6; // 催办
	const CENCEL = 7; // 已撤销

	//审批状态文字描述
	protected $_askfor_status_descriptions = array(
		AskforModel::ASKING => '审批中',
		AskforModel::ASKPASS => '已批准',
		AskforModel::TURNASK => '通过并转审批',
		AskforModel::ASKFAIL => '审核未通过',
		AskforModel::DRAFT => '草稿',
		AskforModel::CENCEL => '已撤销',
		AskforModel::PRESSASK => '已催办'
		//voa_d_oa_askfor::STATUS_REMOVE => '已删除',
	);

	//审批状态样式定义
	protected $_askfor_status_class_tag = array(
		AskforModel::ASKING => 'primary',//审批中
		AskforModel::ASKPASS => 'success',//已批准
		AskforModel::TURNASK => 'info',//通过并转审批
		AskforModel::ASKFAIL => 'danger',//审核未通过
		AskforModel::DRAFT => 'default',//草稿
		//已删除
	);
	//审批流程状态样式定义
	protected $_proc_condition = array(
		AskforProcModel::ASKING => '#69CEA7',//审批中
		AskforProcModel::ASKPASS => '#46AC46',//已批准
		AskforProcModel::TURNASK => '#39B3D7',//通过并转审批
		AskforProcModel::ASKFAIL => '#E14430',//审核未通过
		AskforProcModel::CENCEL => 'warning',//已删除
	);

	/**
	 * 后台列表接口
	 */
	public function List_get() {

		$params = I('get.');
		$serv_Askfor = D('Askfor/Askfor', 'Service');

		$page = $params['page'];
		$limit = $params['limit'];
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

		//判断有无部门搜索
		if (!empty($params['id_cd_id'])) {
			$serv_m_d = D('Common/MemberDepartment', 'Service');
			$conds_m_d['cd_id'] = $params['id_cd_id'];
			$m_list = $serv_m_d->list_by_conds($conds_m_d);
			$params['ulist'] = array();
			//该部门下所有的用户
			if (!empty($m_list)) {
				$u_list = array();
				foreach ($m_list as $user) {
					$u_list[] = $user['m_uid'];
				}
				$params['ulist'] = $u_list;
			}
		}
		//根据条件查询
		$list = $serv_Askfor->cp_list_by_conds($params, $page_option);

		$total = $serv_Askfor->cp_count_by_conds($params);

		//分页
		$multi = null;
		if ($total > 0) {
			$pagerOptions = array(
				'total_items' => $total,
				'per_page' => $params['limit'],
				'current_page' => $params['page'],
				'show_total_items' => true,
			);
			$multi = Pager::make_links($pagerOptions);
			//pager::resolve_options($pagerOptions);

		}

		if (!empty($list)) {
			$list = $this->_askfor_format($list);
		}
		//初始化数据
		//模板名称
		$template_list = array_values($this->askfor_templist());

		//部门名称
		$department_list = array_values($this->department_list());

		//返回值
		$this->_result = array(
			'list' => $list,
			'page' => $page,
			'template' => $template_list,
			'department' => $department_list,
			'status' => $this->_askfor_status_descriptions,
			'multi' => $multi,

		);
	}

	/**
	 * 格式化审批流详情
	 * @param array $askfor
	 * @return array
	 */
	protected function _askfor_format($askfor = array()) {
		$department_list = $this->department_list();

		//格式每个人部门
		$users = array();
		foreach ($askfor as $val) {
			$users[] = $val['m_uid'];
		}
		//获取这些人的信息
		$serv_member_department = D('MemberDepartment', 'Service');
		$conds_me['m_uid'] = $users;
		$mem_d = $serv_member_department->list_by_conds($conds_me);
		foreach ($askfor as &$_ask) {
			$_ask['cd_name'] = '';
			if (!empty($mem_d)) {
				foreach ($mem_d as $_mem_d) {
					$uid_list[] = $_mem_d['m_uid'];
				}
				//判断用户是否存在
				if (!in_array($_ask['m_uid'], $uid_list)) {
					$_ask['cd_name'] = '';
					$_ask['m_username'] = '<span class="text-danger">'.$_ask['m_username'].'(已删除)</span>';
					continue;
				}
				//格式部门信息
				foreach ($mem_d as $_mem) {
					//对应人员
					if ($_mem['m_uid'] == $_ask['m_uid']) {
						$_ask['cd_id'] = array();

						$_ask['cd_id'][] = $department_list[$_mem['cd_id']]['cd_name'];
						$_ask['cd_name'] = implode(',', $_ask['cd_id']);
					}
				}
			}
		}

		//格式其他数据
		foreach ($askfor as &$_format) {
			$_format['_created'] = rgmdate($_format['af_created'], 'Y-m-d H:i');
			$_format['_status'] = isset($this->_askfor_status_descriptions[$_format['af_condition']]) ? $this->_askfor_status_descriptions[$_format['af_condition']] : '';

			$_format['_tag'] = isset($this->_askfor_status_class_tag[$_format['af_condition']]) ? $this->_askfor_status_class_tag[$_format['af_condition']] : 'warning';
		}

		return $askfor;
	}

	/**
	 * 删除接口
	 */
	public function Delete_post() {

		$params = I('post.');
		$af_id = $params['af_id'];
		if (empty($params['af_id'])) {
			E('_ERR_MISS_PARAMETER_AFID');
		}

		$conds['af_id'] = $params['af_id'];
		//删除审批记录
		$serv_askfor = D('Askfor/Askfor', 'Service');
		$serv_askfor->delete_by_conds($conds);
		//删除关联附件
		$serv_att = D('Askfor/AskforAttachment', 'Service');
		$serv_att->delete_by_conds($conds);
		//删除关联进程
		$serv_proc = D('Askfor/AskforProc', 'Service');
		$serv_proc->delete_by_conds($conds);

		return true;
	}

	/**
	 * 详情接口
	 */
	public function View_get() {

		$params = I('get.');
		$af_id = (int)$params['af_id'];

		//当前浏览的审批id
		$serv_askfor = D('Askfor/Askfor', 'Service');
		$askfor = $serv_askfor->askfor_get($af_id);

		$askfor = $this->_view_format($askfor);

		//该审批所有进程
		$serv_proc = D('Askfor/AskforProc', 'Service');
		$proclist = $serv_proc->cp_list_by_conds($af_id);

		//只取审批人的记录
		$sp_list = array();
		$cond_sp = array(
			AskforProcModel::ASKING,
			AskforProcModel::ASKPASS,
			AskforProcModel::TURNASK,
			AskforProcModel::ASKFAIL,
		);
		foreach ($proclist as $_sp) {
			if (in_array($_sp['afp_condition'], $cond_sp)) {
				$sp_list[] = $_sp;
			}
		}
		//格式数据
		$sp_list = $this->_proc_format($sp_list);

		//从进度表获取所有进度数据并格式化
		$serv_procrec = D('Askfor/AskforProcRecord', 'Service');
		$conds_procrec['af_id'] = $af_id;
		$form_proclist = $serv_procrec->list_by_conds($conds_procrec);
		$form_proclist = $this->_procrecord_format($form_proclist);

		//如果是固定流程
		if ($askfor['aft_id'] != 0) {
			$max_leav = 1;
			//计算出当前最大审批级数
			$leav_list = array();
			if (!empty($sp_list)) {
				foreach ($sp_list as $val) {

					$leav_list[$val['afp_level']] = $val['afp_level'];
				}
				$max_leav = max($leav_list);
			}
			//格式每个用户状态
			foreach ($sp_list as &$va) {
				$va['condition'] = $this->_askfor_status_descriptions[$va['afp_condition']];
			}
			$current_le = 0;
			//计算当前到达级数
			foreach ($sp_list as $current) {
				if ($current['is_active'] == 1) {
					$current_le = $current['afp_level'];
				}
			}
			//全部同意情况
			if($current_le == 0){
				$current_le = $max_leav;
			}
			//进行状态显示过滤
			foreach ($sp_list as &$va_cond) {
				if ($va_cond['afp_level'] > $current_le) {
					unset($va_cond['afp_condition']);
					unset($va_cond['_condition']);
				}
			}
		}

		//获取抄送人
		$conds_cs['afp_condition'] = 5;
		$conds_cs['af_id'] = $af_id;
		$cs_list = array();
		$cs_list = $serv_proc->list_by_conds($conds_cs);

		$cs_uids = array();
		if (!empty($cs_list)) {
			foreach ($cs_list as $v_cs) {
				$cs_uids[$v_cs['m_uid']]['m_uid'] = $v_cs['m_uid'];
				$cs_uids[$v_cs['m_uid']]['m_username'] = $v_cs['m_username'];
			}
		}

		// 获取附件
		$serv_att = D('Askfor/AskforAttachment', 'Service');
		// 获取设置
		$cache = &\Common\Common\Cache::instance();
		$sets = $cache->get('Common.setting');
		$url = $sets['domain'];
		$conds_att['af_id'] = $af_id;
		$att_list = array();
		$att_list = $serv_att->list_by_conds($conds_att);
		//如果有附件
		if (!empty($att_list)) {
			foreach ($att_list as &$_att) {
				$_att['imgurl'] = cfg('PROTOCAL') . $url . '/attachment/read/' . $_att['at_id']; // 附件文件url
			}
		}

		//获取自定义字段
		$custom_data = array();
		if($askfor['aft_id'] != 0){
			$serv_field = D('Askfor/AskforCustomdata', 'Service');
			$conds_field['af_id'] = $af_id;
			$custom_data = $serv_field->list_by_conds($conds_field);
		}

		//进程数量
		$proc_count = count($form_proclist);
		$leav_list = array_values($leav_list);

		$this->_result = array(
			'sp_list' => $sp_list,
			'form_proclist' => $form_proclist,
			'askfor' => $askfor,
			'att_list' => $att_list,
			'proc_count' => $proc_count,
			'cs_list' => $cs_list,
			'leav_list' => $leav_list,
			'custom_data' => $custom_data,

		);
	}

	/**
	 * 格式化审批流详情
	 * @param array $askfor 详情信息
	 * @return array
	 */
	protected function _view_format($askfor = array()) {
		$askfor['_created'] = rgmdate($askfor['af_created'], 'Y-m-d H:i');
		$askfor['_status'] = isset($this->_askfor_status_descriptions[$askfor['af_condition']]) ? $this->_askfor_status_descriptions[$askfor['af_condition']] : '';

		$askfor['_tag'] = isset($this->_askfor_status_class_tag[$askfor['af_condition']]) ? $this->_askfor_status_class_tag[$askfor['af_condition']] : 'warning';

		return $askfor;
	}

	/**
	 * 格式化流程信息
	 * @param array $askfor
	 * @return mixed
	 */
	protected function _proc_format($askfor = array()) {

		foreach ($askfor as &$val) {

			$val['_created'] = rgmdate($val['afp_created'], 'Y-m-d H:i');
			$val['_condition'] = isset($this->_askfor_status_descriptions[$val['afp_condition']]) ? $this->_askfor_status_descriptions[$val['afp_condition']] : '未知';
			$val['_tag'] = isset($this->_askfor_status_class_tag[$val['afp_condition']]) ? $this->_askfor_status_class_tag[$val['afp_condition']] : 'warning';
			$val['_color'] = isset($this->_proc_condition[$val['afp_condition']]) ? $this->_proc_condition[$val['afp_condition']] : '#F29F29';

		}

		return $askfor;
	}
	/**
	 * 格式化进度流程信息
	 * @param array $askfor 要格式的数组
	 * @return mixed
	 */
	protected function _procrecord_format($askfor = array()) {

		foreach ($askfor as &$val) {

			$val['_created'] = rgmdate($val['rafp_created'], 'Y-m-d H:i');
			$val['_condition'] = isset($this->_askfor_status_descriptions[$val['rafp_condition']]) ? $this->_askfor_status_descriptions[$val['rafp_condition']] : '未知';
			$val['_tag'] = isset($this->_askfor_status_class_tag[$val['rafp_condition']]) ? $this->_askfor_status_class_tag[$val['rafp_condition']] : 'warning';
			$val['_color'] = isset($this->_proc_condition[$val['rafp_condition']]) ? $this->_proc_condition[$val['rafp_condition']] : '#F29F29';

		}

		return $askfor;
	}
	/** 导出接口 */
	public function Export_post() {

		$params = I('post.');
		// 参数不为空
		if (empty($params)) {
			E('_ERR_CP_PARAMS_CAN_NOT_EMPTY');

			return false;
		}

		$serv_Askfor = D('Askfor/Askfor', 'Service');

		// 判断有无部门搜索
		if (!empty($params['id_cd_id'])) {
			$serv_m_d = D('Common/MemberDepartment', 'Service');
			$conds_m_d['cd_id'] = $params['id_cd_id'];
			$m_list = $serv_m_d->list_by_conds($conds_m_d);
			//该部门下所有的用户
			if (!empty($m_list)) {
				$u_list = array();
				foreach ($m_list as $user) {
					$u_list[] = $user['m_uid'];
				}
				$params['ulist'] = $u_list;
			}else{
				$params['ulist'] = array(-1);
			}
		}

		// 获取总数
		$total = $serv_Askfor->cp_count_by_conds($params);

		// 初始化 压缩
		$zip = new \ZipArchive();
		// 路径和文件名
		$path = get_sitedir() . 'excel/';
		$zipname = $path . 'askfor' . date('YmdHis', time());

		if (!file_exists($zipname)) {
			$zip->open($zipname . '.zip', \ZipArchive::CREATE);
			// 分页参数
			if (!empty($total)) {
				$limit = 1000;
				$page = ceil($total/$limit);
				for ($i = 1; $i <= $page; $i++) {

					// 分页参数
					//list($start, $limit, $i) = page_limit($i, $limit);
					$page_option = array(($i-1) * $limit, $limit);
					// 根据条件查询
					$list = $serv_Askfor->cp_list_by_conds($params, $page_option);
					if(!empty($list)){
						$aft_ids = array_unique(array_column($list, 'aft_id'));
						$af_ids = array_unique(array_column($list, 'af_id'));

						// 查询自定义字段数据
						$serv_custom_data = D('Askfor/AskforCustomdata', 'Service');
						$custom_data = $serv_custom_data->list_by_conds(array('af_id' => $af_ids));
						// 查询 审批进度表
						$serv_proc = D('Askfor/AskforProc', 'Service');
						$proc_data = $serv_proc->list_by_conds(array('af_id' => $af_ids));
						foreach ($list as &$val) {
							// 合并 自定义字段
							foreach ($custom_data as $_val) {
								if ($val['af_id'] == $_val['af_id']) {
									$val['custom'][] = array(
										'name' => $_val['name'],
										'value' => $_val['value'],
									);
								}
							}
							// 合并 审批进度数据
							foreach ($proc_data as $_val) {
								if ($val['af_id'] == $_val['af_id']) {
									// 如果是审批人
									if (in_array($_val['afp_condition'], array(self::ASKING, self::ASKPASS, self::TURNASK, self::ASKFAIL))) {
										$val['approvers'][] = $_val;
									}
									// 如果是抄送人
									if ($_val['afp_condition'] == self::COPYASK) {
										$val['copy'][] = $_val;
									}
								}
							}
						}
					}

					// 生成csv文件
					$result = $this->__create_csv($list, $i, $path);
					if ($result) {
						$zip->addFile($result, $i . '.csv');
					}
				}
			} else {
				$result = $this->__create_csv(array(), 0, $path);
				if ($result) {
					$zip->addFile($result, 0 . '.csv');
				}
			}

			// 下载 并 清除文件
			$zip->close();
			$this->__put_header($zipname . '.zip');
			$this->__clear($path);

		}

		return true;
	}

	/**
	 * 生成csv文件
	 */
	private function __create_csv($list, $i, $path) {

		// 生成文件
		if (!is_dir($path)) {
			rmkdir($path);
		}
		$data = array();

		$filename = $i . '.csv';
		$data[0] = array(
			'申请人',
			'申请时间',
			'审批状态',
			'审批标题(固定流程名称)',
			'自定义字段',
			'抄送人',
			'审批内容',
			'审批人',
		);

		if (!empty($list)) {
			foreach ($list as $val) {
				switch ($val['af_condition']) {
					case (self::ASKING) :
						$af_condition = '审批申请中';
						break;
					case (self::ASKPASS) :
						$af_condition = '审批通过';
						break;
					case (self::TURNASK) :
						$af_condition = '转审批';
						break;
					case (self::ASKFAIL) :
						$af_condition = '审批不通过';
						break;
					case (self::DRAFT) :
						$af_condition = '草稿';
						break;
					case (self::PRESSASK) :
						$af_condition = '已催办';
						break;
					case (self::CENCEL) :
						$af_condition = '撤销';
						break;
					default :
						$af_condition = '未知';
						break;
				}

				// 整合自定义字段数据
				$customdata = '';
				$custom_array = array();
				if (isset($val['custom']) && !empty($val['custom'])) {
					foreach ($val['custom'] as $_value) {
						$custom_array[] = $_value['name'] . ':' . $_value['value'];
					}
					$customdata = implode(" || ", $custom_array);
				}

				// 抄送人数据整合
				$copy = '';
				if (isset($val['copy']) && !empty($val['copy'])) {
					foreach ($val['copy'] as $v) {
						$copy .= $v['m_username'] . '  ';
					}
				}

				// 审批人数据整合
				$approvers = '';
				if (isset($val['approvers']) && !empty($val['approvers'])) {
					// 自由流程 / 固定流程
					if ($val['aft_id'] == 0) {
						foreach ($val['approvers'] as $v) {
							// 匹配审批状态
							switch ($v['afp_condition']) {
								case (self::ASKING) :
									$afp_condition = '审批申请中';
									break;
								case (self::ASKPASS) :
									$afp_condition = '审批通过';
									break;
								case (self::TURNASK) :
									$afp_condition = '转审批';
									break;
								case (self::ASKFAIL) :
									$afp_condition = '审批不通过';
									break;
								default :
									$afp_condition = '未知';
									break;
							}
							// 审批人数据
							$approvers .= $v['m_username'] . $afp_condition;
						}
					} else {
						$by_level = array();
						// 把审批人 按 级数 分类
						foreach ($val['approvers'] as $v) {
							$by_level[$v['afp_level']][] = $v;
						}
						// 审批人数据整合
						foreach ($by_level as $k => $v) {
							$approvers .= '第' . $k . '级审批人 : ';
							foreach ($v as $_val) {
								// 匹配审批状态
								switch ($_val['afp_condition']) {
									case (self::ASKING) :
										$afp_condition = '审批申请中';
										break;
									case (self::ASKPASS) :
										$afp_condition = '审批通过';
										break;
									case (self::TURNASK) :
										$afp_condition = '转审批';
										break;
									case (self::ASKFAIL) :
										$afp_condition = '审批不通过';
										break;
									default :
										$afp_condition = '未知';
										break;
								}
								$approvers .= $_val['m_username'] . $afp_condition . '  ';
							}
						}
					}
				}

				$temp = array(
					'm_username' => $val['m_username'],
					'af_created' => !empty($val['af_created']) ? rgmdate($val['af_created'], 'Y-m-d H:i') : '',
					'af_condition' => $af_condition,
					'af_subject' => !empty($val['af_subject']) ? str_replace(PHP_EOL, '', $val['af_subject']) : '', // 去掉换行
					'customdata' => str_replace(PHP_EOL, '', $customdata),
					'copy' => $copy,
					'af_message' => !empty($val['af_message']) ? str_replace(PHP_EOL, '', $val['af_message']) : '',
					'approvers' => $approvers
				);

				$data[] = $temp;
			}
		}

		$csv_data = $this->_array2csv($data);
		$fp = fopen($path . $filename, 'w');
		fwrite($fp, $csv_data); // 写入数据
		fclose($fp); // 关闭文件句柄

		return $path . $filename;
	}

	/**
	 * 下载输出至浏览器
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
	}

	/**
	 * 将一组整齐的数组转换为csv字符串
	 * @param array $list 待转换的数组列表数据
	 * @param string $newline_symbol 每行之间的分隔符号，默认为：“\r\n”
	 * @param string $field_comma 字段之间的分隔符号，默认为：“,”
	 * @param string $field_quote_symbol 字段的引用符号，默认为：“"”
	 * @param string $out_charset 输出的数据字符集编码，默认为：gbk
	 * @return string
	 */
	protected function _array2csv(array $list, $newline_symbol = "\r\n", $field_comma = ",", $field_quote_symbol = '"', $out_charset = 'gbk') {

		// 初始化输出
		$data = '';
		// 初始化换行符号
		$_row_comma = '';

		// 遍历所有行数据
		foreach ($list as $_arr_row) {

			// 初始化行数据
			$_row = '';
			// 初始化每个字段的分隔符号
			$_comma = '';

			// 遍历所有字段
			foreach ($_arr_row as $_str) {
				// 字段数据分隔符
				$_row .= $_comma;
				if (strpos($_str, $field_comma) === false) {
					// 字段数据不包含字段分隔符，直接使用
					$_row .= $_str;
				} else {
					// 字段数据包含字段分隔符，则使用字段引用符号引用并转义数据内的引用符号
					$_row .= $field_quote_symbol.addcslashes($_str, $field_quote_symbol).$field_quote_symbol;
				}
				// 定义字段分隔符号
				$_comma = $field_comma;
			}

			// 行数据，以行分隔符号连接
			$data .= $_row_comma.$_row;

			// 定义换行符号
			$_row_comma = $newline_symbol;
		}

		// 输出数据
		return $this->_riconv($data, 'UTF-8', $out_charset);
	}

	/**
	 * 转换编码
	 * @param mixed $m
	 * @param string $from
	 * @param string $to
	 * @return mixed
	 */
	protected  function _riconv($m, $from = 'UTF-8', $to = 'GBK'){
		if ( strpos($to, '//') === false ) {
			$to	=	$to.'//IGNORE';
		}
		switch ( gettype($m) ) {
			case 'integer':
			case 'boolean':
			case 'float':
			case 'double':
			case 'NULL':
				return $m;
			case 'string':
				return @iconv($from, $to, $m);
			case 'object':
				$vars = array_keys(get_object_vars($m));
				foreach($vars AS $key) {
					$m->$key = $this->_riconv($m->$key, $from ,$to);
				}
				return $m;
			case 'array':
				foreach($m AS $k => $v) {
					$k2	=	$this->_riconv($k, $from, $to);
					if ( $k != $k2 ) {
						unset($m[$k]);
					}
					$m[$k2] = $this->_riconv($v, $from, $to);
				}
				return $m;
			default:
				return '';
		}
	}



}
