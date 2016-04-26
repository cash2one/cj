<?php
namespace Share\Model;

//素材
class MaterialModel extends AbstractModel {


	// 构造方法
	public function __construct() {

		parent::__construct();
	}

    function getMaterialList($start,$limit,$params = array()) {
        $model = M('Material');
        $where = $this->getWhere($params);
        $select = 'ma.material_id,ma.title,ma.status,last_modify_time,m.m_weixin,m.m_username';
        $sql = 'SELECT '.$select.' from oa_material AS ma JOIN oa_member as m ON ma.m_uid = m.m_uid '.$where.' ORDER BY ma.material_id DESC LIMIT '.$start.','.$limit;
        $result = $model->query($sql);
        // echo $model->getLastSql();die();

        return $result;
    }

    function getWhere($params) {
        $status = isset($_GET['status']) ? intval($_GET['status']) : 0;
        $m_username = isset($_GET['m_username']) ? htmlspecialchars($_GET['m_username']) : '';
        $title  = isset($_GET['title']) ? htmlspecialchars($_GET['title']) : '';

        $where = 'where';

        if(!empty($params)) {
            $where.=' m.m_uid = '.$params['m_uid'].' AND ';
        }

        if (!empty($status)) {

            $where.=' ma.status = '.$status.' AND ';

        }

        if (!empty($m_username)) {
            $where.=' m.m_username like "%'.$m_username.'%" AND ';
        }
        if ('' != $title) {
            $where.=' ma.title like "%'.$title.'%" AND ';
        }


        if('where' == $where) {
            $where = '';
        } else {
            $where = trim(trim($where),'AND');
        }
        // echo $where;die();
        return $where;
    }


    function getCount($params) {
        $model = M('Material');
        $where = $this->getWhere($params);
        $sql = 'SELECT count(*) as total from oa_material AS ma JOIN oa_member as m ON ma.m_uid = m.m_uid '.$where;
        $result = $model->query($sql);

        return $result[0]['total'];
    }

    //更新状态
    function updateStatus() {

        $status      = isset($_POST['status']) ? intval($_POST['status']) : 0;
        $material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;
        if(empty($material_id)) {
            E('_ERR_SHARE_MATERIAL_ID'); //素材id参数错误
        }
        if(empty($status)) {
            E('_ERR_SHARE_STATUS'); //素材状态参数错误
        }

        $desc = isset($_POST['desc']) ? htmlspecialchars($_POST['desc']) : '';
        $material_model = M('Material');

        $material = $material_model->find($material_id);
        if($status == $material['status']) {
            return true;
        }

        $material_model->status = $status;
        $material_model->last_modify_time = time();
        $material_id = $material_id;
        if ($material_model->where('material_id='.$material_id)->save()) {
            //添加更新日志信息
            $material_log = M('MaterialLog');
            $data['material_id'] = $material_id;
            $data['c_time'] = time();
            $data['status'] = $status;
            $data['desc'] = $desc;
            $material_log->add($data);

            $this->sendMsg($material_id,$status,$desc);
            return true;
        }
    }

    //1审核中，2 已通过，3以驳回
    function sendMsg($material_id,$status,$desc) {
        $wxmsg = \Common\Common\WxqyMsg::instance();
        date_default_timezone_set('PRC');
        $arr = array(2,3);
        $msg = '';
        if (in_array($status,$arr)) {
            $material_model = M("Material");
            $material = $material_model->find($material_id);
            if (2 == $status) {
                D('Score/Score', 'Service')->scoreRuleChange($material['m_uid'], 1);
                $score_info = M("ScoreRule")->find(1); //创友分享积分规则
                $msg.='你提交的素材已被采纳'.PHP_EOL;
                $msg.='素材标题:'.$material['title'].PHP_EOL;
                $msg.='奖励积分:'.$score_info['score'].PHP_EOL;
                $msg.='继续努力哦';
            } else {
                $msg.='你提交的素材已被驳回'.PHP_EOL;
                $msg.='素材标题:'.$material['title'].PHP_EOL;
                $msg.='驳回理由:'.$desc.PHP_EOL;
            }
            $u_info[] = $material['m_uid'];
            // echo $msg;die();
            $wxmsg->send_text($msg, $u_info);
        }
    }

    //新建素材
    public function add($arr_login) {

        $post   = I('post.');
        $model = M('Material');

        foreach($post as $k=>$v) {
            $model->$k = $v;
        }
        $model->m_uid = $arr_login['m_uid'];
        $model->c_time = time();
        $model->last_modify_time = time();
        $model->status = 1;
        $id = $model->add();
    }

    //编辑素材
    public function edit($arr_login) {

        $post   = I('post.');
        $material_id = isset($_POST['material_id']) ? intval($_POST['material_id']) : 0;
        if(empty($material_id)) {
            E('_ERR_SHARE_MATERIAL_ID'); //素材id参数错误
        }

        $model = M('Material');
        $data['material_id'] = $post['material_id'];
        unset($post['material_id']);
        foreach($post as $k=>$v) {
            $data[$k] = $v;
        }
        $data['m_uid'] = $arr_login['m_uid'];

        $data['last_modify_time'] = time();
        if ($model->save($data)) {
            $material_log = M('MaterialLog');
            $data['material_id'] = $data['material_id'];
            $data['c_time'] = time();
            $data['status'] = 1;
            $data['desc'] = '驳回后重新编辑！';
            $material_log->add($data);
        }
    }


    //删除附件记录
    public function delFile($at_id) {
        M('CommonAttachment')->delete($at_id);
    }

}
