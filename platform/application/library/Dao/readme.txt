/*=====================================================================
 * @author  Gino
/*=====================================================================
初始化：
$db = new Dao\Connection(array(
    'host' => '127.0.0.1',
    'port' => '3306',
    'username' => 'root',
    'password' => '123456',
    'dbname' => 'test',
    'charset' => 'utf8'
));

查询：
$user = $db->getTable('user');
$user->load(主键ID);
$user->loadFirst(条件);
$user->loadAll(条件);
或者
$select = $db->newSelect('user');
$select->addFilter('name', '张三'); //条件
$db->fetch($select);
$db->fetchAll($select);
$db->fetchAllPage($select, 页码, 每页显示数目);
再或者
$sql = 'select * from user';
$db->query($sql);  //返回PDOStatement
$db->search($sql); //直接返回数据

增加：
$user = $db->getTable('user');
$user->name = '李四';
$user->save();
或者
$select = $db->newSelect('user');
$select->insert(array('name'), array($user, array('王五')));
再或者
$sql = 'insert into user(name) values("王五")';
$db->exec($sql);

更新：
$user = $db->getTable('user');
$user->load(主键ID);
$user->name = '李四';
$user->save();
或者
$sql = 'update user set name = "王五" where id=主键ID';
$db->exec($sql);

删除：
$user = $db->getTable('user')->load(1);
$user->remove();
或者
$select = $db->newSelect('user');
$select->addFilter('name', '张三'); //条件
$db->exec($select->deleteSql());
再或者
$sql = 'delete from user where name = "王五"';
$db->exec($sql);


//=============================================================================
//初始化
define('MIRACLE_CACHE', true);
define('MIRACLE_CACHE_DIR', __DIR__ . '/miracle_runtime/');
if (!file_exists(MIRACLE_CACHE_DIR)) {
    mkdir(MIRACLE_CACHE_DIR);
    chmod(MIRACLE_CACHE_DIR, 0777);
}

//=============================================================================
