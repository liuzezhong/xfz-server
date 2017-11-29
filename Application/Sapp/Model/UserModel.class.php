<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/10/1
 * Time: 23:03
 */
namespace Sapp\Model;

class UserModel extends \Think\Model
{
    private $_db = '';

    public function __construct()
    {
        $this->_db = M('user');
    }

    public function saveUserInfo($data = array()) {

        if(!$data || !is_array($data)) {
            throw_exception('UserModel saveUserInfo data is null');
        }
        return $this->_db->add($data);
    }

    public function getUserByOpenID($openid = '') {
        if($openid == '' || !$openid) {
            throw_exception('UserModel getUserByOpenID openid is null');
        }
        return $this->_db->where('openid = ' . '"' . $openid . '"')->find();
    }

    public function updateUserInfo($openid = '',$data = array()) {
        if($openid == '' || !$openid) {
            throw_exception('UserModel updateUserInfo openid is null');
        }
        if(!$data || !is_array($data)) {
            throw_exception('UserModel updateUserInfo data is null');
        }
        return $this->_db->where('openid = ' . '"' . $openid . '"')->save($data);
    }

    /**
     * 功能：根据多个用户ID批量查找多个用户信息
     * @param array $userIdArray
     * @return mixed
     */
    public function listMemberUserInfo($userIdArray = array()) {
        if(!$userIdArray || !is_array($userIdArray)) {
            throw_exception('UserModel listMemberUserInfo userIdArray is null');
        }
        $condition['user_id'] = array('IN',$userIdArray);
        return $this->_db->where($condition)->select();

    }

    /**
     * 功能：根据用户ID查找用户信息
     * @param int $user_id
     * @return mixed
     */
    public function getUserInfoByUserID($user_id = 0) {
        if(!$user_id) {
            throw_exception('UserModel getUserInfoByUserID user_id is null');
        }
        $condition['user_id'] = $user_id;
        return $this->_db->where($condition)->find();
    }

    /**
     * 功能：修改用户战斗力
     * @param int $user_id
     * @param int $flight_value
     * @return bool
     */
    public function updateFlightValue($user_id = 0, $flight_value =0) {
        if(!$user_id || !$flight_value) {
            throw_exception('UserModel updateFlightValue user_id or flight_value is null');
        }
        $condition['user_id'] = $user_id;
        $flightValue['flight_value'] = $flight_value;
        return $this->_db->where($condition)->save($flightValue);
    }

}