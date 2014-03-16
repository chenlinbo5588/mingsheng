<?php
/*
 * 使用luosibao发送短信类
 */
if (!defined('IN_DISCUZ')) {
    exit('Access Denied');
}

class forum_sendmsg {
    
    //Api url
    private $apiUrl = 'https://sms-api.luosimao.com/v1/send.xml';
    
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
    public function send_msg_tid($tid = 0, $usefid = false, $message = '') {
        $res = false;
        $msgpre = $this->msgPre;
        $msgend = $this->msgEnd;
        if ($tid) {
            $thread = C::t('forum_thread')->fetch($tid);
            $subject = cutstr($thread['subject'], 20, '......');
            $authorid = $thread['authorid'];
            if (!$usefid) {
                $uid = C::t('forum_moderator')->fetch_uid_by_tid($tid);
                if ($message == '') {
                    $message = "$msgpre您提交的”$subject“,现已答复，请访问".$_SERVER['SERVER_NAME']."查阅。$msgend";
                } else {
                    $message = "$msgpre$message$msgend";
                }
            } else {
                $uid = $authorid;
                if ($message == '') {
                    $message = "$msgpre”$subject“[未受理],请速处理。$msgend";
                } else {
                    $message = "$msgpre$message$msgend";
                }
            }
            $userinfo = C::t('common_member_profile')->count_by_field('uid', $uid);
            $mobile = $userinfo['mobile'];
            $res = $this->send_message($message, $userinfo['mobile']);
        }
        return $res;
    }

    /*
     * 使用螺丝帽借口发送短信
     */   
    public function send_message($message = '', $mnumber = '') {
        $res = array('error' => 1);
        if (!$message || !$mnumber) {
            $res['msg'] = 'No message or number.';
            return $res;
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
        return $res;
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
