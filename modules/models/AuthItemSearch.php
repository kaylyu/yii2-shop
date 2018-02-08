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

class AuthItemSearch extends AuthItem
{
    public function rules()
    {
        return [
          [['name','type','rule_name','description','created_at','updated_at'],'safe']
        ];
    }

    /**
     * 获取角色权限列表
     * @param $params
     * @return ActiveDataProvider
     */
    public function search($params){
        $query = parent::find()->where('type=:type',[':type'=>1]);
        $dataPprovider = new ActiveDataProvider([
           'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'sort' => [
                'attributes' => [
                    'name', 'rule_name','description','created_at','updated_at'
                ],
                'defaultOrder' => ['created_at' => SORT_DESC]
            ]
        ]);

        $this->load($params);
        if(!$this->validate()){
            return $dataPprovider;
        }

        $query->andFilterWhere(['like','name', $this->name])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'rule_name', $this->rule_name])
            ->andFilterWhere(['like', 'created_at', $this->created_at])
            ->andFilterWhere(['like', 'updated_at', $this->updated_at]);

        return $dataPprovider;
    }
}