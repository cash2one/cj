<?php
/**
 * upexam.php
 * 比对数据表
 * @uses php tool.php -n upexam
 * $Author$
 * $Id$
 */
class voa_backend_tool_upexam extends voa_backend_base {
	/** 参数 */
	private $__opts = array();

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		return;
		/** 连接数据库 */
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$db = db::init($cfg);

		/** 判断数据库是否存在 */
		for ($i = 10002; $i < 36872; ++ $i) {
			try {
				echo 'use ep_'.$i."\n";
				if ($i > 36700) {
					$cfg['host'] = '10.66.141.207';
					$cfg['pw'] = '88d8K88rMhQse4MD';
					$tablepre = $cfg['tablepre'];
					$db = db::init($cfg);
				}

				$db->query('use ep_'.$i);
				//$db->query("INSERT INTO `oa_common_plugin` (`cp_pluginid`, `cp_identifier`, `cmg_id`, `cpg_id`, `cp_suiteid`, `cp_agentid`, `cp_displayorder`, `cp_available`, `cp_adminid`, `cp_name`, `cp_icon`, `cp_description`, `cp_datatables`, `cp_directory`, `cp_url`, `cp_version`, `cp_lastavailable`, `cp_lastopen`, `cyea_id`, `cp_status`, `cp_created`, `cp_updated`, `cp_deleted`) VALUES(38,	'exam',	1,	5,	'',	'',	1026,	0,	0,	'考试',	'exam.png',	'不受空间和时间限制，员工随时进行职业技能的测评，快速反馈结果。支持判断、单选、多选、填空等多种题型，灵活的题库管理，抽题、查询、提醒、统计等多种功能全支持，及时了解员工的能力提升情况。',	'exam*', 'exam', 'exam.php', '0.1', 0, 0, 0, 1, 0, 0, 0)");
				$db->query("INSERT INTO `oa_common_plugin` (`cp_pluginid`, `cp_identifier`, `cmg_id`, `cpg_id`, `cp_suiteid`, `cp_agentid`, `cp_displayorder`, `cp_available`, `cp_adminid`, `cp_name`, `cp_icon`, `cp_description`, `cp_datatables`, `cp_directory`, `cp_url`, `cp_version`, `cp_lastavailable`, `cp_lastopen`, `cyea_id`, `cp_status`, `cp_created`, `cp_updated`, `cp_deleted`) VALUES(36,	'chatgroup',	1,	7,	'',	'',	1025,	0,	0,	'同事聊天',	'chatgroup.png',	'同事交流最好的平台；无需添加好友，同事之间可以直接发起会话，PC端和微信手机端消息实时互通。',	'chatgroup*',	'chatgroup',	'chatgroup.php',	'0.1',	0,	0,	0,	1,	1417145069,	0,	0)");
				$db->query("INSERT INTO `oa_common_plugin` (`cp_pluginid`, `cp_identifier`, `cmg_id`, `cpg_id`, `cp_suiteid`, `cp_agentid`, `cp_displayorder`, `cp_available`, `cp_adminid`, `cp_name`, `cp_icon`, `cp_description`, `cp_datatables`, `cp_directory`, `cp_url`, `cp_version`, `cp_lastavailable`, `cp_lastopen`, `cyea_id`, `cp_status`, `cp_created`, `cp_updated`, `cp_deleted`) VALUES(37,	'blessingredpack',	1,	4,	'',	'',	1025,	0,	0,	'祝福红包',	'blessredpack.png',	'激励员工自发了解企业文化，员工、企业心连心；打破传统，让企业福利变得更生动诱人。',	'blessredpack*',	'blessredpack',	'blessredpack.php',	'0.1',	0,	0,	0,	1,	1417145069,	0,	0)");
			} catch (Exception $e) {
				continue;
			}

		}

	}

}
