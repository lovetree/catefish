<?php

namespace backend\models;

/**
 * This is the ActiveQuery class for [[Account]].
 *
 * @see Account
 */
class ByQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return Account[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Account|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}