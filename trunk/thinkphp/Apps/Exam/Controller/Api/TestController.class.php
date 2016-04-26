<?php
/**
 * NewsController.class.php
 * $author$
 */

namespace Exam\Controller\Api;
use Com\Cookie;

class TestController extends AbstractController {

	// 模拟数据
	private function serv_paper($id){
		$db=array(
			1 => array(
				'id' => 1,
				'name' => 'php入职考试',
				'total_score' => 100,
				'pass_score' => 60,
				'ti_num' => 4,
				'begin_time' => 1451815200,
				'end_time' => 1456826000,
				'departments' => '技术部',
				'intro' => 'php培训,口碑良好的php培训机构,进来看看同学们的呐喊,就会知道我们是个怎么样的学校',
				'is_all' => 1,
				'type' => 1,
			),
			2 => array(
				'id' => 2,
				'name' => 'java入职考试',
				'total_score' => 100,
				'pass_score' => 60,
				'ti_num' => 4,
				'begin_time' => 1451815200,
				'end_time' => 1456826000,
				'departments' => '产品部',
				'intro' => '《说明》为标稳中有变稳中趋综 ，哈市教育行政网公布了《哈尔滨市2016年初中升学考试说明》(以下简称 《说明》',
				'is_all' => 1,
				'type' => 2,
			),
		);
		return $db[$id];
	}

	// 模拟数据
	private function serv_question(){
		$db=array(
			array(
				'id'=>1,
				'title'=>'＜img＞标记符中连接图片的参数是',
				'options'=>array('A. 需要安装客户端的软件',  'B. 不需要安装就可以使用的软件', 'C. 依托浏览器的网络系统', 'D. 依托outlook等软件的邮件系统?'),
				'score'=>10,
				'type'=>0,
				'result_status'=>0, // 结果状态
			),
			array(
				'id'=>2,
				'title'=>'《朝花夕拾》原名《_________》,是鲁迅的回忆性散文集',
				'options'=>"",
				'score'=>10,
				'type'=>1,
				'result_status'=>1,
			),
			array(
				'id'=>3,
				'title'=>'动能修正系数与断面流速分布有关。',
				'options'=>"",
				'score'=>10,
				'type'=>2,
				'result_status'=>1,
			),
			array(
				'id'=>3,
				'title'=>'可由海水生产的化工产品',
				'options'=>array('A.Cl2 ',  'B.N2', 'C.NaCl', 'D.CH4'),
				'score'=>10,
				'type'=>3,
				'result_status'=>2,
			),
		);
		return $db;
	}
	// 获取试题类型
	private function serv_type(){
		return array('选择题', '填空题', '判断题', '多选题');
	}

	// 获取考试结果
	private function serv_result($id){
		$db=array(
			1 => array(
				'score' => 80,// 得分
				'use_time' => 60,// 用时
				'wrong_num' => 3,// 错题数量
				'is_pass'=>true,
			),
			2 => array(
				'score' => 50,// 得分
				'use_time' => 40,// 用时
				'wrong_num' => 6,// 错题数量
				'is_pass'=>false,
			),
		);
		return $db[$id];
	}

	/**
	 * 试卷列表
	 */
	public function paper_list_get() {

		$is_finished = I('get.is_finished'); // 是否完成
		$page = I('get.page', 1, 'intval'); // 当前页 默认第一页

		// 模拟读取数据库
		$list = array(
			array(
				'id' => 1,
				'name' => 'php入职考试', // 标题
				'begin_time' => 1451341800, // 开始时间
				'is_pass'=> false, // 是否通过
				'score'=> 59, // 分数
			),
			array(
				'id' => 2,
				'name' => 'java入职考试',
				'begin_time' => 1461361800,
				'is_pass'=> true,
				'score'=> 80,
			)
		);
		
		// 返回操作
		$this->_result = array(
			'list' => $list,
			'total' => 3,	//	总页数
			'now_time' => NOW_TIME	// 现在时间戳
		);
		return true;
	}
	/**
	 * 试卷详情
	 */
	public function paper_detail_get() {
		$id = I('get.id'); // 试卷id

		// serv_paper 只是用来模拟数据表
		$paper=$this->serv_paper($id);

		//$authcode = authcode($paper['id'] . "\t" . $paper['type'], self::AUTH_CODE, 'ENCODE');
		// $this->base_encode 这个函数解决不能get auth的问题
		$paper['auth'] = $this->base_encode( authcode($paper['id'] . "\t" . $paper['type'], self::AUTH_CODE, 'ENCODE') );
		
		// 返回操作
		$this->_result = array(
			'paper' => $paper,
		);
		return true;
	}
	/**
	 * 试题详情
	 */
	public function paper_question_get() {
		$auth = I('get.auth');
		$id = I('get.id');// 试题的id
        if(empty($auth)) {
        	$this->_set_error('_ERROR_ID_LEGAL');
            return false;
        }
        $auth=$this->base_decode($auth);
        list($paperid, $type) = explode("\t", authcode($auth, self::AUTH_CODE));
        if(!$paperid) {
        	$this->_set_error('_ERROR_ID_LEGAL');
            return false;
        }

        // 模拟获取试卷
        $paper=$this->serv_paper($paperid);
        // 模拟获取试题
        $question_list=$this->serv_question();
        // 模拟开始考试时间
        $startime=NOW_TIME;
        
        // 返回操作
		$this->_result = array(
			'paper' => $paper,
			'question_list' => $question_list,
			'auth' => $auth,
			'startime' => $startime,
			'type'=>$this->serv_type(),
		);
		return true;
	}

	/**
	 * 试卷结果
	 */
	public function paper_result_get() {
		$id = I('get.id');// 结果的id
		// 返回操作
		$this->_result = array(
			'detail' => $this->serv_result($id),
			'question_list' => $this->serv_question(),
		);
		return true;
	}
	/**
	 * 试题解析
	 */
	public function question_resolve_get() {
		$id = I('get.id');
		$wrong=I('get.wrong');

		$question_list=$this->serv_question();
		if($wrong){
			foreach ($question_list as $k => $v) {
				$question_list[$k]['result_status']=2;
			}
		}

		// 返回操作
		$this->_result = array(
			'paper' => $this->serv_paper($id),
			'question_list' => $question_list,
			'type'=>$this->serv_type(),
			'wrong'=>$wrong,
		);
		return true;
	}

}