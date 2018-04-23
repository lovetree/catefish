<?php
namespace Player;
class PhoneblackModel extends \BaseModel {
/**
     * 获取列表
     * @param int $page
     * @param int $pagesize
     * @param array $args
     * @return array
     */
public function lists(int $page = 1, int $pagesize = 15, array $args = []): array {
        $db = $this->DB();
        $select = $db->newSelect('ms_black_white_list')
            ->join('ms_user mu', 'mu.id = main_table.user_id')
            ->select('main_table.id')
            ->select('user_id')
            ->select('mu.nickname')
            ->select('mu.username')
            ->select('mu.wx_unionid')
            ->select('FROM_UNIXTIME(main_table.created_at,\'%Y-%m-%d %H:%i:%s\') as created_at', true)
            ->select('main_table.status')
            ->where('main_table.type', $args['type'])
           ->where('main_table.status', -1, '<>');

        $data = $db->fetchAllPage($select, $page, $pagesize);

        $data['data'] = $data['list']->toArray();
        unset($data['list']);
        return $data;
    }

    /**
     * 创建
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function create(array $params = []) {
        $params = array_fetch($params, 'user_id', 'type','wx_unionid');
        $user= new \Player\UserModel();
        $info=$user-> _getUser(['wx_unionid'=>$params['wx_unionid']]);
        $params['user_id']=$info['id'];

        $model = $this->DB()->getTable('ms_black_white_list');
        $model->setData(array_merge(['created_at'=>time()], $params));
        $status = $model->save();
        if (!$status) {
            return false;
        }

        //redis
        $redis = \PRedis::instance();
        $redis->select(R_GAME_DB);
        $redis->sAdd($params['type'] == 1 ? 'blacklist' : 'whitelist', $params['user_id']);

        return true;
    }

    /**
     * 修改
     * @param array $params
     * @return mixed false: 保存失败;
     */
    public function edit(int $id, array $params = []) {
        $params = array_fetch($params, 'user_id','wx_unionid');
        $user= new \Player\UserModel();
        $info=$user-> _getUser(['wx_unionid'=>$params['wx_unionid']]);
        $params['user_id']=$info['id'];

        $model = $this->DB()->getTable('ms_black_white_list');
        if (!$model->load($id)) {
            return false;
        }
        $model->setData( $params);
        $status = $model->save();
        if (!$status) {
            return false;
        }
        return true;
    }

    /**
     * 删除记录
     * @param int|array $id
     * @return boolean
     */
    public function delete($id, $type) {
        if (!is_array($id)) {
            $id = [$id];
        }
        $this->DB()->trancation(function($db, &$rollback) use($id, $type) {
            $select = $db->newSelect('ms_black_white_list');
            $select->whereIn('id', $id);
            $select->setData('status', -1);
            $status = $db->exec($select->updateSql());
            if($status){
                $redis = \PRedis::instance();
                $redis->select(R_GAME_DB);
                foreach ($id as $item){
                    $obj = $db->fetch($db->newSelect('ms_black_white_list')
                        ->select('user_id')
                        ->where('id', $item));

                    $redis->sRem($type == 1 ? 'blacklist' : 'whitelist', $obj->user_id);
                }
            }
        });

        return true;
    }
}
