<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/10/31
 * Time: 23:07
 */

namespace Sapp\Controller;


use Think\Controller;
use Think\Exception;

class UserController extends Controller
{
    /**
     * 功能：根据用户ID获取用户信息
     */
    public function getUserInfo() {
        $user_id = I('post.user_id',0,'intval');
        $group_id = I('post.group_id',0,'intval');
        $sessionKey = json_decode($_POST['sessionKey'],true);
        $canHua = 0;
        if(!$user_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '用户ID为空!',
            ));
        }

        try {
            // 获取当前登录用户信息
            $session = D('session')->findSessionBySession3key($sessionKey);
            $openid = $session['openid'];
            $localUser = D('user')->getUserByOpenID($openid);

            // 获取分组信息
            $group = D('Group')->findGroupByGroupID($group_id);

            // 根据用户ID数据库查找用户信息
            $userInfo = D('User')->getUserInfoByUserID($user_id);

            if($localUser['user_id'] == $userInfo['user_id'] || $localUser['user_id'] == $group['user_id']) {
                // 当前登录用户打开了自己的页面  或者  当前登录用户是分组的创建者
                $canHua = 1;
            }

            if(!$userInfo) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '用户信息不存在!',
                ));
            }
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }

        $this->ajaxReturn(array(
            'status' => 1,
            'message' => '用户信息查找成功!',
            'userInfo' => $userInfo,
            'canHua' => $canHua,
        ));
    }

    /**
     * 功能：修改用户战斗力
     */
    public function changeFlightValue() {
        $user_id = I('post.user_id',0,'intval');
        $flight_value = I('post.flight_value',0,'intval');

        if(!$user_id || !$flight_value) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'user_id或flight_value参数有误！',
            ));
        }
        try {
            $res = D('User')->updateFlightValue($user_id,$flight_value);
            if(!$res) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '战斗力修改失败！',
                ));
            }
        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }

        $this->ajaxReturn(array(
            'status' => 1,
            'message' => '战斗力修改成功！',
        ));
    }
}