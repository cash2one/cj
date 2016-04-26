<?php
/**
 * 应用安装时的初始数据文件
 * data.php
 * $Author$
 */

return "
INSERT INTO `[PREFIX]guestbook_setting[SUFFIX]` (`key`, `value`, `type`, `comment`, `status`, `created`, `updated`, `deleted`) VALUES
('perpage', '30', 0, '分页个数', 1, 0, 0, 0);
";
