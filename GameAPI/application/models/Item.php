<?php

class ItemModel extends BaseModel {

    const TYPE_GOLD = 1;
    const TYPE_CREDIT = 2;
    
    private static $_static_data = [];
    private $data = [];
    public $id;
    public $type;
    public $name;

    public function __construct(int $item_id) {
        try {
            if (!isset(self::$_static_data[$item_id])) {
                self::$_static_data[$item_id] = $this->DB()->query_fetch('select id, type, name from ms_item where id = ?', [$item_id]);
            }
            $this->data = self::$_static_data[$item_id];
            $this->id = $this->data['id'];
            $this->name = $this->data['name'];
            $this->type = $this->data['type'];
        } catch (Exception $e) {
            throw new Exception('无法处理的参数');
        }
    }

    /**
     * 将物品给到玩家
     * @param int $user_id
     * @param int $count
     * @return bool
     */
    public function giveToUser(int $user_id, int $count = 1, $why = '') {
        try{
            $this->addItemLog($user_id, ['item_id' => $this->id, 'item_count' => $count, 'why' => $why]);
            $userData = new Data\UserModel($user_id);
            if($this->type == self::TYPE_GOLD){
                $userData->addGold($count);
                return true;
            }else if($this->type == self::TYPE_CREDIT){
                $userData->addCredit($count);
                return true;
            }
        }catch(Exception $e){
            logMessage($e->getMessage(), LOG_ERR);
        }
        return false;
    }
    
}
