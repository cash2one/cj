<?php
/**
 * upquestion.php
 * 比对数据表
 * @uses php tool.php -n upquestion
 * $Author$
 * $Id$
 */
class voa_backend_tool_upquestion extends voa_backend_base {
	/** 参数 */
	private $__opts = array();

	public function __construct($opts) {

		parent::__construct();
		$this->__opts = $opts;
	}

	public function main() {

		/** 连接数据库 */
		$cfg = config::get('voa.db.dbadmin');
		$tablepre = $cfg['tablepre'];
		$db = db::init($cfg);

		/** 判断数据库是否存在 */
		for ($i = 10003; $i < 39940; ++ $i) {
			try {
				echo 'use ep_'.$i."\n";
				if ($i > 36700) {
					$cfg['host'] = '10.66.141.207';
					$cfg['pw'] = '88d8K88rMhQse4MD';
					$tablepre = $cfg['tablepre'];
					$db = db::init($cfg);
				}

				$db->query('use ep_'.$i);

				$db->query("UPDATE oa_common_plugin SET cp_available=0, cp_name='问卷调查', cp_description='支持多题多类型的强大表单设计，可用于内部数据收集，外部市场调研。灵活设置问卷可见范围、实/匿名答题、定时发布问卷、未填人员提醒、是否允许重复填写、是否可分享。填写情况一键导出，轻松完成在线调查。' WHERE cp_pluginid=46 AND cp_available=255");
				$db->query("INSERT INTO `oa_common_plugin` (`cp_pluginid`, `cp_identifier`, `cmg_id`, `cpg_id`, `cp_suiteid`, `cp_agentid`, `cp_displayorder`, `cp_available`, `cp_adminid`, `cp_name`, `cp_icon`, `cp_description`, `cp_datatables`, `cp_directory`, `cp_url`, `cp_version`, `cp_lastavailable`, `cp_lastopen`, `cyea_id`, `cp_status`, `cp_created`, `cp_updated`, `cp_deleted`) VALUES(46, 'questionnaire', 1, 5, '', '', 1046, 0, 0, '问卷调查', 'questionnaire.png', '支持多题多类型的强大表单设计，可用于内部数据收集，外部市场调研。灵活设置问卷可见范围、实/匿名答题、定时发布问卷、未填人员提醒、是否允许重复填写、是否可分享。填写情况一键导出，轻松完成在线调查。', 'questionnaire*', 'questionnaire', 'questionnaire.php', '0.1', 0, 0, 0, 1, 0, 0, 0)");
			} catch (Exception $e) {
				continue;
			}

		}

	}

}
