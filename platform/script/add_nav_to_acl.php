<?php
require_once __DIR__ . '/init.php';
$db = new Dao\Connection(Yaf\Application::app()->getConfig()->get('db'));

$nav = include '../conf/nav.conf.php';

foreach ($nav as $k=>$v){
    if(!is_array($v)) continue;

    $result = $db->fetch(
        $db->newSelect('ms_admin_permission')
        ->select('id')
        ->where('group', $k)
    );

    if(!$result){
        $prefix = str_replace('.', '/', strtolower($k));

        $db->exec(
            $db->newSelect('ms_admin_permission')
                ->insert(['path', 'name', 'parent_id', 'rw_type', 'status', 'group'],[
                    [$prefix . '/list', '查询'.$v[0], 0, 0, 1, $k],//查询
                    [$prefix . '/save', '编辑'.$v[0], 0, 1, 1, $k],//编辑
                    [$prefix . '/delete', '查询'.$v[0], 0, 1, 1, $k],//删除
            ])
        );
    }
 }

 function sql(){
  $insert = '
     insert into ms_admin_permission(path,name,parent_id,rw_type,status,`group`) values(\'system/stat/player\', \'查看用户统计信息\', 0, 0, 1, \'SYSTEM.STAT\');
     insert into ms_admin_permission(path,name,parent_id,rw_type,status,`group`) values(\'gamemt/fish/coefficient\', \'捕鱼调控系数\', 0, 1, 1, \'GAMEMT.FISH\');
     insert into ms_admin_permission(path,name,parent_id,rw_type,status,`group`) values(\'gamemt/statistics/user\', \'查看用户统计\', 0, 0, 1, \'SYSTEM.STAT\');
     insert into ms_admin_permission(path,name,parent_id,rw_type,status,`group`) values(\'gamemt/statistics/active\', \'查看用户活跃\', 0, 0, 1, \'SYSTEM.STAT\');
     insert into ms_admin_permission(path,name,parent_id,rw_type,status,`group`) values(\'system/stat/estate\', \'查看存量统计\', 0, 0, 1, \'SYSTEM.STAT\');
     insert into ms_admin_permission(path,name,parent_id,rw_type,status,`group`) values(\'system/stat/generalstat\', \'查看综合统计\', 0, 0, 1, \'SYSTEM.STAT\');
     insert into ms_admin_permission(path,name,parent_id,rw_type,status,`group`) values(\'system/stat/room\', \'查看房间统计\', 0, 0, 1, \'SYSTEM.STAT\');
    ';
 }