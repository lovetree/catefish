<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/14 0014
 * Time: 21:01
 */
class FaqModel extends BaseModel{
    /**
     * 查询列表
     * @return array|bool
     * @throws Exception
     */
    public function getList(){
        $sql = 'select id, question from by_faq';
        $result = $this->DB()->query($sql);

        return $result;
    }

    public function getDetail($faqId){
        $sql = <<<SQL
                select * from by_faq where id=?
SQL;

        $result = $this->DB()->query_fetch($sql, [$faqId]);

        return $result;
    }

    public function addFeed($data){
        $this->DB()->insert('by_feedback',$data);
    }
}