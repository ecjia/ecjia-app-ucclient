<?php 

namespace Royalcms\Component\Ucenter\Client;

use Royalcms\Component\Ucenter\Client\Traits\FunctionTrait;
use Royalcms\Component\Ucenter\Client\Traits\CompatibleTrait;
use Royalcms\Component\Ucenter\Utilities\Serialize;
use Royalcms\Component\Support\Traits\Macroable;

class Ucenter
{
    use FunctionTrait;
    use CompatibleTrait;
    use Macroable;
    
    protected $service;
    
    protected $config;

    public function __construct()
    {
        $this->config = new UcenterConfig();

        $this->service = new UcenterRequest($this->config);
    }

    /**
     * 获取Ucenter的配置项
     *
     * @return UcenterConfig
     */
    public function getUcenterConfig()
    {
        return $this->config;
    }
    
    /**
     * 用户注册
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $questionid
     * @param string $answer
     * @param string $regip
     */
    public function ucUserRegister($username, $password, $email, $questionid = '', $answer = '', $regip = '')
    {
        return $this->service->send('user', 'register',
            array(
                'username' => $username,
                'password' => $password,
                'email' => $email,
                'questionid' => $questionid,
                'answer' => $answer,
                'regip' => $regip
            )
        );
    }

    /**
     * 用户登录
     *
     * @param $username
     * @param $password
     * @param int $isuid
     * @param int $checkques
     * @param string $questionid
     * @param string $answer
     * @return array|\Royalcms\Component\Error\Facades\RC_Error|string
     */
    public function ucUserLogin($username, $password, $isuid = 0, $checkques = 0, $questionid = '', $answer = '')
    {
        $isuid = intval($isuid);
        $return = $this->service->send('user', 'login',
            array(
                'username' => $username,
                'password' => $password,
                'isuid' => $isuid,
                'checkques' => $checkques,
                'questionid' => $questionid,
                'answer' => $answer
            )
        );

        if (self::isXml($return)) {
            return Serialize::unserialize($return);
        } else {
            return \RC_Error::make('xml_parse_error', $return);
        }
    }
    
    /**
     * 用户同步登录
     *
     * @param integer $uid
     * @return string
     */
    public function ucUserSynlogin($uid)
    {
        $uid = intval($uid);
        return $this->service->send('user', 'synlogin', array('uid' => $uid));
    }
    
    /**
     * 用户同步退出
     *
     * @return string
     */
    public function ucUserSynlogout()
    {
        return $this->service->send('user', 'synlogout', array());
    }
    
    /**
     * 用户信息编辑
     *
     * @param string $username
     * @param string $oldpw
     * @param string $newpw
     * @param string $email
     * @param number $ignoreoldpw
     * @param string $questionid
     * @param string $answer
     */
    public function ucUserEdit($username, $oldpw, $newpw, $email, $ignoreoldpw = 0, $questionid = '', $answer = '')
    {
        return $this->service->send('user', 'edit',
            array(
                'username' => $username,
                'oldpw' => $oldpw,
                'newpw' => $newpw,
                'email' => $email,
                'ignoreoldpw' => $ignoreoldpw,
                'questionid' => $questionid,
                'answer' => $answer
            )
        );
    }
    
    /**
     * 删除指定的用户
     *
     * @param integer $uid
     */
    public function ucUserDelete($uid)
    {
        return $this->service->send('user', 'delete', array('uid' => $uid));
    }
    
    /**
     * 删除指定用户的头像
     *
     * @param integer $uid
     */
    public function ucUserDeleteAvatar($uid)
    {
        return $this->service->send('user', 'deleteavatar', array('uid' => $uid));
    }
    
    /**
     * 检查用户名是否存在
     *
     * @param string $username
     */
    public function ucUserCheckName($username)
    {
        return $this->service->send('user', 'check_username', array('username' => $username));
    }
    
    /**
     * 检查用户邮箱是否存在
     *
     * @param string $email
     */
    public function ucUserCheckEmail($email)
    {
        return $this->service->send('user', 'check_email', array('email' => $email));
    }

    /**
     * 获取用户信息
     *
     * @param $username
     * @param int $isuid
     * @return array|string
     */
    public function ucGetUser($username, $isuid = 0)
    {
        $return = $this->service->send('user', 'get_user',
            array(
                'username' => $username,
                'isuid' => $isuid
            )
        );

        return Serialize::unserialize($return);
    }
    
    /**
     * 用户合并
     *
     * @param string $oldusername
     * @param string $newusername
     * @param number $uid
     * @param string $password
     * @param string $email
     */
    public function ucUserMerge($oldusername, $newusername, $uid, $password, $email)
    {
        return $this->service->send('user', 'merge',
            array(
                'oldusername' => $oldusername,
                'newusername' => $newusername,
                'uid' => $uid,
                'password' => $password,
                'email' => $email
            )
        );
    }
    
    /**
     * 用户名合并，并移除
     *
     * @param string $username
     */
    public function ucUserMergeRemove($username)
    {
        return $this->service->send('user', 'merge_remove', array('username' => $username));
    }

    /**
     * 检测用户的头像是否存在
     *
     * @param $uid
     * @param string $size
     * @param string $type
     * @return array|int|\RC_Error
     */
    public function ucCheckAvatar($uid, $size = 'middle', $type = 'virtual')
    {
        $query = [
        	'uid'                   => $uid,
            'size'                  => $size,
            'type'                  => $type,
            'check_file_exists'     => 1
        ];
        $query = http_build_query($query);
        $url = UC_API . "/avatar.php?" . $query;
        $response = \RC_Http::remote_get($url);
        if (is_ecjia_error($response)) {
            return $response;
        }
        
        if ($response['body'] == 1) {
            return 1;
        } else {
            return 0;
        }
    }
    
    /**
     * 检查UCenter版本号信息
     *
     * @return string
     */
    public function ucCheckVersion()
    {
        $return = $this->service->send('version', 'check', array());
        $data = Serialize::unserialize($return);
        return is_array($data) ? $data : $return;
    }

}

// end