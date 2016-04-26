<?php
/**
 * voa_c_admincp_office_sign_list
 * 企业后台/微办公管理/考勤签到/签到记录列表
 * Create By Deepseath
 * $Author$
 * $Id$
 */
// 如果是导出操作则不自动加入header头
define('NO_AUTO_HEADER', isset($_GET['is_dump']));

class voa_c_admincp_office_sign_list extends voa_c_admincp_office_sign_base {
    // 获取人员班次信息
    protected $_get_member_batch = array();
    // 获取所有班次
    protected $_get_all_batch = array();
    // xls 横坐标
    protected $_letter = array();

    public function execute() {

        $searchDefault = array('m_username' => '', 'signtime_min' => '', // rgmdate(startup_env::get('timestamp'), 'Y-m-d'),
            'signtime_max' => '', // rgmdate(startup_env::get('timestamp') + 86400, 'Y-m-d'),
            'sr_type' => '', 'sr_sign' => '', 'cd_id' => '');
        $issearch = $this->request->get('issearch');
        $perpage = 15;

        // 请求的是导出操作
        if ($this->request->get('is_dump')) {
            // xls 横坐标
            for($i = 0; $i <= 500; $i ++) {
                if ($i < 26) {
                    $this->_letter[] = chr($i + 65);
                } else {
                    $ascii = floor($i / 26) - 1;
                    $this->_letter[] = chr($ascii + 65) . chr(($i % 26) + 65);
                }
            }

            // 导出长度限制
            $perpage = 10000;
            // 导出的数据准备
            list($total, $multi, $searchBy, $list, $conditions) = $this->_search_sign_record($this->_module_plugin_id, $issearch, $searchDefault, $perpage);

            // 导出
            $this->__dump_list($list, $searchBy, $conditions);

            return true;
        } else {
            list($total, $multi, $searchBy, $list, $conditions) = $this->_search_sign_record($this->_module_plugin_id, $issearch, $searchDefault, $perpage);
        }
        // 默认部门
        if (! empty($searchBy['cd_id'])) {
            $dep_cache = $deplist = voa_h_cache::get_instance()->get('department', 'oa');
            $cd_name = $dep_cache[$searchBy['cd_id'][0]]['cd_name'];
            // 根据部门id取部门名称
            $searchBy['dep_default'] = array(array('id' => $searchBy['cd_id'][0], 'cd_name' => $cd_name, 'isChecked' => (bool)true));
        } else {
            $searchBy['dep_default'] = array();
        }
        $searchBy['dep_default'] = rjson_encode(array_values($searchBy['dep_default']));

        // 获取该月第一天和最后一天日期
        list($begin_d, $end_d) = $this->get_m_day();

        // 默认上班搜索条件为全部, 有提交搜索就改变值
        $sr_type = 0;
        if ($this->request->get('sr_type')) {
            $sr_type = $this->request->get('sr_type');
        }
        $this->view->set('sr_type', $sr_type);

        $this->view->set('begin_d', $begin_d);
        $this->view->set('end_d', $end_d);
        $this->view->set('signStatus', $this->_sign_status);
        $this->view->set('signType', $this->_sign_type);
        $this->view->set('signStatusSet', $this->_sign_status_set);
        $this->view->set('signTypeSet', $this->_sign_type_set);
        $this->view->set('searchBy', $searchBy);
        $this->view->set('issearch', $issearch);
        $this->view->set('multi', $multi);
        $this->view->set('list', $list);
        $this->view->set('total', $total);
        $this->view->set('timestamp', startup_env::get('timestamp'));
        $this->view->set('editUrlBase', $this->cpurl($this->_module, $this->_operation, 'edit', $this->_module_plugin_id, array('sr_id' => '')));
        $this->view->set('searchActionUrl', $this->cpurl($this->_module, $this->_operation, $this->_subop, $this->_module_plugin_id));
        $this->view->set('detailUrlBase', $this->cpurl($this->_module, $this->_operation, 'detail', $this->_module_plugin_id, array('sr_id' => '')));

        $this->output('office/sign/list');
    }

    /**
     * 搜索签到记录
     *
     * @param $cp_pluginid
     * @param $issearch
     * @param array $searchDefaults
     * @param int $perpage
     * @return array
     */
    protected function _search_sign_record($cp_pluginid, $issearch, $searchDefaults = array(), $perpage = 10) {

        /**
         * 搜索条件
         */
        $searchBy = array();
        $conditions = array();
        /**
         * 如果为搜索
         */
        if ($issearch) {
            // 查询条件
            foreach ($searchDefaults as $_k => $_v) {
                if (isset($_GET[$_k]) && $this->request->get($_k) != $_v) {
                    if ($this->request->get($_k) != null) {
                        $searchBy[$_k] = $this->request->get($_k);
                    } else {
                        $searchBy[$_k] = $_v;
                    }
                }
            }
            $searchBy = array_merge($searchDefaults, $searchBy);
        } else {
            $searchBy = $searchDefaults;
        }
        // 组合搜索条件
        if (! empty($searchBy)) {

            $this->_add_condi($conditions, $searchBy);
        }
        //部门里没有人员
        if(!empty($searchBy['cd_id']) && empty($conditions['m_uid'])){
            $list = array();
            $total = 0;
            $multi = '';
        }else{
            $list = array();
            $serv_rec = &service::factory('voa_s_oa_sign_record');

            $total = $serv_rec->count_by_conds($conditions);

            $multi = '';
            if ($total > 0) {
                $pagerOptions = array('total_items' => $total, 'per_page' => $perpage, 'current_page' => $this->request->get('page'), 'show_total_items' => true);
                $multi = pager::make_links($pagerOptions);
                pager::resolve_options($pagerOptions);
                $page_option[0] = $pagerOptions['start'];
                $page_option[1] = $perpage;
                $orderby['sr_signtime'] = 'DESC';
                $tmp = $serv_rec->list_by_conds($conditions, $page_option, $orderby);
                foreach ($tmp as $_id => $_data) {
                    $list[$_id] = $this->_format_sign_record($_data);
                }
                unset($tmp);
            }

        }

        return array($total, $multi, array_merge($searchDefaults, $searchBy), $list, $conditions);
    }

    /**
     * 导出.xls文件
     *
     * @param array $list
     * @param $searchBy
     * @param $conditions
     * @return bool
     */
    private function __dump_list(array $list, $searchBy, $conditions) {

        // 从搜索结果里提取人员ID
        $this->__list_muid_by_result($list);
        // 去重
        $list = array_unique($list);

        // 获取人员班次方法
        $this->_get_member_batch = $this->get_member_batch($list);

        // 获取所有班次
        $batchinfo = &service::factory('voa_s_oa_sign_batch');
        $this->_get_all_batch = $batchinfo->list_all();

        // 实例化压缩类
        $zip = new ZipArchive();
        $path = voa_h_func::get_sitedir() . 'excel/';
        rmkdir($path);
        $zipname = $path . 'sign' . date('YmdHis', time());
        $zip->open($zipname . '.zip', ZipArchive::CREATE);
        // 按条件获取签到记录
        $serv_re = &service::factory('voa_s_oa_sign_record');
        $tmp = $serv_re->list_by_conds($conditions);
        // 格式数据
        foreach ($tmp as $_id => &$_data) {
            $li[$_id] = $this->_format_sign_record($_data);
        }
        // 汇总表
        $path_record = $this->get_record($li, $searchBy);
        // 异常表
        $path_unusual = $this->get_unusual($li, $searchBy);

        // 排班信息表
        $path_batch = $this->get_batch($this->_get_member_batch);

        $zip->addFile($path_unusual, 'unusual.xls');
        $zip->addFile($path_record, 'record.xls');
        $zip->addFile($path_batch, 'batch.xls');
        $zip->close();
        $this->__put_header($zipname . '.zip');
        // 清除缓存
        $this->__clear($path);

        return false;
    }

    /**
     * 下载输出至浏览器
     *
     * @param $zipname
     */
    private function __put_header($zipname) {

        if (! file_exists($zipname)) {
            exit("下载失败");
        }

        $file = fopen($zipname, "r");
        Header("Content-type: application/octet-stream");
        Header("Accept-Ranges: bytes");
        Header("Accept-Length: " . filesize($zipname));
        Header("Content-Disposition: attachment; filename=" . basename($zipname));
        echo fread($file, filesize($zipname));
        $buffer = 1024;
        while (! feof($file)) {
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
     * 考勤汇总表
     *
     * @param unknown $list
     * @return string
     */
    public function get_record($list, $searchBy) {

        $excel = new excel();
        // Excel表格式
        $letter = $this->_letter;
        // 表头数组
        $tableheader = array('考勤汇总表');
        // 填充表头信息
        for($i = 0; $i < count($tableheader); $i ++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
        }
        // 设置宽度,加粗
        $this->__set_width($excel);
        // 获取人员班次方法
        $mem = $this->_get_member_batch;
        $begin = strtotime($searchBy['signtime_min']);
        $end = strtotime($searchBy['signtime_max']);
        // 测试数据
        /*
         * $begin = strtotime ( '2015-08-21' );
         * $end = strtotime ( '2015-09-10' );
         */
        // 构造日期数组
        $datelist = $this->get_date($begin, $end);
        // 组合记录数据
        $haveday = $this->format_list($datelist, $list);
        // 查询所有班次详细信息
        $batchinfo = &service::factory('voa_s_oa_sign_batch');
        $batchlist = $this->_get_all_batch;
        // 部门信息
        $deplist = voa_h_cache::get_instance()->get('department', 'oa');
        // 人员信息
        $serv_member = &service::factory('voa_s_oa_member');
        $member_li = $serv_member->fetch_all();
        // 结果输出
        $result = array();
        $result = $this->get_result($batchinfo, $deplist, $member_li, $haveday, $batchlist, $datelist, $mem);

        // 输出表格
        $data = $this->__get_data($result, $searchBy);
        // 填充表格信息
        for($i = 2; $i <= count($data) + 1; $i ++) {
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $j ++;
            }
        }
        // 创建Excel输入对象
        $write = $this->__write($excel, 'record');
        // 设置存储路径
        $path = voa_h_func::get_sitedir() . 'excel/';
        if (! is_dir($path)) {
            mkdir($path);
        }
        $write->save($path . "record.xls");
        $filepath = $path . 'record.xls';

        return $filepath;
    }

    /**
     * 异常统计表
     *
     * @param unknown $list
     * @return string
     */
    public function get_unusual($list, $searchBy) {

        $excel = new excel();
        // Excel表格式,这里简略写了8列
        $letter = $this->_letter;
        // 表头数组
        $tableheader = array('异常统计表');
        // 填充表头信息
        for($i = 0; $i < count($tableheader); $i ++) {
            $excel->getActiveSheet()->setCellValue("$letter[$i]1", "$tableheader[$i]");
        }
        $i = 1;
        // 获取人员班次方法
        $mem = $this->_get_member_batch;
        $begin = strtotime($searchBy['signtime_min']);
        $end = strtotime($searchBy['signtime_max']);
        // 测试数据
        /*
         * $begin = strtotime ( '2015-08-21' );
         * $end = strtotime ( '2015-09-10' );
         */
        // 构造日期数组
        $datelist = $this->get_date($begin, $end);
        // 组合记录数据
        $haveday = $this->format_list($datelist, $list);
        // 查询所有班次详细信息
        $batchinfo = &service::factory('voa_s_oa_sign_batch');
        $batchlist = $this->_get_all_batch;
        // 部门信息
        $deplist = voa_h_cache::get_instance()->get('department', 'oa');
        // 人员信息
        $serv_member = &service::factory('voa_s_oa_member');
        $member_li = $serv_member->fetch_all();
        // 结果输出
        $result = array();
        $result = $this->get_result($batchinfo, $deplist, $member_li, $haveday, $batchlist, $datelist, $mem);

        // 找出异常数据
        $un_result = $this->__unusual_data($result);
        // 输出数据
        $data = $this->__un_data($un_result, $searchBy);

        // 填充表格信息
        for($i = 2; $i <= count($data) + 1; $i ++) {
            $j = 0;
            foreach ($data[$i - 2] as $key => $value) {
                $excel->getActiveSheet()->setCellValue("$letter[$j]$i", "$value");
                $j ++;
            }
        }
        // 合并单元格,样式
        $this->__un_merge($excel);
        // 创建Excel输入对象
        $write = $this->__write($excel, 'unusual');
        $path = voa_h_func::get_sitedir() . 'excel/';
        if (! is_dir($path)) {
            mkdir($path);
        }
        // 保存路径
        $write->save($path . "unusual.xls");
        $filepath = $path . 'unusual.xls';

        return $filepath;
    }

    /**
     * 排班信息表
     *
     * @param $member_batch_information 人员信息
     * @return string
     * @throws PHPExcel_Exception
     */
    public function get_batch($member_batch_information) {

        $excel = new excel();
        // xls 横坐标
        $letter = $this->_letter;
        // 填充 标题
        $excel->getActiveSheet()->setCellValue('A1', '排班信息表');
        // 居中，加粗
        $excel->getActiveSheet()->getStyle('A1:AH4')->applyFromArray(array('font' => array('bold' => true), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
        // 设置宽度
        $excel->setActiveSheetIndex(0);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);

        // 表格数组
        $data[0] = '统计日期 : ' . $this->request->get('signtime_min') . ' ~ ' . $this->request->get('signtime_max');
        // 填充统计日期
        $excel->getActiveSheet()->setCellValue("A2", $data[0]);
        // 填充 序号 姓名 部门 头
        $data[1] = array('序号', '姓名', '部门');
        $excel->getActiveSheet()->setCellValue("A3", $data[1][0]);
        $excel->getActiveSheet()->setCellValue("B3", $data[1][1]);
        $excel->getActiveSheet()->setCellValue("C3", $data[1][2]);

        // 开始时间 和 结束时间 的 时间戳
        $bday = strtotime($this->request->get('signtime_min'));
        $eday = strtotime($this->request->get('signtime_max'));

        $data[2] = array(); // 日期(号)
        $data[3] = array(); // 星期 (数字)
        $data[4] = array(); // 星期 (中文)

        // 得到 日期表
        while ($bday <= $eday) {
            $bday = date('Y-m-d', $bday);
            $short = substr($bday, 8, 2);
            array_push($data[2], $short);
            $week = date('w', strtotime($bday));
            array_push($data[3], $week);
            $str = $this->getWeek($week);
            array_push($data[4], $str);
            $bday = strtotime($bday);
            $bday = $bday + 86400; // 加一天
        }

        // 处理人员信息
        $this->__deal_batch_data($member_batch_information, $data, $member_data);

        // 填充 日期
        $day_start_in_xls = 3; // 关于日期 在 $letter数组 里的开始位置
        foreach ($data[2] as $val) {
            $excel->getActiveSheet()->setCellValue($letter[$day_start_in_xls] . "3", $val);
            $day_start_in_xls ++;
        }

        // 填充 星期
        $day_start_in_xls = 3;
        foreach ($data[4] as $val) {
            $excel->getActiveSheet()->setCellValue($letter[$day_start_in_xls] . "4", $val);
            $day_start_in_xls ++;
        }

        // 填充 个人值班信息
        $per_id_in_xls = 5; // 序号在 xls里的开始位置
        $work_days_start_in_xls = 3;
        foreach ($member_data as $k => $v) {
            $excel->getActiveSheet()->setCellValue('A' . $per_id_in_xls, $k); // 填充序号
            $excel->getActiveSheet()->setCellValue('B' . $per_id_in_xls, $v['m_username']); // 填充姓名
            $excel->getActiveSheet()->setCellValue('C' . $per_id_in_xls, $v['cd_name']); // 填充部门
            foreach ($v['_work_days'] as $_k => $_v) {
                $excel->getActiveSheet()->setCellValue($letter[$work_days_start_in_xls] . $per_id_in_xls, $_v); // 横向 填充工作日
                $work_days_start_in_xls ++;
            }
            $per_id_in_xls ++; // 纵向填充位置
            $work_days_start_in_xls = 3; // 还原 序号开始位置
        }

        // 合并
        $excel->getActiveSheet()->mergeCells('A1:M1');
        $excel->getActiveSheet()->mergeCells('A2:M2');
        $excel->getActiveSheet()->mergeCells('A3:A4');
        $excel->getActiveSheet()->mergeCells('B3:B4');
        $excel->getActiveSheet()->mergeCells('C3:C4');

        // 创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="batch.xls"');
        header("Content-Transfer-Encoding:binary");

        $path = voa_h_func::get_sitedir() . 'excel/';
        if (! is_dir($path)) {
            mkdir($path);
        }

        $write->save($path . "batch.xls");

        $filepath = $path . 'batch.xls';

        return $filepath;
    }

    /**
     * 从搜索结果里提取人员id
     *
     * @param $list
     * @return bool
     */
    private function __list_muid_by_result(&$list) {

        $result = array();
        foreach ($list as $k => $v) {
            $result[] = $v['m_uid'];
        }
        $list = $result;

        return true;
    }

    /**
     * 处理人员所在的部门 和 班次信息
     *
     * @param $member_batch_information
     * @param $member_data
     * @return bool
     */
    private function __deal_batch_data($member_batch_information, $data, &$member_data) {
        // 整理每个人的数据
        foreach ($member_batch_information as $val) {
            // 因为会出现一人多部门的情况,所以用遍历
            foreach ($val['cd_ids'] as $k => $v) {
                $member_data[] = array('m_username' => $val['m_username'], '_cd_id' => $val['cd_ids'][$k], 'batch' => $val['batch'][$val['cd_ids'][$k]]); // 获取cd_ids关联的班次ID

            }
        }

        // 获取所有班次
        $serv_batch = &service::factory('voa_s_oa_sign_batch');
        $all_batch = $this->_get_all_batch;
        // 匹配班次 获取工作日
        foreach ($member_data as $KEY => &$VAL) {
            foreach ($all_batch as $_KEY => $_VAL) {
                if ($_VAL['sbid'] == $VAL['batch']) {
                    $VAL['work_days'] = unserialize($_VAL['work_days']);
                }
            }
        }

        // 获取所有部门 列表
        $all_department = voa_h_cache::get_instance()->get('department', 'oa');
        // 匹配部门 获得部门名
        foreach ($member_data as $key => &$val) {
            foreach ($all_department as $_key => $_val) {
                if ($val['_cd_id'] == $_val['cd_id']) {
                    $val['cd_name'] = $_val['cd_name'];
                }
            }
        }

        // 整理每个人的 工作日
        foreach ($member_data as $k => &$v) {
            foreach ($data[3] as $_k => $_v) {
                if (isset($v['work_days']) && ! empty($v['work_days']) && in_array($_v, $v['work_days'])) {
                    $v['_work_days'][] = 1;
                } else {
                    $v['_work_days'][] = '';
                }
            }
        }

        return true;
    }

    /**
     * 状态判断
     *
     * @param int conds
     * @param array searchBy
     */
    protected function _add_condi(&$conds, $searchBy) {

        if (! empty($searchBy['signtime_min'])) { // 发起时间
            $conds['sr_created >= ?'] = rstrtotime($searchBy['signtime_min']);
        }
        if (! empty($searchBy['signtime_max'])) { // 发起时间
            $conds['sr_created <= ?'] = rstrtotime($searchBy['signtime_max']) + 86400;
        }
        if (! empty($searchBy['m_username'])) { // 发起人
            $conds["m_username like ?"] = "%" . $searchBy['m_username'] . "%";
        }
        if (! empty($searchBy['sr_type'])) { // 类型
            $conds['sr_type = ?'] = $searchBy['sr_type'];
        }
        if (! empty($searchBy['sr_sign'])) { // 状态
            $conds['sr_sign = ?'] = $searchBy['sr_sign'];
        }
        if (! empty($searchBy['cd_id'])) { // 部门搜索条件
            //把部门条件换成m_uid
            $serv_member_dep = &service::factory('voa_s_oa_member_department');
            $conds_dep['cd_id'] = $searchBy['cd_id'][0];
            $mem_list = $serv_member_dep->fetch_all_by_conditions($conds_dep);
            $conds['m_uid'] = array();
            if(!empty($mem_list)){
                foreach($mem_list as $val){
                    $muids[] = $val['m_uid'];
                }
                $conds['m_uid'] = $muids;
            }
        }
    }

    public function record_need($formatlist, &$data) {

        $data = array();
        foreach ($formatlist as $n_rec) {
            $date = date('Y-m-d', $n_rec['sr_created']);
            $data[$n_rec['m_uid']][$date][] = $n_rec;
        }
        /*
         * echo '<pre>';
         * print_r($data);
         * echo '</pre>';
         * exit;
         */
    }

    function getWeek($week) {

        switch ($week) {
            case 1 :
                return "星期一";
                break;
            case 2 :
                return "星期二";
                break;
            case 3 :
                return "星期三";
                break;
            case 4 :
                return "星期四";
                break;
            case 5 :
                return "星期五";
                break;
            case 6 :
                return "星期六";
                break;
            case 0 :
                return "星期日";
                break;
        }
    }

    public function get_ba_users() {

        $list = $this->get_batch_user();
        $userlist = array();
        foreach ($list as $batch) {
            foreach ($batch['department_list'] as $users) {
                array_push($userlist, $users);
            }
        }
        $ulist = array();
        foreach ($userlist as $v) {
            $count = count($v);
            for($i = 0; $i < $count; $i ++) {
                $ulist[] = $v[$i];
            }
        }
        $ulist = array_unique($ulist);

        return $ulist;
    }

    /**
     * 日期数组
     *
     * @param $begin
     * @param $end
     * @return array
     */
    public function get_date($begin, $end) {
        // 构造日期数组
        $datelist = array();
        while ($begin <= $end) {
            $begin = date('Y-m-d', $begin);
            $datelist[$begin] = $begin;
            $begin = strtotime($begin);
            $begin = strtotime("+1 day", $begin);
        }

        return $datelist;
    }

    /**
     * 按日期格式签到记录
     *
     * @param unknown $list
     * @return Ambigous <multitype:, unknown>
     */
    public function format_list($datelist, $list) {

        $haveday = array();
        // 循环日期数据，按日期格式
        foreach ($datelist as $_evday) {
            foreach ($list as $_lis) {
                if (rgmdate($_lis['sr_signtime'], 'Y-m-d') == $_evday) {
                    $haveday[rgmdate($_lis['sr_signtime'], 'Y-m-d')][$_lis['m_uid']][] = $_lis;
                }
            }
        }

        return $haveday;
    }

    /**
     * 只打上班卡情况
     *
     * @param $_batch
     * @param $_meminfo
     * @param $haveday
     * @param $_d
     * @param $work_begin
     * @param $late_range
     * @param $result
     * @param $late_time
     * @param $_sign_on
     */
    private function __sbset1($_batch, $_meminfo, $haveday, $_d, $work_begin, $late_range, &$result, &$late_time, &$_sign_on) {

        $addunusual = '';
        foreach ($haveday[$_d][$_meminfo['m_uid']] as $likey => $_li) {
            $able = 1;
            if ($_li['sr_type'] == 1) { // 上班
                if ($_li['sr_sign'] == 2) {
                    $result[$_d][$_meminfo['m_uid']][$_batch]['late'] = 2;
                    $late_time = floor(($this->_to_seconds(rgmdate($_li['sr_signtime'], 'H:i')) - $work_begin - $late_range) / 60);
                    // 错误班次数据处理
                    if ($late_time < 0) {
                        $late_time = 0;
                    }
                    $_sign_on = rgmdate($_li['sr_signtime'], 'H:i');
                } elseif ($_li['sr_sign'] == 1) {
                    $result[$_d][$_meminfo['m_uid']][$_batch]['late'] = 1;
                    $late_time = 0;
                    $_sign_on = rgmdate($_li['sr_signtime'], 'H:i');
                }
            }
            // 判断地理位置是否异常
            if ($_li['sr_addunusual'] == 1) {
                $addunusual = '(' . $_li['sr_address'] . ')';
            }
            $result[$_d][$_meminfo['m_uid']][$_batch]['date'] = rgmdate($_li['sr_signtime'], 'Y-m-d H:i');
            $result[$_d][$_meminfo['m_uid']][$_batch]['addunusual'] = $addunusual;
            $result[$_d][$_meminfo['m_uid']][$_batch]['address'] = $_li['sr_address'];
        }
        if (! isset($able)) {
            $result[$_d][$_meminfo['m_uid']][$_batch]['absent'] = 1;
        }
        unset($addunusual);
    }

    /**
     * 只打下班卡情况
     *
     * @param $_batch
     * @param $_meminfo
     * @param $haveday
     * @param $_d
     * @param $work_end
     * @param $leave_early_range
     * @param $result
     * @param $early_time
     * @param $_sign_off
     * @param $overtime
     */
    private function __sbset2($_batch, $_meminfo, $haveday, $_d, $work_end, $leave_early_range, &$result, &$early_time, &$_sign_off, &$overtime) {

        $addunusual = '';
        foreach ($haveday[$_d][$_meminfo['m_uid']] as $likey => $_li) {
            if ($_li['sr_sign'] == 4) {
                $result[$_d][$_meminfo['m_uid']][$_batch]['early'] = 4;
                $early_time = ceil(($work_end - $leave_early_range - $this->_to_seconds(rgmdate($_li['sr_signtime'], 'H:i'))) / 60);
                // 错误班次数据处理
                if ($early_time < 0) {
                    $early_time = 0;
                }
                $_sign_off = rgmdate($_li['sr_signtime'], 'H:i');
                $overtime = 0;
            } elseif ($_li['sr_sign'] == 1) {
                $result[$_d][$_meminfo['m_uid']][$_batch]['early'] = 1;
                $early_time = 0;
                $overtime = $_li['sr_overtime'];
                $_sign_off = rgmdate($_li['sr_signtime'], 'H:i');
            }
            // 判断地理位置是否异常
            if ($_li['sr_addunusual'] == 1) {
                $addunusual = '(' . $_li['sr_address'] . ')';
            }
            $result[$_d][$_meminfo['m_uid']][$_batch]['date'] = rgmdate($_li['sr_signtime'], 'Y-m-d H:i');
            $result[$_d][$_meminfo['m_uid']][$_batch]['addunusual'] = $addunusual;
            $result[$_d][$_meminfo['m_uid']][$_batch]['address'] = $_li['sr_address'];
        }
        unset($addunusual);
    }

    /**
     * 上下班卡都打情况
     *
     * @param $_batch
     * @param $_meminfo
     * @param $haveday
     * @param $_d
     * @param $work_begin
     * @param $work_end
     * @param $late_range
     * @param $leave_early_range
     * @param $result
     * @param $late_time
     * @param $early_time
     * @param $_sign_on
     * @param $_sign_off
     * @param $overtime
     * @param $work_ont
     * @param $work_offt
     */
    private function __sbset3($_batch, $_meminfo, $haveday, $_d, $work_begin, $work_end, $late_range, $leave_early_range, &$result, &$late_time, &$early_time, &$_sign_on, &$_sign_off, &$overtime, &$work_ont, &$work_offt) {

        $addunusual = '';
        $ad1 = '';
        $ad2 = '';
        foreach ($haveday[$_d][$_meminfo['m_uid']] as $_li) {

            if ($_li['sr_type'] == 1) { // 上班
                if ($_li['sr_sign'] == 2) { // 迟到状态
                    $result[$_d][$_meminfo['m_uid']][$_batch]['late'] = 2;
                    $late_time = ceil(($this->_to_seconds(rgmdate($_li['sr_signtime'], 'H:i')) - $work_begin - $late_range) / 60);

                    // 错误班次数据格式
                    if ($late_time < 0) {
                        $late_time = 0;
                    }
                    $_sign_on = rgmdate($_li['sr_signtime'], 'H:i');
                } elseif ($_li['sr_sign'] == 1) { // 正常状态
                    $result[$_d][$_meminfo['m_uid']][$_batch]['late'] = 1;
                    $late_time = 0;
                    $_sign_on = rgmdate($_li['sr_signtime'], 'H:i');
                }
                $work_ont = $_li['sr_signtime'];
                // 判断地理位置是否异常
                if ($_li['sr_addunusual'] == 1) {
                    $addunusual = '(' . $_li['sr_address'] . ')';
                }
                $ad1 = $_li['sr_address'];
            } elseif ($_li['sr_type'] == 2) { // 下班
                if ($_li['sr_sign'] == 4) { // 早退状态
                    $result[$_d][$_meminfo['m_uid']][$_batch]['early'] = 4;
                    // 判断下班实际时间是否大于上班时间
                    if ($this->_to_seconds(rgmdate($_li['sr_signtime'], 'H:i')) < $work_begin) {
                        $early_time = ceil(($work_end - $leave_early_range - $work_begin) / 60);
                    } else {
                        $early_time = ceil(($work_end - $leave_early_range - $this->_to_seconds(rgmdate($_li['sr_signtime'], 'H:i'))) / 60);
                    }
                    // 错误班次数据
                    if ($early_time < 0) {
                        $early_time = 0;
                    }
                    $_sign_off = rgmdate($_li['sr_signtime'], 'H:i');

                    $overtime = 0;
                } elseif ($_li['sr_sign'] == 1) { // 正常状态
                    $result[$_d][$_meminfo['m_uid']][$_batch]['early'] = 1;
                    $early_time = 0;
                    $overtime = $_li['sr_overtime'];
                    $_sign_off = rgmdate($_li['sr_signtime'], 'H:i');
                }
                $work_offt = $_li['sr_signtime'];
                // 判断地理位置是否异常
                if ($_li['sr_addunusual'] == 1) {
                    $addunusual .= '(' . $_li['sr_address'] . ')';
                }

                $ad2 = $_li['sr_address'];
            }
            $result[$_d][$_meminfo['m_uid']][$_batch]['date'] = rgmdate($_li['sr_signtime'], 'Y-m-d H:i');
            $result[$_d][$_meminfo['m_uid']][$_batch]['addunusual'] = $addunusual;
            if (! empty($ad1) && ! empty($ad2)) {
                $result[$_d][$_meminfo['m_uid']][$_batch]['address'] = $ad1 . ',' . $ad2;
            } elseif (! empty($ad1) && empty($ad2)) {
                $result[$_d][$_meminfo['m_uid']][$_batch]['address'] = $ad1;
            } elseif (empty($ad1) && ! empty($ad2)) {
                $result[$_d][$_meminfo['m_uid']][$_batch]['address'] = $ad2;
            }
        }
        // 当天未签退不记录在内
        if (count($haveday[$_d][$_meminfo['m_uid']]) < 2) {
            if (rgmdate(startup_env::get('timestamp'), 'Y-m-d') == $_d) {
                $_sign_off = '暂无';
            }
        }
        unset($addunusual, $ad1, $ad2);
    }

    /**
     * 格式班次信息
     *
     * @param $batchlist
     * @param $_batch
     * @param $_d
     * @return array
     */
    public function info_format($batchlist, $_batch, $_d) {
        // 班次信息
        $work_begin = $this->formattime($batchlist[$_batch]['work_begin']);
        $work_end1 = $this->formattime($batchlist[$_batch]['work_end']);
        $work_begin = $this->_to_seconds($work_begin);
        $work_end = $this->_to_seconds($work_end1);
        // 设置表数据
        $late_range = (int)$batchlist[$_batch]['come_late_range'] * 60;
        $leave_early_range = (int)$batchlist[$_batch]['leave_early_range'] * 60;
        $week = rgmdate(strtotime($_d), 'w');

        return array($work_begin, $work_end, $work_end1, $late_range, $leave_early_range, $week);
    }

    /**
     * 汇总表获取输出数组
     *
     * @param $batchinfo
     * @param $deplist
     * @param $member_li
     * @param $haveday
     * @param $batchlist
     * @param $datelist
     * @param $mem
     * @return array
     */
    public function get_result($batchinfo, $deplist, $member_li, $haveday, $batchlist, $datelist, $mem) {
        // 构造输出数组
        $result = array();
        foreach ($datelist as $_d) {
            foreach ($mem as $_meminfo) {
                // 每个人每个班次生成一条记录
                foreach ($_meminfo['batch'] as $_cdid => $_batch) {
                    // 格式班次信息
                    list($work_begin, $work_end, $work_end1, $late_range, $leave_early_range, $week) = $this->info_format($batchlist, $_batch, $_d);
                    // 大于今天日期

                    $time_true = strtotime(rgmdate(startup_env::get('timestamp'), 'Y-m-d H:i:s'));
                    // var_dump($time_true);die;

                    // 2015-10-10 03:23:50
                    if (strtotime($_d) < $time_true) {
                        if (in_array($week, unserialize($batchlist[$_batch]['work_days']))) { // 在工作日内
                            // 当天有记录
                            if (! isset($haveday[$_d][$_meminfo['m_uid']])) {
                                $result[$_d][$_meminfo['m_uid']][$_batch]['absent'] = 1; // 旷工
                            } else {
                                $result[$_d][$_meminfo['m_uid']][$_batch]['absent'] = 0;
                                // 判断打卡设置
                                switch ($batchlist[$_batch]['sb_set']) {
                                    case 1 : // 只打上班卡
                                        $early_time = '-';
                                        $late_time = '-';
                                        $_sign_on = '-';
                                        $this->__sbset1($_batch, $_meminfo, $haveday, $_d, $work_begin, $late_range, $result, $late_time, $_sign_on);
                                        break;
                                    case 2 : // 只打下班卡
                                        $early_time = '-';
                                        $overtime = 0;
                                        $_sign_off = '-';
                                        $this->__sbset2($_batch, $_meminfo, $haveday, $_d, $work_end, $leave_early_range, $result, $early_time, $_sign_off, $overtime);
                                        break;
                                    case 3 : // 打两种卡
                                        $_sign_on = '-';
                                        $_sign_off = '-';
                                        $early_time = '-';
                                        $this->__sbset3($_batch, $_meminfo, $haveday, $_d, $work_begin, $work_end, $late_range, $leave_early_range, $result, $late_time, $early_time, $_sign_on, $_sign_off, $overtime, $work_ont, $work_offt);
                                        break;
                                }

                                // 三种情况最终数据，签到
                                if ($_sign_on != '-') {
                                    $result[$_d][$_meminfo['m_uid']][$_batch]['_sign_on'] = $_sign_on;
                                } else {
                                    $result[$_d][$_meminfo['m_uid']][$_batch]['_sign_on'] = '-';
                                }
                                // 三种情况最终数据，签退
                                if ($_sign_off != '-') {
                                    $result[$_d][$_meminfo['m_uid']][$_batch]['_sign_off'] = $_sign_off;
                                } else {
                                    $result[$_d][$_meminfo['m_uid']][$_batch]['_sign_off'] = '-';
                                    $result[$_d][$_meminfo['m_uid']][$_batch]['no_signoff'] = '未签退';
                                }

                                $end_time = substr($work_end1, 0, 2);
                                // 一下几种情况没有工作时长，未签退，只打一次卡
                                if ($_sign_off != '-' && isset($work_ont) && isset($work_offt) && $batchlist[$_batch]['sb_set'] == 3 && isset($early_time)) {
                                    if ($_sign_off == '暂无') {
                                        $result[$_d][$_meminfo['m_uid']][$_batch]['work_long'] = '-';
                                    } else {
                                        $result[$_d][$_meminfo['m_uid']][$_batch]['work_long'] = ceil(($work_offt - $work_ont) / 60);
                                    }
                                } else {
                                    $result[$_d][$_meminfo['m_uid']][$_batch]['work_long'] = '-';
                                }
                                $result[$_d][$_meminfo['m_uid']][$_batch]['overtime'] = isset($overtime) ? ceil($overtime / 60) : 0;

                                $result[$_d][$_meminfo['m_uid']][$_batch]['late_time'] = isset($late_time) ? $late_time : '-';
                                $result[$_d][$_meminfo['m_uid']][$_batch]['early_time'] = isset($early_time) ? $early_time : '-';
                                // 清除本次循环缓存
                                unset($_sign_on, $_sign_off, $_li);
                                unset($late_time, $early_time, $overtime);
                            }

                            // 班次名称
                            $result[$_d][$_meminfo['m_uid']][$_batch]['bname'] = $batchlist[$_batch]['name'];
                            // 日期
                            $result[$_d][$_meminfo['m_uid']][$_batch]['date'] = rgmdate(rstrtotime($_d), 'Y/m/d');
                            $result[$_d][$_meminfo['m_uid']][$_batch]['uid'] = $_meminfo['m_uid'];
                            // 匹配人员部门信息
                            $result[$_d][$_meminfo['m_uid']][$_batch]['cd_name'] = $cdname = $deplist[$_cdid]['cd_name'];
                            $result[$_d][$_meminfo['m_uid']][$_batch]['username'] = $member_li[$_meminfo['m_uid']]['m_username'];
                        }
                    }
                    unset($work_begin, $work_end, $work_end1);
                }
            }
        }

        return $result;
    }

    /**
     * 汇总表 构造xls表输出数组
     *
     * @param $result
     * @return array
     */
    private function __get_data($result, $searchBy) {
        // 标题
        $data[0] = array('统计日期:' . $searchBy['signtime_min'] . '~' . $searchBy['signtime_max']);
        // 子段
        $data[1] = array('序号', '姓名', '部门', '日期', '上班', '下班', '迟到(分钟)', '早退(分钟)', '加班时长(分钟)', '出勤时长(分钟)', '考勤地址');
        $i = 1;
        foreach ($result as $_result) {
            // 人员数组
            foreach ($_result as $user) {
                foreach ($user as $_info) {

                    if ($_info['absent'] == 0) {
                        $data[] = array($i, $_info['username'], $_info['cd_name'], $_info['date'], $_info['_sign_on'], $_info['_sign_off'], $_info['late_time'], $_info['early_time'], $_info['overtime'], $_info['work_long'], $_info['address']);
                    } else {
                        $data[] = array($i, $_info['username'], $_info['cd_name'], $_info['date'], '旷工', '-', '-', '-', '-', '-', '_');
                    }

                    $i ++;
                }
            }
        }

        return $data;
    }

    /**
     * 汇总表设置宽度
     */
    private function __set_width($excel) {
        // 居中，加粗
        $excel->getActiveSheet()->getStyle('A1:K3')->applyFromArray(array('font' => array('bold' => true), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));
        // 合并
        $excel->getActiveSheet()->mergeCells('A1:J1');
        $excel->getActiveSheet()->mergeCells('A2:J2');
        // 设置宽度
        $excel->setActiveSheetIndex(0);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);

        return true;
    }

    /**
     * 异常数据
     *
     * @param $result
     * @return array
     */
    private function __unusual_data($result) {

        $un_result = array();
        $un = array('0', '-');
        // 遍历所有天数据
        foreach ($result as $_d => $_date) {
            foreach ($_date as $uid => $_user) {
                foreach ($_user as $_bid => $_info) {
                    // 旷工情况
                    if (($_info['absent'] == 1) || // 迟到情况
                        ($_info['absent'] == 0 && ! in_array($_info['early_time'], $un)) || // 迟到情况
                        ($_info['absent'] == 0 && ! in_array($_info['late_time'], $un)) || // 地理位置异常情况
                        ($_info['absent'] == 0 && $_info['addunusual'] != '') || // 未签退情况
                        ($_info['absent'] == 0 && isset($_info['no_signoff']))) {
                        $un_result[$_d][$uid][$_bid] = $_info;
                    }
                }
            }
        }

        return $un_result;
    }

    private function __un_data($result, $searchBy) {
        // 标题
        $data[0] = array('统计日期:' . $searchBy['signtime_min'] . '~' . $searchBy['signtime_max']);
        $data[1] = array('序号', '姓名', '部门', '日期', '班次', '上班', '下班', '迟到(分钟)', '早退(分钟)', '备注');

        $i = 1;
        foreach ($result as $_result) {
            // 人员数组
            foreach ($_result as $user) {
                foreach ($user as $_info) {
                    $addun = '';
                    if (isset($_info['addunusual'])) {
                        $addun = $_info['addunusual'];
                    }
                    // 地理位置异常
                    if ($addun != '' && isset($_info['no_signoff'])) {
                        $str_beizhu = $addun . ',';
                    } else {
                        $str_beizhu = $addun;
                    }
                    $no_off = '';
                    if (isset($_info['no_signoff'])) {
                        $no_off = $_info['no_signoff'];
                    }

                    // 未旷工
                    if ($_info['absent'] == 0) {
                        $data[] = array($i, $_info['username'], $_info['cd_name'], $_info['date'], $_info['bname'], $_info['_sign_on'], $_info['_sign_off'], $_info['late_time'], $_info['early_time'], $str_beizhu . $no_off);
                    } else {
                        $data[] = array($i, $_info['username'], $_info['cd_name'], $_info['date'], '旷工', '-', '-', '-', '-', '-');
                    }

                    $i ++;
                }
            }
        }

        return $data;
    }

    /**
     * 异常表样式
     *
     * @param unknown $excel
     * @return boolean
     */
    private function __un_merge($excel) {
        // 设置宽度
        $excel->setActiveSheetIndex(0);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
        $excel->getActiveSheet()->getColumnDimension('K')->setWidth(15);
        // 合并
        $excel->getActiveSheet()->mergeCells('A1:M1');
        $excel->getActiveSheet()->mergeCells('A2:M2');

        // 居中，加粗
        $excel->getActiveSheet()->getStyle('A1:M3')->applyFromArray(array('font' => array('bold' => true), 'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER)));

        return true;
    }

    /**
     * 创建Excel公用输入对象方法
     *
     * @param unknown $excel
     * @param unknown $name
     * @return PHPExcel_Writer_Excel5
     */
    private function __write($excel, $name) {
        // 创建Excel输入对象
        $write = new PHPExcel_Writer_Excel5($excel);
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
        header("Content-Type:application/force-download");
        header("Content-Type:application/vnd.ms-execl");
        header("Content-Type:application/octet-stream");
        header("Content-Type:application/download");
        header('Content-Disposition:attachment;filename="' . $name . '.xls"');
        header("Content-Transfer-Encoding:binary");

        return $write;
    }

}
