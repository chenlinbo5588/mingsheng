<?php
/*
 * 使用luosibao发送短信类
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class forum_sendmsg {
    
    //Api url
    private $apiUrl = 'https://sms-api.luosimao.com/v1/send.json';
    
    //Api Key
    private $apiKey = 'api:key-033c6d22cc138b989cfba829f68e17f1';
    
    private $msgPre = '民e通提醒：';
    
    private $msgEnd = '【慈溪网络民生服务台】';
    
    /*
     * 获取apiurl
     */
    public function getApiUrl() {
        return $this->apiUrl;
    }
    
    /*
     * 获取apikey
     */
    public function getApiKey() {
        return $this->apiKey;
    }
    
    /*
     * 设置apiurl
     */
    public function setApiUrl($url) {
        $this->apiUrl = $url;
    }
    
    /*
     * 设置apikey
     */
    public function setApiKey($key) {
        $this->apiKey = $key;
    }
    
    /*
     * 通过帖子tid发送信息，使用发帖者(或者版主)的手机号发送
     * @param $tid 帖子id
     * @param $usefid 是否使用版主手机号发送, 默认为false
     * 
     * @return 信息发送状态true/false
     */
    public function send_msg_tid($tid = 0, $usefid = false, $message = '',$extra = '已审核') {
        $res = false;

        $msgpre = '民e通提醒：';
        $msgend = '【慈溪网络民生服务平台】';

        if ($tid) {
            $thread = C::t('forum_thread')->fetch($tid);
            $subject = cutstr($thread['subject'], 20, '......');
            $authorid = $thread['authorid'];
            if ($usefid) {

                $uidIds = C::t('forum_moderator')->fetch_uid_by_fid($thread['fid']);
                $moderator = array();
                foreach($uidIds as $u){
                    $moderator[] = $u['uid'];
                }
                
                $uinfo = C::t('common_member')->fetch_all_by_uid($moderator,' AND groupid IN (3) ');
                $uid = array();
                foreach($uinfo as $v){
                    $uid[] = $v['uid'];
                }

                if ($message == '') {
                    $message = $msgpre.'您好,你有一条新的未受理主题,请访问 http://'.$_SERVER['SERVER_NAME'].'/forum.php?mod=modcp&action=thread&op=thread&fid='.$thread['fid'].' ,请速处理。'.$msgend;
                } else {
                    $message = $msgpre.$message.$msgend;
                }
            } else {
                $uid = $authorid;
                if ($message == '') {
                    $message = $msgpre.'您好,你提交的问题'.$extra.',请访问 http://'.$_SERVER['SERVER_NAME'].' 查阅。'.$msgend;
                } else {
                    $message = $msgpre.$message.$msgend;
                }
            }

            
            if(!is_array($uid)){
                $uid = (array)$uid;
            }
            
            $userinfo = C::t('common_member_profile')->fetch_all($uid);
            
            foreach($userinfo as $u){
                // @todo delete test code
                //file_put_contents('dx.txt',print_r($u,true),FILE_APPEND);
                $res = $this->send_message($message, $u['mobile']);
                //file_put_contents('dx.txt',print_r($res,true),FILE_APPEND);
            }

        }
        return $res;
    }

    /*
     * 使用螺丝帽借口发送短信
     */   
    public function send_message($message = '', $mnumber = '') {
        
        if (!$message || !$mnumber) {
            return false;
        }
        $message = $this->msgPre.$message.$this->msgEnd;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);

        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->apiKey);


        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $mnumber, 'message' => $message));

        $res = curl_exec($ch);
        curl_close($ch);
        return $res['error'] ? false : true;
    }

    /*
     * 使用螺丝帽借口获取账号余额
     */

    public function get_balance() {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->apiKey);

        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

}
