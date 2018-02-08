<?php
/**
 * RolesSearch.php.
 * User: lvfk
 * Date: 2018/1/24 0024
 * Time: 17:39
 * Desc:
 */

namespace app\modules\models;


use yii\data\ActiveDataProvider;

class AuthRuleSearch extends AuthRule
{
    public function rules()
    {
        return [
          [['name','created_at','updated_at'],'safe']
        ];
    }

    /**
     * 获取角色权限列表
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params){
        $query = parent::find();
        $dataPprovider = new ActiveDataProvider([
           'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'name', 'created_at','updated_at'
                ],
                'defaultOrder' => ['created_at' => SORT_DESC]
            ]
        ]);

        $this->load($params);
        if(!$this->validate()){
            return $dataPprovider;
        }

        $query->andFilterWhere(['like','name', $this->name])
            ->andFilterWhere(['like', 'created_at', $this->created_at])
            ->andFilterWhere(['like', 'updated_at', $this->updated_at]);

        return $dataPprovider;
    }
}