<?php
/**
 * phpexcel易用封装
 * Create By Deepseath
 * $Author$
 * $Id$
 */
/** 引入 PHPExcel 库 */
require dirname(__FILE__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'phpexcel'.DIRECTORY_SEPARATOR.'PHPExcel.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'phpexcel'.DIRECTORY_SEPARATOR.'PHPExcel'.DIRECTORY_SEPARATOR.'Writer'.DIRECTORY_SEPARATOR.'Excel5.php';

class excel extends PHPExcel {

	/** 错误代码 */
	public $errcode = 0;
	/** 错误信息 */
	public $errmsg = '';

	/**
	 * 标题栏文字颜色
	 * @var string default:FFFFFFFF
	 */
	private $_title_text_color = '';

	/**
	 * 标题栏背景颜色
	 * @var string default:FF808080
	 */
	private $_title_background_color = '';

	/**
	 * 是否已经设置过标题行
	 * @var unknown
	 */
	private $_row_title_set = array();

	/**
	 * 读取的文件名
	 * @var string
	 */
	private $_file = null;

	/**
	 * 导出文件备份
	 * @var unknown
	 */
	private $_xls_bak_dir = 'backup';

	private $_read_sheet_index = null;

	/** 默认属性值 */

	/** 属性：作者 */
	private $_attr_creator = '';
	/** 属性：最后一次保存者 */
	private $_attr_last_modified = '';
	/** 属性：标题 */
	private $_attr_title = '';
	/** 属性：主题 */
	private $_attr_subject = '';
	/** 属性：备注 */
	private $_attr_description = '';
	/** 属性：关键字 */
	private $_attr_keywords = '';
	/** 属性：类别 */
	private $_attr_category = '';

	/**
	 * 生成一个可下载的Excel文件
	 * @param string $filename 下载的文件名
	 * @param array $title_string 标题栏文字数组
	 * @param array $title_width 标题栏宽度数组
	 * @param array $row_data 每行数据数组
	 */
	public static function make_excel_download($filename, $title_string, $title_width, $row_data, $options = array(), $attrs = array()){
		$phpexcel = new self($options, $attrs);
		$phpexcel->setActiveSheetIndex(0)->setTitle($filename); //PHPExcel的方法
		$phpexcel->setRowTitle($title_string);
		$phpexcel->setRowWidth($title_width);
		$phpexcel->setRows($row_data);
		$phpexcel->downloadFile($filename.'.xls');
	}

	/**
	 * 读取XSL文件，并返回数组
	 * @param string $filename xsl文件的本地绝对物理路径
	 * @param number $set_read_index 读取xsl的标签页，默认为0
	 * @param unknown $options 配置
	 * @return multitype:multitype:Ambigous <unknown, string>
	 */
	public static function read_from_xsl($filename, $set_read_index = 0, $options = array(), $attrs = array()){
		$phpexcel = new self($options, $attrs);
		$phpexcel->setFile($filename);
		$phpexcel->set_read_index($set_read_index);
		return $phpexcel->read_xls();
	}

	/**
	 * 生成 excel 文件
	 * @see excel::make_excel_download()
	 */
	public function mk_excel_file($filename, $title_string, $title_width, $row_data, $options = array(), $attrs = array()) {
		self::make_excel_download($filename, $title_string, $title_width, $row_data, $options, $attrs);
	}

	/**
	 * 生成一个Excel文件
	 * @param string $filename 下载的文件名
	 * @param array $title_string 标题栏文字数组
	 * @param array $title_width 标题栏宽度数组
	 * @param array $row_data 每行数据数组
	 */
	public static function make_tmp_excel_download($filename,$file_excel,$title_string, $title_width, $row_data, $options = array(), $attrs = array()){
		$phpexcel = new self($options, $attrs);
		$phpexcel->setActiveSheetIndex(0)->setTitle($filename); //PHPExcel的方法
		$phpexcel->setRowTitle($title_string);
		$phpexcel->setRowWidth($title_width);
		$phpexcel->setRows($row_data);
		$phpexcel->setFile($file_excel);
		$phpexcel->saveFile();
	}

	/**
	 * 以字段形式输出格式化的数据列表
	 * @param string $filename xsl文件的本地绝对物理路径
	 * @param number $set_read_index 读取xsl的标签页，默认为0
	 * @param array $field_options 字段定义
	 * array('字段名' => array('name'=>'中文名', 'width'=>宽度[一个字符可按6来计算],), ... ...)
	 * @param number $title_row_num 标题栏行号（起始行=0）
	 * @param number $data_start_row_num 数据开始的行号（起始行=0）
	 * @param arrayunknown $options 配置
	 * @param array $attrs
	 * @return array(字段映射关系数组, 读取的数据列表)
	 */
	public function parse_xsl($filename, $set_read_index = 0, $field_options, $title_row_num = 0, $data_start_row_num = 0, $options = array(), $attrs = array()) {

		if (!is_readable($filename)) {
			$this->__set_errcode(900, '无法读取上传的 Excel 文件');
			return false;
		}

		$data = @file_get_contents($filename, FALSE, NULL, 0, 8);
		if ($data != pack('CCCCCCCC', 0xd0, 0xcf, 0x11, 0xe0, 0xa1, 0xb1, 0x1a, 0xe1)) {
			$this->__set_errcode(900, '上传的文件不是标准的云工作模板格式，请使用下载的模板');
			return false;
		}

		// 读取数据
		$list = self::read_from_xsl($filename, $set_read_index, $options, $attrs);

		// 没有读取到数据
		if (empty($list)) {
			$this->__set_errcode(901, '没有读取到有效的数据');
			return false;
		}

		// 字段中文名与表字段名对应关系
		$name2field = array();
		foreach ($field_options as $_k => $_arr) {
			$name2field[rstrtolower($_arr['name'])] = $_k;
		}
		unset($_k, $_arr);

		// 自标题栏行读取标记与字段名之间的对应关系
		$col2field = array();
		if (!isset($list[$title_row_num])) {
			$this->__set_errcode(902, '标题栏行号不存在');
			return false;
		}

		foreach ($list[$title_row_num] as $_col_num => $_col_name) {
			$_col_name = rstrtolower($_col_name);
			if (!isset($name2field[$_col_name])) {
				continue;
			}
			if (strpos($name2field[$_col_name], '#') === false) {
				$col2field[$_col_num] = $name2field[$_col_name];
			}
		}

		// 读取数据行
		$list = array_slice($list, $data_start_row_num, count($list), true);

		//过滤空的数据行
		foreach ($list as $k => $row) {
			$row = array_filter($row);
			if(empty($row)) {
				unset($list[$k]);
			}
		}

		return array($col2field, $list);
	}

	/**
	 * 设置错误信息
	 * @param number $errcode
	 * @param string $errmsg
	 */
	private function __set_errcode($errcode = 0, $errmsg = '') {
		$this->errcode = $errcode;
		$this->errmsg = $errmsg;
	}

	public function __construct($options = array(), $attrs = array()) {
		/** 构造父类 */
		parent::__construct();
		//$this->_options = $options;
		//$this->_attrs = $attrs;
		$this->_set_options($options);
		$this->_set_attributes($attrs);
	}

	/**
	 * 设置读取xls的标签页
	 * @param int $i
	 */
	private function set_read_index($i){
		$this->_read_sheet_index = $i;
	}

	/**
	 * 设置Excel的属性值
	 * @param array $attrs
	 * @return void
	 */
	private function _set_attributes($attrs = array()){
		$allowed_attrs = array(
				'creator',//作者
				'last_modified',//最后一次保存者
				'title',//标题
				'subject',//主题
				'description',//备注
				'keywords',//关键字
				'category'//类别
		);
		foreach ($allowed_attrs AS $key) {
			if (!isset($attrs[$key]) || !is_scalar($attrs[$key])) {
				$attrs[$key] = $this->{'_attr_'.$key};
			}
		}
		$this->getProperties()->setCreator($attrs['creator'])
		->setLastModifiedBy($attrs['last_modified'])
		->setTitle($attrs['title'])
		->setSubject($attrs['subject'])
		->setDescription($attrs['description'])
		->setKeywords($attrs['keywords'])
		->setCategory($attrs['category']);
	}

	/**
	 * 设置一些环境变量
	 * @param array $options
	 * @return void
	 */
	private function _set_options($options = array()){
		$allowed_options = array(
				'title_text_color',
				'title_background_color'
		);
		foreach ($options AS $key => $value) {
			if (in_array($key, $allowed_options) && ($value !== null)) {
				$this->{'_'.$key} = $value;
			}
		}

		/** 未设置标题栏颜色，则使用 PHPExcel 黑色 */
		if ($this->_title_text_color == '') {
			$this->_title_text_color = PHPExcel_Style_Color::COLOR_BLACK;
		}
		/** 未设置标题栏背景色，则使用 PHPExcel 深黄色 */
		if ($this->_title_background_color == '') {
			$this->_title_background_color = PHPExcel_Style_Color::COLOR_DARKYELLOW;
		}
	}

	/**
	 * 读取xls内容
	 * @param int $start_row 开始行数
	 * @param int $max_row 最大行数
	 */
	private function read_xls($start_row = 1, $max_row = 10000){
		if (!$this->_file){
			die("not set_file()");
		}
		$objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2007 for 2007 format
		$objPHPExcel=	$objReader->load($this->_file);

		if (!is_null($this->_read_sheet_index)) {
			$objWorksheet = $objPHPExcel->getSheet($this->_read_sheet_index);
		} else {
			$objWorksheet = $objPHPExcel->getActiveSheet();
		}

		/** 取得总行数 */
		$highestRow = $objWorksheet->getHighestRow();
		$highestColumn = $objWorksheet->getHighestColumn();

		/** 总列数 */
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

		/** 避免超过设置的最大行数 */
		if ($highestRow > $max_row) {
			$highestRow = $max_row;
		}

		$arr_Return = array();
		for ($row = $start_row; $row <= $highestRow; $row++) {

			/** 注意highestColumnIndex的列数索引从0开始 */
			$arr_info = array();
			for ($col = 0; $col < $highestColumnIndex; $col++) {
				$cell = $objWorksheet->getCellByColumnAndRow($col, $row)->getValue(); //getValue()  getCalculatedValue()

				/** 富文本转换字符串 */
				if ($cell instanceof PHPExcel_RichText) {
					$cell = $cell->__toString();
				}

				/** 公式 */
				if (substr($cell, 0, 1) == '='){
					$cell = $objWorksheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
				}
				$arr_info[$col] = $this->_excelDataToString($cell);
			}
			$arr_Return[] = $arr_info;
		}
		return $arr_Return;
	}

	/**
	 * 设置标题
	 * @param array $arrWidth = array('A'=>'ID' ,'B'=>'中文', 'D'=>'英文') | array('ID' ,'中文', '英文')
	 */
	private function setRowTitle($arrTitle){
		$index = $this->getActiveSheetIndex();
		$this->_row_title_set[$index] = true;
		if ($this->_array_type($arrTitle) == 'assoc') {
			foreach ($arrTitle as $Column=>$value){
				$this->getActiveSheet()->setCellValue($Column.'1', $value);
				$this->getActiveSheet()->getStyle($Column.'1')->getFont()->setBold(true);
				$this->getActiveSheet()->getStyle($Column.'1')->getFont()->getColor()->setARGB($this->_title_text_color);
				$this->getActiveSheet()->getStyle($Column.'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$this->getActiveSheet()->getStyle($Column.'1')->getFill()->getStartColor()->setARGB($this->_title_background_color);
				$this->getActiveSheet()->getStyle($Column)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			}
		} else {
			$start = 'A';
			for ($i=0; $i<count($arrTitle); $i++){
				$Column = $start++;
				$this->getActiveSheet()->setCellValue($Column.'1', $arrTitle[$i]);
				$this->getActiveSheet()->getStyle($Column.'1')->getFont()->setBold(true);
				$this->getActiveSheet()->getStyle($Column.'1')->getFont()->getColor()->setARGB($this->_title_text_color);
				$this->getActiveSheet()->getStyle($Column.'1')->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				$this->getActiveSheet()->getStyle($Column.'1')->getFill()->getStartColor()->setARGB($this->_title_background_color);
				$this->getActiveSheet()->getStyle($Column)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			}
		}
	}

	/**
	 * 设置行内容
	 * @param array $xls_rows
	 * e.g. $xls_rows = array(
	 * 		   array('content1','content2','content3'),
	 * 		   array('A'=>'content1','B'=>'content2','C'=>'content3'),
	 * 		   ...
	 * 		)
	 */
	private function setRows($xls_rows) {
		$index = $this->getActiveSheetIndex();
		$n = $this->_row_title_set[$index] ? 2 : 1;
		foreach ($xls_rows as $row) {
			if($this->_array_type($row) == 'assoc') { //关联
				foreach ($row as $Column=>$value){
					$this->getActiveSheet()->setCellValueExplicit($Column.$n, $value, $this->_setDataType($value));
					$this->getActiveSheet()->getStyle($Column.$n)->getAlignment()->setWrapText(true)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				}
			} else{
				$start = 'A';
				for($i=0; $i<count($row); $i++){
					$Column = $start++;
					$this->getActiveSheet()->setCellValueExplicit($Column.$n, $row[$i], $this->_setDataType($row[$i]));
					$this->getActiveSheet()->getStyle($Column.$n)->getAlignment()->setWrapText(true)->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
				}
			}
			$n++;
			/*
			#横向|竖向 对齐方式 setHorizontal | setVertical (PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
			//也可生成EXCEL后手动设置也方便
			# HORIZONTAL_RIGHT | HORIZONTAL_LEFT | HORIZONTAL_CENTER  参考PHPExcel/Style/Alignment.php
			# VERTICAL_RIGHT | VERTICAL_LEFT | VERTICAL_CENTER  参考PHPExcel/Style/Alignment.php
			*/
		}
	}

	/**
	 * 设置标题宽度
	 * @param array $arrWidth = array('A'=>8 ,'B'=>60, 'C'=>60,'D'=>'auto','E'=>0) | array(8,60,60,0,0)
	 */
	private function setRowWidth($arrWidth = array()) {
		if($this->_array_type($arrWidth)=='assoc') {
			//关联
			foreach ($arrWidth as $Column=>$value){
				if($value=='auto' || $value==0){
					$this->getActiveSheet()->getColumnDimension($Column)->setAutoSize(true);
				}else{
					$this->getActiveSheet()->getColumnDimension($Column)->setWidth($value."pt");
				}
				//$this->getActiveSheet()->getStyle($Column)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				//$this->getActiveSheet()->getStyle($Column)->getFill()->getStartColor()->setARGB('FF808080');
			}
		}else{
			$start = 'A';
			for($i=0; $i<count($arrWidth); $i++){
				$Column = $start++;
				$value = $arrWidth[$i];
				if($value=='auto' || $value==0){
					$this->getActiveSheet()->getColumnDimension($Column)->setAutoSize(true);
				}else{
					$this->getActiveSheet()->getColumnDimension($Column)->setWidth($value."pt");
				}
				//$this->getActiveSheet()->getStyle($Column)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
				//$this->getActiveSheet()->getStyle($Column)->getFill()->getStartColor()->setARGB('FF808080');
			}
		}
	}

	/**
	 * 设置要保存的文件,测试文件是否可以被打开
	 * @param unknown $file_excel
	 * @throws Exception
	 */
	private function setFile($file_excel){
		$file_excel = riconv($file_excel, 'UTF-8', 'GBK');
		if (!($fp=fopen($file_excel,'a+'))) {
			throw new Exception("{$file_excel} can not fopen!!");
		}
		if ($fp) {
			fclose($fp);
		}
		$this->_file = $file_excel;
	}

	/**
	 * 保存文件
	 * 使用该方法前，必须使用 $this->setFile();方法设置文件名
	 */
	private function saveFile(){
		$file_excel = $this->_file;
		$objWriter = PHPExcel_IOFactory::createWriter($this, 'Excel5');
		$objWriter->save($file_excel);
	}

	/**
	 * 下载生成的Excel文件
	 * @param string $downloadFile 下载的文件名
	 */
	private function downloadFile($downloadFile){
		$filename = riconv($downloadFile, 'UTF-8', 'GBK');
		header("Pragma: public");
		header("Expires: 0");
		header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
		header("Content-Type:application/force-download");
		header("Content-Type:application/vnd.ms-execl");
		header("Content-Type:application/octet-stream");
		header("Content-Type:application/download");
		header('Content-Disposition:attachment;filename="'.$filename.'"');
		header("Content-Transfer-Encoding:binary");
		$objWriter = PHPExcel_IOFactory::createWriter($this, 'Excel5');
		$objWriter->save('php://output');
		exit;
	}

	/**
	 * 将单元格内的数据转为可读的字符串
	 * @param unknown $string
	 * @return unknown|string
	 */
	private function _excelDataToString($string){
		if (stripos($string, 'e') === false) {
			return $string;
		}
		if (!preg_match('/^[0-9\.e\-\+]+$/i', $string)) {
			return $string;
		}
		/** 科学计数法，还原成字符串 */
		$string = trim(preg_replace('/[=\'"]/', '', $string, 1),'"');
		$result = '';
		while ($string > 0) {
			$v = $string - floor($string / 10) * 10;
			$string = floor($string / 10);
			$result = $v.$result;
		}
		return $result;
	}

	/**
	 *
	 * @param array $arr
	 * @return string
	 */
	private function _array_type($arr){
		$c = count($arr);
		$in = array_intersect_key($arr, range(0, $c-1));
		if (count($in) == $c) {
			//索引数组
			return 'index';
		} elseif(empty($in)) {
			//关联数组
			return 'assoc';
		} else{
			//混合数组
			return 'mix';
		}
	}

	/**
	 * 根据数据类型自动判断单元格的数据格式
	 * @param unknown $str
	 * @return string
	 */
	private function _setDataType($str){
		return 'str';
		if (is_numeric($str)) {
			if (strpos($str,'.') !== false) {
				return 'b';
			} elseif (!isset($str{3})) {
				return 'n';
			} else {
				return 'str';
			}
		} else {
			return 'str';
		}
	}
}
