<?php
/**
 * 安全箱
 * User: jim
 * Date: 2017/3/10 0010
 * Time: 16:53
 */

class SafeModel extends BaseModel{

    /**
     * @param $user_id int
     * @param $amount int
     * @return int
     * @throws Exception
     */
    public function addGold(int $user_id, int $amount):int{
        if (!is_array($user_id)) {
            $user_id = array($user_id);
        }

        if($amount <= 0){
            return false;
        }

        $select = $this->DB()->newSelect('ms_safe');
        $select->whereIn('user_id', $user_id);
        $select->where('status', 1);
        $select->setData('gold', ['gold + '.$amount]);

        return $this->DB()->exec($select->updateSql());
    }
}