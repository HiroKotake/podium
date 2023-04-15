<?php
/**
 * AdminUserBean.php
 * 
 * @category  Admin 
 * @package   Bean 
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 *  
 */
namespace system\admin;

/**
 * AdminUserBean Class
 * 
 * @category Admin 
 * @package  Bean 
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class AdminUserBean
{
    /**
     * 管理者情報
     *
     * @param array $adminUser
     */
    private array $_adminUser = [
        'Id' => '',
        'Password' => '',
        'Category' => PWF_AUTH_CATEGORY_DEVEL,
        'Level' => PWF_AUTH_LEVEL_BOTTOM,
        'Profile' => [
            'Lid' => '',
            'Name' => '',
            'Mail' => '',
        ],
        'CreateDate' => '',
        'LapseDate' => '',
        'StopFlag' => PWF_ADMIN_LOGIN_OK,
    ];
    /**
     * データ未登録
     *
     * @var boolean
     */
    private bool $_initialed = false;

    /**
     * コンストラクタ
     *
     * @param string|array $adminUser
     */
    function __construct($adminUser = null)
    {
        if (!empty($adminUser)) {
            $this->setAdminUserInfo($adminUser);
        }
    }

    /**
     * Getter
     *
     * @param  string $name 変数名
     * @return mix 変数名に対応する値があった場合は、その格納している値を返す
     *             対応する変数名が無い場合は nullを返す
     */
    function __get(string $name)
    {
        if (array_key_exists($name, $this->_adminUser)) {
            return $this->_adminUser[$name];
        }
        if (array_key_exists($name, $this->_adminUser['Profile'])) {
            return $this->_adminUser['Profile'][$name];
        }
        return null;
    }

    /**
     * Setter
     *
     * @param string $name 変数名
     * @param mix $value 格納する値
     */
    function __set(string $name, $value)
    {
        if (array_key_exists($name, $this->_adminUser)) {
            $this->_adminUser[$name] = $value;
            return;
        }
        if (array_key_exists($name, $this->_adminUser['Profile'])) {
            $this->_adminUser['Profile'][$name] = $value;
            return;
        }
        // 対応するキーが無い場合
        $this->_adminUser[$name] = $value;
    }

    /**
     * 個人情報が空が確認
     *
     * @return boolean 空の場合は trueを、個人情報が設定される場合は falseを返す
     */
    public function isEmpty() : bool
    {
        return !$this->_initialed;
    }

    /**
     * 管理者情報の個人情報を設定する
     *
     * @param  string $name キー名
     * @param  mix $value 設定する値
     * @return void
     */
    public function setProfile(string $name, $value) 
    {
        $this->_adminUser['Profile'][$name] = $value;
    }

    /**
     * 管理者情報の個人情報を取得する
     *
     * @param string $name キー名
     * @return mix キー名に対応する値がある場合にはその値を、無い場合はnullを返す
     */
    public function getProfile(string $name)
    {
        if (array_key_exists($name, $this->_adminUser['Profile'])) {
            return $this->_adminUser['Profile'][$name];
        }
        return null;
    }
    /**
     * 管理者情報配列の原型を取得する
     *
     * @return array
     */
    public static function getPrototypeArray() : array
    {
        // スケルトンを返す
        return [
            'Id' => '',
            'Password' => '',
            'Category' => PWF_AUTH_CATEGORY_DEVEL,
            'Level' => PWF_AUTH_LEVEL_MASTER,
            'Profile' => [
                'Lid' => '',
                'Name' => '',
                'Mail' => '',
            ],
            'CreateDate' => '',
            'LapseDate' => 0,
            'StopFlag' => PWF_ADMIN_LOGIN_OK
        ];
    }

    /**
     * 空の管理者情報を設定する配列を取得する
     *
     * @return array 管理者情報配列
     */
    public function toArray() : array
    {
        return $this->_adminUser;
    }

    /**
     * 管理者情報をシリアライズして取得
     *
     * @return string シリアライズ文字列
     */
    public function toSerialize() : string
    {
        return serialize($this->_adminUser);
    }

    /**
     * Profileのみシリアライズかされた管理者情報配列を取得する
     *
     * @return array
     */
    public function toArrayWithSerializedProfile() : array
    {
        if (array_key_exists('Profile', $this->_adminUser) && is_array($this->_adminUser['Profile'])) {
            $temp = $this->_adminUser;
            $temp['Profile'] = serialize($temp['Profile']);
            return $temp;
        }
        return $this->_adminUser;
    }

    /**
     * 管理者情報がシリアライズ化されている場合に値を設定
     *
     * @param  array $data 管理者情報を含む配列
     * @return boolean 値を設定できた場合は trueを、失敗した場合は falseを返す
     */
    private function _setSerializedData(string $data) : bool
    {
        $temp = unserialize($data);
        if (!is_array($temp)) {
            return false;
        }
        $this->_adminUser = $temp;
        $this->_initialed = true;
        return true;
    }

    /**
     * 管理者情報が配列の場合に値を設定
     *
     * @param  array $data 管理者情報を含む配列
     * @return boolean 値を設定できた場合は trueを、失敗した場合は falseを返す
     */
    private function _setPartSerializedData(array $data) : bool
    {

        if (array_key_exists('Profile', $data) && is_string($data['Profile'])) {
            $temp = unserialize($data['Profile']);
            if (!is_array($temp)) {
                return false;
            }
            $data['Profile'] =  $temp;
        }
        $this->_adminUser = $data;
        $this->_initialed = true;
        return true;
    }

    /**
     * 管理者情報を設定する
     *
     * @param  array|string $adminUser　管理者情報
     * @return boolean 値を設定できた場合は trueを、失敗した場合は falseを返す
     */
    public function setAdminUserInfo($adminUser) : bool
    {
        if (is_string($adminUser)) {
            return $this->_setSerializedData($adminUser);
        }
        if (is_array($adminUser)) {
            return $this->_setPartSerializedData($adminUser);
        }
        return false;
    }

}