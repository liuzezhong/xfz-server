<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/10/1
 * Time: 20:58
 */
namespace Sapp\Controller;

class LoginController extends \Think\Controller
{
    public function saveUserInfo() {

        if($_POST['userInfo'] && $_POST['sessionKey']) {
            $sessionKey = json_decode($_POST['sessionKey']);
            $session = D('session')->findSessionBySession3key($sessionKey);
            if($session) {
                $openid = $session['openid'];
                $user = D('user')->getUserByOpenID($openid);
                if($user) {
                    //获取post的用户信息
                    $userInfo = json_decode($_POST['userInfo'],true);
                    $userInfo['gmt_modify'] = date('Y-m-d H:i:s',time());
                    //写入数据库
                    $updateUserInfo = D('User')->updateUserInfo($openid,$userInfo);
                    if($updateUserInfo) {
                        $this->ajaxReturn(array(
                            'status' => 1,
                            'message' => '用户信息新增成功！'
                        ));
                    }else {
                        $this->ajaxReturn(array(
                            'status' => 0,
                            'message' => '用户信息新增失败！'
                        ));
                    }
                }else {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '用户信息openid不存在！'
                    ));
                }

            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => 'session信息不存在！'
                ));
            }
        }else {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'POST信息不存在！'
            ));
        }
    }

    public function wxlogin() {
        //获取登录凭证（code）进而换取用户登录态信息
        $code = $_POST['code'];
        $openArray = $this->getOpenID($code);

        //生成3rd_session
        $sessionKey = createNonceStr();

        //以3rd_session为key，openid+session_id为值写入session
        $session = D('session')->findSessionByOpenID($openArray['openid']);
        if(!$session) {
            $sessionArray = array(
                'session3key' => $sessionKey,
                'openid' => $openArray['openid'],
                'session_key' => $openArray['session_key'],
                'unionid' => $openArray['unionid'],
                'create_time' => time(),
            );
            $saveSession = D('session')->addSession($sessionArray);
        }else {
            $sessionArray = array(
                'session3key' => $sessionKey,
                'session_key' => $openArray['session_key'],
                'unionid' => $openArray['unionid'],
                'create_time' => time(),
            );
            $updateSession = D('session')->updateSession($openArray['openid'],$sessionArray);
        }

        $user = D('user')->getUserByOpenID($openArray['openid']);
        if(!$user) {
            $openArray['gmt_create'] = date('Y-m-d H:i:s',time());
            $saveUserInfo = D('User')->saveUserInfo($openArray);
        }

        $this->ajaxReturn($sessionKey);
    }

    public function checkSession() {
        if($_POST['sessionKey']) {
            $sessionKey = json_decode($_POST['sessionKey']);
        }
        $session = D('session')->findSessionBySession3key($sessionKey);
        if($session) {
            $this->ajaxReturn(array(
                'status' => 1,
                'message' => 'session3key存在'
            ));
        }else {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'session3key不存在'
            ));
        }
    }

    public function getOpenID($code = '') {
        if(!$code || $code == '') {
            return 0;
        }
        $apiUrl = 'https://api.weixin.qq.com/sns/jscode2session?appid='.C('WECHAT_SMALL_APPLICATION')['APPID'].'&secret='.C('WECHAT_SMALL_APPLICATION')['APPSECRET'].'&js_code='.$code.'&grant_type=authorization_code';
        return json_decode(curlGet($apiUrl),true);
    }

}