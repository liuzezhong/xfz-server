<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/10/29
 * Time: 22:42
 */

namespace Sapp\Controller;


use Think\Controller;
use Think\Exception;

class MemberController extends Controller
{
    public function createGroupMembers() {
        // 判断post值是否为空
        if($_POST['sessionKey'] && $_POST['group_id']) {
            // 获取session和groupid信息
            $sessionKey = json_decode($_POST['sessionKey'],true);
            $group_id = json_decode($_POST['group_id'],true);
            $invit_user_id = json_decode($_POST['invit_user_id'],true);
            // 通过sessinKey数据库中查找session信息
            $session = D('session')->findSessionBySession3key($sessionKey);
            if($session) {
                // 获取openid并换取user信息
                $openid = $session['openid'];
                $user = D('user')->getUserByOpenID($openid);
                if(!$user) {
                    $this->ajaxReturn(array(
                        'status' => '0',
                        'message' => 'user信息不存在'
                    ));
                }
                // 将用户信息和分组信息写入menmber表中（加入分组）
                try {
                    $memberArray = array(
                        'group_id' => $group_id,
                        'user_id' => $user['user_id'],
                        'invit_user_id' => $invit_user_id,
                        'gmt_create' => date('Y-m-d H:i:s',time()),
                    );
                    $member = D('Member')->createMembers($memberArray);
                    if(!$member) {
                        $this->ajaxReturn(array(
                            'status' => '0',
                            'message' => '分组成员添加失败！',
                        ));
                    } else {
                        $this->ajaxReturn(array(
                            'status' => '1',
                            'message' => '分组成员添加成功！',
                        ));
                    }
                } catch (Exception $exception) {
                    $this->ajaxReturn(array(
                        'status' => '0',
                        'message' => $exception->getMessage(),
                    ));
                }
            } else {
                $this->ajaxReturn(array(
                    'status' => '0',
                    'message' => 'session信息不存在'
                ));
            }
        } else {
            $this->ajaxReturn(array(
                'status' => '0',
                'message' => 'post信息不存在'
            ));
        }
    }

    /**
     * 功能：根据分组ID查找分组成员信息
     */
    public function listMemberOfGroup() {
        // 获取分组ID
        $group_id = $_POST['group_id'];
        if(!$group_id) {
            $this->ajaxReturn(array(
                'status' => '0',
                'message' => 'post信息不存在'
            ));
        }
        // 查找分组成员信息
        $members = D('Member')->listMemberByGroupIDLimit($group_id);
        if(!$members) {
            $this->ajaxReturn(array(
                'status' => '0',
                'message' => '没有加入分组的用户信息',
                'group_id' => $group_id,
            ));
        }
        foreach ($members as $key => $value) {
            if($value['invit_user_id']) {
                $invitUserInfo = D('User')->getUserInfoByUserID($value['invit_user_id']);
                $members[$key]['invit_user_name'] = $invitUserInfo['nickname'];
            }
            $userInfo = D('User')->getUserInfoByUserID($value['user_id']);
            $members[$key]['userInfo'] = $userInfo;
        }
        /*// 取出所有分组成员id号
        $memberUserID = array_column($members,'user_id');
        // 根据多个ID批量查找成员信息
        $memberUserInfo = D('User')->listMemberUserInfo($memberUserID);
        if(!$memberUserInfo) {
            $this->ajaxReturn(array(
                'status' => '0',
                'message' => '用户信息不存在'
            ));
        }*/
        // 返回结果
        $this->ajaxReturn(array(
            'status' => '1',
            'message' => '加入分组用户信息成功',
//            'memberUserInfo' => $memberUserInfo,
            'members' => $members,
            'countMembers' => count($members),
        ));

    }
}