<?php

namespace Jobtrain\Controller\Api;
use Common\Common\Cache;
use Org\Util\String;
use Think\Log;

class ArticleController extends AbstractController {

	/**
	 * 获取文章列表
	 */
    public function list_get() {
        $s_article = D('Jobtrain/JobtrainArticle', 'Service');
        $s_cata = D('Jobtrain/JobtrainCategory', 'Service');
        // 获取搜索参数
        $cid = I('get.cid',0,'intval');
        $type_id = I('get.type_id');
        $is_study = I('get.is_study',0,'intval');
        $keywords = I('get.keywords','');
        $page = I('get.page', 1, 'intval');
        $cids = I('get.cids','');
        // 获取分类id范围
        if($cids!=''&&$cid!=0){
        	$cids_arr = explode(',', $cids);
        	$idarr = array($cid);
        	$catas = $s_cata->list_by_conds(array('pid'=>$cid,'is_open'=>1));
        	foreach ($catas as $v) {
        		if(in_array($v['id'], $cids_arr)) {
        			$idarr[] = $v['id'];
        		}
        	}
        	$cids = implode(',', $idarr);
        }
        // 设置分页
        $pagesize = 8;
        list($start, $limit, $page) = page_limit($page, $pagesize);
        // 获取文章列表 多读取一条
        $result = $s_article->get_list($cids, $type_id, $keywords, $is_study, $this->_login->user['m_uid'], $start, $limit+1);
       
        // 计算数量 删除最后一个
        $count = count($result['list']);
        if($count>$pagesize){
            array_pop($result['list']);
        }
        // 输出结果
        $this->_result = array(
            'total' => $count<=$pagesize?0:$pagesize*$page+1,
            'list' => $result['list'],
            //'total' => $result['total']
        );
        return true;
    }

    /**
	 * 获取文章详情
	 */
    public function detail_get() {
        $s_article = D('Jobtrain/JobtrainArticle', 'Service');
        $s_study = D('Jobtrain/JobtrainStudy', 'Service');
        $s_coll = D('Jobtrain/JobtrainColl', 'Service');
        $s_comment = D('Jobtrain/JobtrainComment', 'Service');
        $cache = &\Common\Common\Cache::instance();
        $cache_setting = $cache->get('Common.setting');
        
        $id = I('get.id',0,'intval');
        // 获取文章
        $result = $s_article->get( $id );
        if(empty($result)){
            // 内容被删除无法查看
            //$this->_set_error('_ERR_ARTICLE_ID');
            $this->_result = array(
                'detail' => array('d_status'=>2)
            );
            return false;
        }
        // 判断状态 d_status: 1正常 2删除 3无权限 4分类被禁用
        $s_right = D('Jobtrain/JobtrainRight', 'Service');
        // 检查权限
        $d_status = 1;
        if( !$s_right->check_right($result['id'], $result['cid'], $this->_login->user['m_uid'], $result['is_publish']) ){
            $d_status = 3;
        }else{
            $d_status = 1;
        }
        // 检查分类是否被禁用
        if($d_status == 1){
            $s_cata = D('Jobtrain/JobtrainCategory', 'Service');
            $cata = $s_cata->get( $result['cid'] );
            if($cata['is_open']==0){
                $d_status = 4;
            }
        }
        if($result['is_publish']==1){
            // 获取学习情况
            $study = $s_study->get_by_conds(array('aid' => $result['id'], 'm_uid' => $this->_login->user['m_uid']));
            if(!$study){
            	// 如果未学习 插入学习情况
    			$dps = $cache->get('Common.department');
            	$jobs = $cache->get('Common.job');
            	$job = $jobs[$this->_login->user['cj_id']]['cj_name'];
            	$data = array(
    				'aid' => $result['id'],
    				'm_uid' => $this->_login->user['m_uid'],
    				'm_username' => $this->_login->user['m_username'],
    				'department' => $dps[$this->_login->user['cd_id']]['cd_name'],
    				'job' => $job?$job:'',
    				'mobile' => $this->_login->user['m_mobilephone'],
    				'study_time' => NOW_TIME
            	);
            	$s_study->insert($data);
            	$s_article->inc_study_num($result['id']);
            }
        }
        // 附件处理
        $attachments = unserialize($result['attachments']);
        // 格式化
		$detail = array(
			'id' => $result['id'],
            'title' => $result['title'],
        	'content' => $result['content'],
        	'publish_time' => $result['publish_time'],
        	'author' => $result['author'],
        	'study_num' => $result['study_num'],
        	'attachs' => $attachments['attachs'],
        	'is_comment' => $result['is_comment'],
        	'is_secret' => $result['is_secret'],
            'is_publish' => $result['is_publish'],
            'd_status' => $d_status,
            'type' => $result['type'],
            'summry' => String::msubstr(preg_replace ( "/(\<[^\<]*\>|\r|\n|\s|\[.+?\])/is", '', $result['summary']),0,64),
            'cover' => $s_article->get_attachment($result['cover_id'])
		);
		// 按类型获取参数
		if($result['type'] == 1) {
			// 音图
			$detail['is_loop'] = $result['is_loop'];
			$detail['audimgs'] = $attachments['audimgs'];
            foreach ($detail['audimgs'] as &$v) {
                $v['audio_url'] = $v['audio_id']?$cache_setting['domain'].'/attachment/read/'.$v['audio_id']:'';
                $v['img_url'] = $cache_setting['domain'].'/attachment/read/'.$v['img_id'];
                unset($v['audio_id'],$v['img_id']);
            }
		}else if($result['type'] == 2) {
			// 视频
            cfg('JOBTRAIN', load_config(APP_PATH.'Jobtrain/Conf/config.php'));
			$detail['video_appid'] = cfg('JOBTRAIN.APP_ID');
            $detail['video_id'] = $result['video_id'];
		}
        if($result['is_secret'] == 1){
            $detail['watermark'] = cfg('PROTOCAL') . $cache_setting['domain'] . '/Jobtrain/Api/Watermark/watermark_get';
        }
		// 获取收藏
        $coll = $s_coll->get_by_conds(array('aid' => $result['id'], 'm_uid' => $this->_login->user['m_uid']));
        $detail['is_coll'] = $coll?true:false;
        // 获取评论数
        $detail['comment_num'] = $s_comment->count_by_conds(array('aid' => $result['id']));        
                
		unset($result);
        $this->_result = array(
            'detail' => $detail
        );
        return true;
    }

    /**
	 * 获取视频详情
	 */
    public function video_get() {
    	Vendor('QcloudApi.QcloudApi');
    	// 获取配置信息
    	cfg('JOBTRAIN', load_config(APP_PATH.'Jobtrain/Conf/config.php'));
        $video_id = I('get.video_id');

    	$config = array('SecretId'       => cfg('JOBTRAIN.SECRET_ID'),
                'SecretKey'      => cfg('JOBTRAIN.SECRET_KEY'),
                'RequestMethod'  => 'GET',
                'DefaultRegion'  => 'gz');

    	$service = \QcloudApi::load(\QcloudApi::MODULE_VOD, $config);
    	$package = array('fileId' => $video_id);
    	$a = $service->DescribeVodPlayUrls($package);
    	$this->_result = $a['playSet'];
        return true;
    }
    /**
     * 推送附件
     */
    public function attach_push_get() {
        $id = I('get.id');
        $serv = &\Common\Common\Wxqy\Service::instance();
        $serv_media = new \Common\Common\Wxqy\Media($serv);
        $serv_a = D('Common/CommonAttachment', 'Service');
        // 获取附件
        $attach = $serv_a->get($id);
        if($attach){
            // 获取附件物理路径
            $z_path = get_sitedir();
            $attach_path = str_replace('/thinkphp/Apps/Runtime/Temp', '/apps/voa/data/attachments', $z_path) . $attach['at_attachment'];
            $file = array();
            // 上传附件
            $serv_media->upload_file($file, array('path' => $attach_path, 'name' => $attach['at_filename']));
            $result = false;
            if($file['media_id']){
                $wxqyMsg = &\Common\Common\WxqyMsg::instance();
                $model_plugin = D('Common/CommonPlugin');
                $plugin = $model_plugin->get_by_identifier('jobtrain');
                // 推送附件
                $wxqyMsg->send_file($file['media_id'], array($this->_login->user['m_uid']), '', $plugin['cp_agentid'], $plugin['cp_pluginid']);
                $result = true;
            }
        }

        $this->_result = $result;
    }


}