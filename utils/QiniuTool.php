<?php
/**
 * QiniuTool.php.
 * User: lvfk
 * Date: 2017/12/25 0025
 * Time: 17:35
 * Desc: 七牛云SDK封装 (https://developer.qiniu.com/kodo/sdk/1241/php#overwrite-uptoken)
 */

namespace app\utils;


use Qiniu\Auth;
use Qiniu\Config;
use Qiniu\Storage\BucketManager;
use Qiniu\Storage\UploadManager;

class QiniuTool
{
    private $domain = 'o7zgluxwg.bkt.clouddn.com';
    private $accessKey = "toix9okVaTB0uz6oxPe_vTnW-psg62jGuQOb01uZ";
    private $secretKey = "9BbqEK8nmW-LlLWnmt4Aqe3CWWKN-IiSJMDlY0a3";
    private $bucket = "imooc-shop";
    private $auth = null;
    public function __construct()
    {
        // 初始化Auth状态
        $this->auth = new Auth($this->accessKey, $this->secretKey);
    }

    /**
     *获取上传的凭证
     * @param int $expires
     * @param null $policy
     * @return string
     */
    private function getUpToken($expires = 3600 , $policy=null){
        $cache = \Yii::$app->cache;
        $key = 'qiniu_upload_key';

        $upToken = $cache->get($key);
        if(empty($upToken)){
            $upToken = $this->auth->uploadToken($this->bucket, null, $expires, $policy, true);
            $cache->set($key, $upToken, $expires);
        }
        return $upToken;
    }

    /**
     * 文件上传
     * @param string $filePath 待上传文件路径
     * @param string $key 上传到七牛后保存的文件名
     * @return bool|string
     */
    public function fileUpload($filePath, $key){
        //获取上传凭证
        $returnBody = '{"key":"$(key)","hash":"$(etag)","fsize":$(fsize),"bucket":"$(bucket)","name":"$(x:name)"}';
        $policy = array(
            'returnBody' => $returnBody
        );
        $upload_token = $this->getUpToken(7200, $policy);

        // 初始化 UploadManager 对象并进行文件的上传。
        $uploadMgr = new UploadManager();
        // 调用 UploadManager 的 putFile 方法进行文件的上传。
        list($ret, $err) = $uploadMgr->putFile($upload_token, $key, $filePath);
        if ($err !== null) {
            return false;
        } else {
            return 'http://'.$this->domain.'/'.$ret['key'];
        }
    }

    /**
     * 删除文件
     * @param $key
     */
    public function delete($key){
        $config = new Config();
        $bucketManager = new BucketManager($this->auth, $config);
        $err = $bucketManager->delete($this->bucket, $key);
        if ($err) {
            print_r($err);
        }
    }

    /**
     * 获取公开资源访问链接
     * @param $key
     * @return string
     */
    public function getPublicUrl($key){
        return 'http://'.$this->domain.'/'.$key;
    }

    /**
     * 获取私有资源访问链接
     * @param $key
     * @return string
     */
    public function getPrivateUrl($key){
        $url = 'http://'.$this->domain.'/'.$key;
        return $this->auth->privateDownloadUrl($url);
    }
}