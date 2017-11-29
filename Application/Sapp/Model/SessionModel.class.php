<?php
/**
 * Created by PhpStorm.
 * User: liuzezhong
 * Date: 2017/10/2
 * Time: 18:47
 */

namespace Sapp\Model;


use Think\Model;

class SessionModel extends Model
{
    private $_db = '';

    public function __construct()
    {
        $this->_db = M('session');
    }

    public function addSession($data = array()) {
        if(!$data || !is_array($data)) {
            throw_exception('SessionModel SessionModel data is null');
        }
        return $this->_db->add($data);
    }

    public function findSessionByOpenID($openid = '') {
        if(!$openid || $openid == '') {
            throw_exception('SessionModel findSessionByOpenID openid is null');
        }
        return $this->_db->where('openid = ' . "'".$openid."'")->find();
    }

    public function updateSession($openid = '',$data = array()) {
        if(!$openid || $openid == '') {
            throw_exception('SessionModel updateSession openid is null');
        }
        if(!$data || !is_array($data)) {
            throw_exception('SessionModel updateSession data is null');
        }
        return $this->_db->where('openid = ' . "'".$openid."'")->save($data);
    }

    public function findSessionBySession3key($session3key = '') {
        if(!$session3key || $session3key == '') {
            throw_exception('SessionModel findSessionBySession3key session3key is null');
        }
        return $this->_db->where('session3key = ' . "'" . $session3key . "'")->find();
    }
}