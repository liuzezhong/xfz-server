<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/10/3
 * Time: 12:33
 */

namespace Sapp\Controller;


use MongoDB\Driver\Exception\ExecutionTimeoutException;
use Think\Controller;
use Think\Exception;

class GroupController extends Controller
{
    public function createGroup() {
        if($_POST['groupInfo'] && $_POST['sessionKey']) {
            $groupInfo = json_decode($_POST['groupInfo'],true);
            $sessionKey = json_decode($_POST['sessionKey'],true);

            $session = D('session')->findSessionBySession3key($sessionKey);
            if($session) {
                $openid = $session['openid'];
                $user = D('user')->getUserByOpenID($openid);
                if($user) {
                    $user_id = $user['user_id'];
                    $groupInfo['leader'] = $groupInfo['leader'] ? 1 : 0;
                    $groupInfo['sex'] = $groupInfo['sex'] ? 1 : 0;
                    $groupInfo['user_id'] = $user_id;
                    $groupInfo['create_time'] = time();
                    $groupInfo['update_time'] = time();

                    $addGroup = D('Group')->addGroup($groupInfo);
                    if($addGroup) {
                        // 将信息写入分组成员表
                        $memberArray = array(
                            'group_id' => $addGroup,
                            'user_id' => $user_id,
                            'invit_user_id' => 0,
                            'gmt_create' => date('Y-m-d H:i:s',time()),
                        );
                        $member = D('Member')->createMembers($memberArray);
                        if(!$member) {
                            $this->ajaxReturn(array(
                                'status' => '0',
                                'message' => '分组成员添加失败！',
                            ));
                        }
                        // 返回结果
                        $this->ajaxReturn(array(
                            'status' => 1,
                            'message' => '分组新建成功',
                            'group_id' => $addGroup,
                        ));
                    }else {
                        $this->ajaxReturn(array(
                            'status' => 0,
                            'message' => '分组新建失败',
                        ));
                    }

                }else {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '用户信息不存在',
                    ));
                }
            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => 'session信息不存在',
                ));
            }
        }else {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'post信息不存在',
            ));
        }
    }

    /**
     * 功能：根据ID号查找分组信息
     */
    public function findGroup() {
        if($_POST['group_id'] && $_POST['sessionKey']) {
            $sessionKey = json_decode($_POST['sessionKey'],true);
            $group_id = json_decode($_POST['group_id'],true);
            $isCreateUser = 0;
            $session = D('session')->findSessionBySession3key($sessionKey);
            if($session) {
                $openid = $session['openid'];
                $localUser = D('user')->getUserByOpenID($openid);
                if($localUser) {
                    $group = D('Group')->findGroupByGroupID($group_id);
                    if($group) {
                        $style = D('Style')->findStyleByStyleID($group['style_id']);
                        $group['style_name'] = $style['style_name'];

                        if($group['user_id'] == $localUser['user_id']) {
                            // 判断当前用户是否为创建者
                            $isCreateUser = 1;
                        }
                        // 查找报名用户信息
                        $members = D('Member')->listMemberByGroupIDLimit($group_id,6);
                        foreach ($members as $key => $item) {
                            $userInfo = D('User')->getUserInfoByUserID($item['user_id']);
                            $members[$key]['userInfo'] = $userInfo;
                        }

                        // 查找组队信息
                        $teamArray = D('Team')->listTeamByGroupID($group_id);
                        $newTeamArray = array();
                        if($teamArray) {
                            foreach ($teamArray as $key => $item) {
                                // 查找用户信息
                                $user = D('User')->getUserInfoByUserID($item['user_id']);
                                $newTeamArray[$item['team_name']][] = $user;
                            }
                        }
                        $this->ajaxReturn(array(
                            'status' => 1,
                            'message' => 'group信息查找成功',
                            'groupInfo' => $group,
                            'userInfo' => $localUser,
                            'members' => $members,
                            'teamArray' => $newTeamArray,
                            'isCreateUser' => $isCreateUser,
                        ));
                    }else {
                        $this->ajaxReturn(array(
                            'status' => 0,
                            'message' => 'group信息查找失败',
                        ));
                    }
                }else {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '用户信息不存在',
                    ));
                }
            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => 'session信息不存在',
                ));
            }
        }else {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'post信息不存在',
            ));
        }
    }

    public function findUserGroup() {
        if($_POST['sessionKey']) {
            $sessionKey = json_decode($_POST['sessionKey'],true);
            $session = D('session')->findSessionBySession3key($sessionKey);
            if($session) {
                $openid = $session['openid'];
                $user = D('user')->getUserByOpenID($openid);
                if($user) {
                    // 查看参加的活动
                    $memberArray = D('Member')->listMemberByUserID($user['user_id']);
                    $groupIDArray = array_unique(array_column($memberArray,'group_id'));
                    foreach ($groupIDArray as $key => $value) {
                        $group[] = D('Group')->findGroupByGroupID($value);
                    }
                    /*$group = D('Group')->listGroupByIDList($groupIDArray);*/
                    /*$group = D('Group')->selectGroupByUserID($user['user_id']);*/
                    if($group) {
                        foreach ($group as $key => $value) {
                            // 获取分组成员前9个人的用户ID
                            $nineUser = D('Member')->listUserLimitNine($value['group_id']);
                            $nineUserArray = array();
                            foreach($nineUser as $m => $n) {
                                $nineUserArray[] = D('User')->getUserInfoByUserID($n['user_id']);
                            }

                            // 获取分组成员数
                            $userNumber = D('Member')->countGroupUser($value['group_id']);
                            $style = D('Style')->findStyleByStyleID($value['style_id']);
                            $group[$key]['style_name'] = $style['style_name'];
                            $group[$key]['user_number'] = $userNumber;
                            $group[$key]['nineUser'] = $nineUserArray;
                        }
                        $this->ajaxReturn(array(
                            'status' => 1,
                            'message' => '用户的分组信息查找成功',
                            'groupList' => $group,
                        ));
                    }else {
                        $this->ajaxReturn(array(
                            'status' => 0,
                            'message' => '用户的分组信息查找失败',
                        ));
                    }
                }else {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '用户信息不存在',
                    ));
                }
            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => 'session信息不存在',
                ));
            }
        }else {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'post信息不存在',
            ));
        }
    }

    public function detailInfo() {

    }

    public function updateGroup() {
        if($_POST['groupInfo'] && $_POST['sessionKey'] && $_POST['group_id']) {
            $groupInfo = json_decode($_POST['groupInfo'],true);
            $sessionKey = json_decode($_POST['sessionKey'],true);
            $group_id = json_decode($_POST['group_id'],true);

            $session = D('session')->findSessionBySession3key($sessionKey);
            if($session) {
                $openid = $session['openid'];
                $user = D('user')->getUserByOpenID($openid);
                if($user) {
                    $groupInfo['leader'] = $groupInfo['leader'] ? 1 : 0;
                    $groupInfo['sex'] = $groupInfo['sex'] ? 1 : 0;
                    $groupInfo['update_time'] = time();

                    $updateGroup = D('Group')->updateGroup($group_id,$groupInfo);
                    if($updateGroup) {
                        $this->ajaxReturn(array(
                            'status' => 1,
                            'message' => '分组更新成功',
                            'group_id' => $updateGroup,
                        ));
                    }else {
                        $this->ajaxReturn(array(
                            'status' => 0,
                            'message' => '分组更新失败',
                        ));
                    }

                }else {
                    $this->ajaxReturn(array(
                        'status' => 0,
                        'message' => '用户信息不存在',
                    ));
                }
            }else {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => 'session信息不存在',
                ));
            }
        }else {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => 'post信息不存在',
            ));
        }
    }

    /**
     *对用户进行分组
     */
    public function checkGroup() {
        $group_id = I('post.group_id',0,'intval');
        if(!$group_id) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => '分组ID为空',
            ));
        }

        try {
            // 获取分组信息
            $group = D('Group')->findGroupByGroupID($group_id);
            if(!$group) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '分组信息不存在！',
                ));
            }
            // 获取该分组的所有用户信息
            $member = D('Member')->listMemberByGroupIDLimit($group_id);
            if(!$member) {
                $this->ajaxReturn(array(
                    'status' => 0,
                    'message' => '分组内用户信息不存在！',
                ));
            }
            // 用户ID列表
            $userArray = array_column($member,'user_id');
            // 进行分组
            $teamArray = $this->grouping($group,$userArray);

            $this->ajaxReturn(array(
                'status' => 1,
                'message' => '分组成功',
                'teamArray' => $teamArray,
            ));

        } catch (Exception $exception) {
            $this->ajaxReturn(array(
                'status' => 0,
                'message' => $exception->getMessage(),
            ));
        }
    }

    /**
     * 进行分组
     * @param $group
     * @param $userArray
     * @return array
     */
    private function grouping($group = array(), $userArray = array()) {
        // 分组ID
        $group_id = $group['group_id'];

        $teamArray = array();

        if($group['style_id'] == 0) {
            //随机分组
            $teamArray = $this->randomized($group,$userArray);
        }else if($group['style_id'] == 1) {
            //实力分组
            $teamArray = $this->strength($group,$userArray);
        }

        // 新分组写入数据库前删除老数据
        $deleteTeam = D('Team')->deleteTeamByGroupID($group_id);

        foreach ($teamArray as $key => $item) {
            foreach($item as $i => $j) {
                // 写入数据库
                $teamValue = array(
                    'group_id' => $group_id,
                    'team_name' => $key,
                    'user_id' => $j,
                    'gmt_create' => date('Y-m-d H:i:s',time()),
                );
                D('Team')->addTeam($teamValue);

                // 从数据库获取用户信息，保存到数组中
                $user = D('User')->getUserInfoByUserID($j);
                $teamArray[$key][$i] = $user;
            }
        }

        return $teamArray;
    }

    /**
     * 实力分组
     */
    private function strength($group = array(), $userArray = array()) {
        // 每组人数
        $number = $group['number'];
        //$userInfoArray = D('User')->listMemberUserInfo($userArray);
        $userInfoArray = array();
        foreach ($userArray as $key => $value) {
            $userInfoArray[] = D('User')->getUserInfoByUserID($value);
        }

        // 对用户战斗力进行冒泡排序
        $cnt = count($userInfoArray);
        for ($i = 0; $i < $cnt; $i++) {
            for ($j = 0; $j < $cnt - $i - 1; $j++) {
                if ($userInfoArray[$j]['flight_value'] < $userInfoArray[$j + 1]['flight_value']) {
                    $temp = $userInfoArray[$j];
                    $userInfoArray[$j] = $userInfoArray[$j + 1];
                    $userInfoArray[$j + 1] = $temp;
                }
            }
        }
        $userCntArray = array_column($userInfoArray,'user_id');

        //将一列数据根据每组人数
        $userRangeArray = array();
        $k = 0;
        if(count($userCntArray) <= $number) {
            $m = 1;
        }else {
            $m = count($userCntArray) / $number;
        }
        foreach ($userCntArray as $key => $item) {
            // 下一列
            if($key % $m == 0 && $key) {
                $k ++;
            }
            //回到第一列
            if($k >= $number) {
                $k = 0;
                $m = 1;
            }
            $userRangeArray[$k][] = $item;
        }

        // 判断是否区分男女
        if($group['sex'] == 1) {
            // 区分男女
            foreach ($userRangeArray as $key => $item) {
                $userInfoNewArray = array();
                // 获取用户信息
                foreach ($item as $m => $n) {
                    $userInfoNewArray[] = D('User')->getUserInfoByUserID($n);
                }

                // 将按男女将用户分为两个数组
                $userMArray = array();
                $userWArray = array();
                foreach ($userInfoNewArray as $m => $n) {
                    if($n['gender'] == 0 || $n['gender'] == 1) {
                        // 0未知，1男
                        $userMArray[] = $n['user_id'];
                    }else if($n['gender'] == 2) {
                        // 2女
                        $userWArray[] = $n['user_id'];
                    }
                }

                // 随机排序男女列表
                shuffle($userMArray);
                shuffle($userWArray);
                $randomUser = array();
                // 将两个数组合并为一个数组
                if(count($userMArray) > count($userWArray)) {
                    foreach ($userMArray as $m => $n) {
                        if($key % 2 != 0) {
                            $randomUser[] = $n;
                            if($m < count($userWArray)) {
                                $randomUser[] = $userWArray[$m];
                            }
                        }else if($key % 2 == 0) {
                            if($m < count($userWArray)) {
                                $randomUser[] = $userWArray[$m];
                            }
                            $randomUser[] = $n;
                        }
                    }
                }else {
                    foreach ($userWArray as $m => $n) {
                        if($key % 2 != 0) {
                            $randomUser[] = $n;
                            if($m < count($userMArray)) {
                                $randomUser[] = $userMArray[$m];
                            }
                        }else if($key % 2 == 0) {
                            if($m < count($userWArray)) {
                                $randomUser[] = $userWArray[$m];
                            }
                            $randomUser[] = $n;
                        }
                    }
                }
                $userRangeArray[$key] = $randomUser;
            }
        }else if($group['sex'] == 0) {
            // 不分男女
            // 每行随机一下
            foreach ($userRangeArray as $key => $item) {
                shuffle($userRangeArray[$key]);
            }
        }

        // 按列取出最终数据
        $teamArray = array();
        $teamIndex = C('TEAM_INDEX');
        $index = 0;
        foreach ($userRangeArray[0] as $key => $item) {
            $teamArray[$teamIndex[$index++]] = array_column($userRangeArray,$key);
        }
        return $teamArray;
    }

    /**
     * 随机分组
     */
    private function randomized($group = array(), $userArray = array()) {
        // 每组人数
        $number = $group['number'];
        $randomUser = array();
        if($group['sex'] == 1) {
            // 区分性别
            $userInfoArray = array();
            // 获取用户信息
            foreach ($userArray as $key => $value) {
                $userInfoArray[] = D('User')->getUserInfoByUserID($value);
            }
            // 将按男女将用户分为两个数组
            foreach ($userInfoArray as $key => $value) {
                if($value['gender'] == 0 || $value['gender'] == 1) {
                    // 0未知，1男
                    $userMArray[] = $value['user_id'];
                }else if($value['gender'] == 2) {
                    // 2女
                    $userWArray[] = $value['user_id'];
                }
            }
            // 随机排序男女列表
            shuffle($userMArray);
            shuffle($userWArray);

            // 将两个数组合并为一个数组
            if(count($userMArray) > count($userWArray)) {
                foreach ($userMArray as $key => $value) {
                    $randomUser[] = $value;
                    if($key < count($userWArray)) {
                        $randomUser[] = $userWArray[$key];
                    }
                }
            }else {
                foreach ($userWArray as $key => $value) {
                    $randomUser[] = $value;
                    if($key < count($userMArray)) {
                        $randomUser[] = $userMArray[$key];
                    }
                }
            }
        }else {
            // 不区分性别
            // 随机分组
            $randomUser = $userArray;
            shuffle($randomUser);
        }

        $teamArray = array();
        $teamIndex = C('TEAM_INDEX');
        $index = 0;
        foreach ($randomUser as $key => $item) {
            if($key % $number == 0 && $key) {
                $index++ ;
            }
            $teamArray[$teamIndex[$index]][] = $item;
        }
        return $teamArray;
    }
}