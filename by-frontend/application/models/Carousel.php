<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/14 0014
 * Time: 21:01
 */
class CarouselModel extends BaseModel{

    /**
     * 查询列表
     * @return array|bool
     * @throws Exception
     */
    public function getList(){
        $sql = <<<SQL
                select id, img_url, jump_url from by_carousel where is_hide=0 order by 'order'  
SQL;
        $result = $this->DB()->query($sql);

        return $result;
    }
}