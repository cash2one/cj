<?php
/**
 * 应用安装时的初始数据文件
 * data.php
 * $Author$
 */

return "
INSERT INTO `[PREFIX]chatgroup_setting[SUFFIX]` (`cgs_key`, `cgs_value`, `cgs_type`, `cgs_comment`, `cgs_status`, `cgs_created`, `cgs_updated`, `cgs_deleted`) VALUES
('perpage', '10', 0, '分页个数', 1, 0, 0, 0);
";
