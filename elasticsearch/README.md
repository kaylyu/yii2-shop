环境配置
1、elasticsearch-2.4.1
2、elasticsearch-analysis-ik-1.10.1.zip
3、elasticsearch-jdbc-2.3.4.0

官网开发手册：https://www.elastic.co/guide/en/elasticsearch/reference/current/index.html

请参考对应版本的elasticsearch开发手册,如下是elasticsearch的使用demo

源码安装启动：
./bin/elasticsearch

1、创建索引,索引为yii2_shop，索引类型为products
createindex.json 创建搜索索引配置
curl "http://localhost:9200/yii2_shop" -d "@/var/www/yii2-shop/elasticsearch/createindex.json"

2、添加或更新数据
curl -XPUT "http://localhost:9200/yii2_shop/products/1" -d '{"productid":1,"title":"这是一个商品的标题","descr":"这是一个商品的描述"}'
curl -XPUT "http://localhost:9200/yii2_shop/products/2?pretty" -d '{"productid":2,"title":"这是一个商品的标题2","descr":"这是一个商品的描述2"}'
curl -XPOST "http://localhost:9200/yii2_shop/products/3?pretty" -d '{"productid":3,"title":"小米note3手机","descr":"小米公司的手机都很不错，特别是小米note3"}'

3、查看数据
curl -XGET "http://localhost:9200/yii2_shop/_search?pretty"

依托JSON参数进行查询,并且可以对搜索的关键字进行高亮（主要是给搜索内容进行样式的添加，配合前端显示）
curl -XPOST "http://localhost:9200/yii2_shop/_search?pretty" -d "@/var/www/yii2-shop/elasticsearch/search.json"

4、删除数据
curl -XDELETE "http://localhost:9200/yii2_shop/products/1?pretty"
curl -XDELETE "http://localhost:9200/yii2_shop/products/2?pretty"
curl -XDELETE "http://localhost:9200/yii2_shop/products/3?pretty"


5、使用elasticsearch-jdbc-2.3.4.0工具批量把mysql中的数据导入到elasticsearch中，
在/elasticsearch-jdbc-2.3.4.0/bin中新建一份mysql-import-product.sh，按照配置填写完毕
并执行/elasticsearch-jdbc-2.3.4.0/bin/mysql-import-product.sh
cd /elasticsearch-jdbc-2.3.4.0/bin
sudo /bin/bash mysql-import-product.sh

查看日志
cat logs/jdbc.log

查看数据导入是否成功
curl -XGET "http://localhost:9200/yii2_shop/_search?pretty"

sh配置文件说明
echo '
{
    "type" : "jdbc",
    "jdbc" : {
        "url" : "jdbc:mysql://localhost:3306/yii2_shop",//数据库名称
        "user" : "root",//用户名
        "password" : "root",//密码
        "sql" : "select *, productid as _id from shop_product",//把表所有数据填充到es，并把productid当作es的id
        //"sql" : "select title,descr,productid, productid as _id from shop_product",//把表指定数据填充到es，并把productid当作es的id
        "index" : "yii2_shop",//索引名称
        "type" : "products",//类型
        "metrics": {
            "enabled" : true
        },
        "elasticsearch" : {
             "cluster" : "yii2-shop-search",//集群名称
             "host" : "10.168.1.216",
             "port" : 9300//集群端口，与http端口9200不一样
        }   
    }
}

6、定时自动导入数据到es中，
在/elasticsearch-jdbc-2.3.4.0/bin中新建一份mysql-delta-import-product.sh，按照配置填写完毕
并执行/elasticsearch-jdbc-2.3.4.0/bin/mysql-delta-import-product.sh
cd /elasticsearch-jdbc-2.3.4.0/bin
sudo /bin/bash mysql-delta-import-product.sh

{
    "type" : "jdbc",
    "jdbc" : {
        "url" : "jdbc:mysql://localhost:3306/yii2_shop",
        "schedule" : "0 0-59 0-23 ? * *",   //定时任务
        "user" : "root",
        "password" : "root",
        "sql" : [{//此处结合数据库的更新时间字段进行导入
                "statement": "select title,descr,productid, productid as _id from shop_product where updatetime > unix_timestamp(?)",
                "parameter": ["$metrics.lastexecutionstart"]}
            ],
        "index" : "yii2_shop",
        "type" : "products",
        "metrics": {
            "enabled" : true
        },
        "elasticsearch" : {
             "cluster" : "yii2-shop-search",
             "host" : "10.168.1.216",
             "port" : 9300
        }
    }
}