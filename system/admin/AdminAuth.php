<?php
/**
 * AdminAuth.php
 * 
 * @category  Admin 
 * @package   Api
 * @author    Takahiro Kotake <podium@teleios.jp>
 * @license   MIT
 * @version   1.0.0
 * @copyright 2022 Teleios
 *  
 */
namespace system\admin;

use \Exception;
use system\admin\AdminUserBean;
use system\session\Session;
use system\supports\LogWriter;

/**
 * AdminAuth Class
 * 管理者関連クラス
 * [機能]
 *  登録
 *  認証
 *  権限停止
 *  削除
 *  権限確認
 *  カテゴリ・レベル設定・変更、
 * 
 * [格納先]
 *  file, sqlite3, database
 * 
 * [管理者として格納するデータ]
 *      'Id' -> 管理者ID ... 管理者ページログインID (ハッシュ化したID)
 *      'Password' -> 管理者パスワード ... 管理者ページログインパスワード
 *      'Category' -> 管理カテゴリ ... 管理者のカテゴリ developer:開発者、manager:運用者
 *      'Level' -> 権限レベル ... 利用出来る権限レベル
 *      'Profile' -> 個人情報 [ ... 管理者の個人情報
 *          'Id' => ログインID
 *          'Name' -> 氏名
 *          'Mail' -> メールアドレス
 *          (その他)
 *      ]
 *      'CreateDate' -> 登録日 ... 登録した日時
 *      'LapseDate' -> 失効日 ... 失効する日時。この日付を超えるとログイン不可とする (通常は未設定とする)
 *      'StopFlag' -> 有効フラグ ... 対象のログインの可否(true:ログイン不可,false:ログイン可能)
 * 
 * [設定ファイル]
 *  admin/config/Administrator.php
 * 
 * @category Admin 
 * @package  Api
 * @author   Takahiro Kotake <podium@teleios.jp>
 */
class AdminAuth
{
    const AUTH_SKIP_FILE = PWF_ADMIN_PATH . 'config/AuthSkip.php';
    /**
     * 認証管理設定
     *
     * @var array
     */
    protected array $config = [];
    /**
     * データ格納先
     *
     * @var integer
     */
    protected int $type = STRAGE_TYPE_FILE;
    /**
     * ストレージクラス格納先
     *
     * @var Object ストレージクラス
     */
    public $strage = null;
    /**
     * ログインユーザ
     *
     * @var AdminUserBean
     */
    protected AdminUserBean $currentUser;
    /**
     * 参照ユーザ
     *
     * @var array
     */
    protected array $refferenceUser = [];
    /**
     * 初期ユーザ無効化
     *
     * @var boolean
     */
    protected bool $initialUserAnnul = false;
    /**
     * エラー発生時のメッセージ
     *
     * @var string
     */
    protected string $message = '';
    /**
     * 登録ユーザ数
     *
     * @var integer
     */
    protected int $allUserNumber = 0;

    /**
     * コンストラクタ
     */
    function __construct()
    {
        $this->initialize();    
        if (PWF_ADMIN_LOG_ON) {
            LogWriter::open(LogWriter::LOG_ADMIN);
        }
    }

    function __destruct()
    {
        if (PWF_ADMIN_LOG_ON) {
            LogWriter::close(LogWriter::LOG_ADMIN);
        }
    }

    /**
     * エラーが発生した場合のメッセージを取得する
     *
     * @return string エラーが発生している場合はエラーメッセージを、発生していない場合は空文字を返す
     */
    public function getMessage() : string
    {
        return $this->message;
    }

    /**
     * スーパーユーザのデータを作成する
     *
     * @return AdminUserBean
     */
    protected function makeSuperUser() : AdminUserBean 
    {
        $userInfo = AdminUserBean::getPrototypeArray();
        $userInfo['Id'] = $this->_makeHashedUserId($this->config['InitialAdmin']);
        $userInfo['Password'] = password_hash($this->config['InitialAdminPwd'], PASSWORD_DEFAULT);
        $userInfo['Category'] = PWF_AUTH_CATEGORY_DEVEL;
        $userInfo['Level'] = PWF_AUTH_LEVEL_MASTER;
        $userInfo['Profile']['Lid'] = $this->config['InitialAdmin'];
        $userInfo['Profile']['Name'] = $this->config['InitialAdmin'];
        return new AdminUserBean($userInfo);
    }

    /**
     * 初期化（設定ファイル読み込み)
     *
     * @return void
     * @throws Exception 設定ファイルが存在しない場合に例外を発生
     */
    protected function initialize()
    {
        $configFile = PWF_ADMIN_PATH . 'config/Administrator.php';
        if (!file_exists($configFile)) {
            throw new Exception('Administrator Configure File Not Found !!');
        }
        include_once $configFile;
        $this->config = $administrator;
        $this->type = $this->config['Strage']['type'];
        if ($this->type == STRAGE_TYPE_FILE) {
            $this->strage = new AdminAuthFile($this->config['Strage']);
        } else {
            $this->strage = new AdminAuthDB($this->config['Strage']);
        }
        // 初期ユーザストップ確認
        if (file_exists(self::AUTH_SKIP_FILE)) {
            include_once self::AUTH_SKIP_FILE;
            $this->initialUserAnnul = $initailUserAnnul;
        } else {
            // 登録ユーザがあるかを確認
            if ($this->strage->isInitialized()) {
                // 登録ユーザあるならばスキップファイルを作成
                $this->makeAnnulInitial();
                $this->initialUserAnnul = true;
            }
        }
    }

    /**
     * 初期ユーザ無効化ファイル
     *
     * @return boolean
     */
    protected function makeAnnulInitial() : bool
    {
        // 既存の設定ファイルがある場合は削除する
        if (file_exists(self::AUTH_SKIP_FILE)) {
            unlink(self::AUTH_SKIP_FILE);
        }
        try {
            $hFile = fopen(self::AUTH_SKIP_FILE, 'a+');
            fwrite($hFile, '<?php' . PHP_EOL);
            fwrite($hFile, '$initailUserAnnul = true;' . PHP_EOL);
            fclose($hFile);
        } catch (Exception $e) {
            $this->message = $e->getMessage();
            return false;
        }
        return true;
    }

    /**
     * ユーザIDをハッシュ化する
     *
     * @param  string $userId
     * @return string
     */
    private function _makeHashedUserId(string $userId) : string
    {
        $hashedId = sha1($userId);
        return $hashedId;
    }

    /**
     * ID重複チェック
     *
     * @param  string $userId ユーザID
     * @return boolean 重複していない場合に trueを返し、重複した場合は falseを返す
     */
    public function checkDuplicate(string $userId) : bool
    {
        return $this->strage->checkDuplicate($this->_makeHashedUserId($userId));
    }

    /**
     * ログインしたユーザの情報を取得する
     *
     * @return AdminUserBean ログイン済みの場合は管理者情報を返し、それ以外は falseを返す
     */
    public function getCurrentUser() // : AdminUserBean
    {
        if (empty($this->currentUser)) {
            return false;
        }
        return $this->currentUser;
    }

    /**
     * ストレージから指定したユーザ情報を取得する
     *
     * @param  string $userId 管理者ユーザID
     * @param  boolean $refference リファレンスに格納 ((省略時)true: 格納する、false:格納しない)
     *                 falseを指定した場合はカレントユーザとして格納される。
     * @return AdminUserBean|boolean 情報取得に成功したら管理者情報を取得する、失敗した場合はfalseを返す
     */
    public function getUserInfo(string $userId, bool $refference = true) // : AdminUserBean
    {
        if ($refference) {
            $this->refferenceUser[$userId] 
                = $this->strage->getUserInfo($this->_makeHashedUserId($userId));
            return $this->refferenceUser[$userId];
        }
        $this->currentUser 
            = $this->strage->getUserInfo($this->_makeHashedUserId($userId));
        return $this->currentUser; 
    }

    /**
     * ストレージから指定したユーザ情報を取得する
     *
     * @param string $hashedId ハッシュ化したユーザID
     * @return AdminUserBean|boolean 情報取得に成功したら管理者情報を取得する、失敗した場合はfalseを返す
     */
    public function getUserInfoHID(string $hashedId)
    {
        return $this->strage->getUserInfo($hashedId);
    }

    /**
     * 管理者ユーザを一括で取得する
     *
     * @param integer $offset 開始オフセット。省略時は先頭からとなる。
     * @param integer $length 取得件数。省略時は全件取得となる
     * @return array
     */
    public function getAllUserInfos(int $offset = 0, int $length = 0) : array
    {
        $users = $this->strage->getAllUser();
        $this->allUserNumber = count($users);
        usort($users, function($a, $b){
            return $a->CreateDate <=> $b->CreateDate;
        });
        if ($length > 0) {
            return array_slice($users, $offset, $length);
        }
        return $users;
    }

    /**
     * 登録ユーザ数を取得する
     * (注意)現状はgetAllUserInfos()を実行後に、正確な値が取得できる様になっている。
     *
     * @return integer
     */
    public function getUsersCount() : int
    {
        return $this->allUserNumber;
    }

    /**
     * 参照済みユーザ設定情報を取得する
     * 参照する情報が無い場合は、ストレージから取得し格納する
     *
     * @param  string $userId 管理者ユーザID
     * @return AdminUserBean 管理者情報
     */
    private function _getTargetUserInfo(string $userId) // : AdminUserBean
    {
        if (!empty($this->currentUser) && array_key_exists($userId, $this->currentUser)) {
            return $this->currentUser;
        }
        if (!empty($this->refferenceUser) && array_key_exists($userId, $this->refferenceUser)) {
            return $this->refferenceUser[$userId];
        }
        $userInfo = $this->getUserInfo($userId);
        if (empty($userInfo)) {
            $this->message = $this->strage->getMessage();
            return false;
        }
        $this->refferenceUser[$userId] = $userInfo;
        return $userInfo;
    }

    /**
     * ログイン時に管理者のカテゴリとレベルおよびその他をセッションに設定
     *
     * @param  string $hashedId
     * @param  integer $category
     * @param  integer $level
     * @return void
     */
    private function _setLoginSession(string $userId, int $category, int $level)
    {
        Session::set('Id', $userId);
        Session::set('Category', $category);
        Session::set('Level', $level);
        Session::set('Login', true);
        Session::set('LoginExpire', time() + $this->config['LoginExpire']);
    }

    /**
     * 管理者認証を実施するか
     *
     * @return boolean 認証を実施する場合は trueを返す。認証を実施しない場合は falseを返す
     */
    public function withoutAuthLogin() : bool
    {
        if ($this->config['Authenticate']) {
            return true;
        }
        // スーパーユーザを設定する
        $this->currentUser = $this->makeSuperUser();
        $this->_setLoginSession($this->currentUser->Lid, PWF_AUTH_CATEGORY_DEVEL, PWF_AUTH_LEVEL_MASTER);
        return false;
    }

    /**
     * ログイン有効時間を超えているか確認
     *
     * @return boolean 超えているのであれば trueを、超えていないのであれば falseを返す
     */
    public function checkLoginExpire() : bool
    {
        $expireTime = Session::get('LoginExpire');
        $now = time();
        return $expireTime < $now ? true : false;
    }

    /**
     * ログイン状態にあるか確認
     * (注意)ページ遷移後は現在のログインユーザ情報は初期化される。
     *      しかし、セッション内部にログイン状態が格納されているので、この関数を用いそれを確認する。
     *      ログイン状態にあれば、必要な情報を設定し、trueを返す
     *      未ログイン状態であれば falseを返すので、再ログインさせる必要がある。
     * 
     * @return boolean ログインしているならば trueを返し、未ログインならばfalseを返す
     */
    public function isLogined()
    {
        $uid = Session::get('Id');
        $logined = Session::get('Login');
        if (!$logined) {
            return false;
        }
        if ($this->checkLoginExpire()) {
            return false;
        }
        // 認証を実施しない場合は、スーパーユーザを設定
        if (!$this->config['Authenticate']) {
            $this->currentUser = $this->makeSuperUser();
            return true;
        }
        // カレントユーザにセッションからIDを取得し、ユーザデータをセット
        $user = $this->_getTargetUserInfo($uid);
        if (!$user) {
            // ユーザ不明なのでエラー
            return false;
        }
        $this->currentUser = $user;
        return true;
    }

    /**
     * 管理者ログイン認証を行う
     *
     * @param  string $userId 管理者ユーザID
     * @param  string $password パスワード
     * @return boolean 認証できた場合は trueを、認証不可の場合はfalseを返す
     */
    public function login(string $userId, string $password) : bool
    {
        // 認証を実施しない
        if (!$this->config['Authenticate']) {
            // Default User
            $this->currentUser = $this->makeSuperUser();
            return true;
        }
        // 初期ユーザの確認
        if (!$this->initialUserAnnul) {
            if ($this->config['InitialAdmin'] == $userId) {
                if ($this->config['InitialAdminPwd'] != $password) {
                    return false;
                }
                // Default User
                $this->currentUser = $this->makeSuperUser();
                $this->_setLoginSession($userId, $this->currentUser->Category, $this->currentUser->Level);
                return true;
            }
        }
        // ストレージに格納されている場合
        $userInfo = $this->getUserInfo($userId);
        if ((is_bool($userInfo) && !$userInfo) || $userInfo->isEmpty()) {
            // データが取れない場合エラー
            $this->message = 'No Exist User!!';
            return false;
        }
        // ログイン停止か確認
        if ($userInfo->StopFlag == PWF_ADMIN_LOGIN_NG) {
            $this->message = 'Stopping Login !!';
            return false;
        }
        // 失効日を超えているか？
        $expireTime = strtotime($userInfo->LapseDate);
        if ($expireTime > 0) {
            $now = time();
            if ($expireTime < $now) {
                $this->message = 'User Right was Lapse !!';
                return false;
            }
        }
        // 対象ユーザが存在したのでカレントユーザとして設定
        $this->currentUser = $userInfo;
        // 対象ユーザのパスワード確認
        $authResult = password_verify($password, $userInfo->Password);
        if ($authResult) {
            // $this->currentUser = $userInfo;
            $this->_setLoginSession($userId, $userInfo->Category, $userInfo->Level);
        } else {
            $this->message = 'Password is not match !!';
        }
        return $authResult;
    }

    /**
     * ログアウト
     *
     * @return void
     */
    public function logout()
    {
        Session::set('Login', false);
        Session::set('Id', '');
    }

    /**
     * 管理者を登録する
     *
     * @param  string $userId 管理者ユーザID
     * @param  string $password パスワード
     * @param  array $data その他データ(連想配列)
     * @return boolean 登録に成功した場合は trueを、失敗した場合は falseを返す
     */
    public function regist(string $userId, string $password, AdminUserBean $adminInfo) : bool
    {
        if (!$this->checkDuplicate($userId)) {
            $this->message = 'UserID is Deplicated !!';
            return false;
        }
        // ToDo: 以下の連想配列を AdminUserBeanに変更すること
        $adminInfo->Id = $this->_makeHashedUserId($userId);
        $adminInfo->Password = password_hash($password, PASSWORD_DEFAULT);
        $adminInfo->setProfile(PWF_ADMIN_PROFILE_LID, $userId);
        $adminInfo->CreateDate = date('Y/m/d H:i:s');
        $adminInfo->LapseDate = date('Y/m/d H:i:s', time() + (60*60*24*365));
        $result = $this->strage->regist($adminInfo);
        if (!$result) {
            $this->message = $this->strage->getMessage();
            return false;
        }
        return true;
    }

    /**
     * 管理者情報を更新する
     *
     * @param AdminUserBean $adminInfo 管理者情報
     * @return boolean 更新に成功した場合は trueを、失敗した場合は falseを返す
     */
    public function update($adminInfo) : bool
    {
        if ((is_bool($adminInfo) && !$adminInfo) || $adminInfo->isEmpty()) {
            $this->message = $this->strage->getMessage();
            return false;
        }
        $result = $this->strage->update($adminInfo);
        if (!$result) {
            $this->message = $this->strage->getMessage();
            return false;
        }
        return true;
    }

    /**
     * パスワードを変更する
     *
     * @param  string $userId 管理者ユーザID
     * @param  string $oldPassword 旧パスワード
     * @param  string $newPassword 新パスワード
     * @return boolean 更新に成功した場合は trueを、失敗した場合は falseを返す
     */
    public function changePassword(string $userId, string $newPassword, string $oldPassword) : bool
    {
        $userInfo = $this->_getTargetUserInfo($userId);
        if (!password_verify($oldPassword, $userInfo->Password)) {
            $this->message = 'Password is unmatch !!';
            return false;
        }
        $userInfo->Password = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userInfo);
    }

    /**
     * 強制的にパスワードを変更する
     *
     * @param  string $userId 管理者ユーザID
     * @param  string $newPassword 新パスワード
     * @return boolean 更新に成功した場合は trueを、失敗した場合は falseを返す
     */
    public function forceChangePassword(string $userId, string $newPassword) : bool
    {
        $userInfo = $this->_getTargetUserInfo($userId);
        $userInfo->Password = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($userInfo);
    }

    /**
     * 権限レベルを変更する
     *
     * @param  string $userId 管理者ユーザID
     * @param integer $level 新しい権限レベル
     * @return boolean 更新に成功した場合は trueを、失敗した場合は falseを返す
     */
    public function changeLevel(string $userId, int $level) : bool
    {
        $userInfo = $this->_getTargetUserInfo($userId);
        $userInfo->Level = $level;
        return $this->update($userInfo);
    }

    /**
     * プロファイルを変更する
     *
     * @param  string $userId 管理者ユーザID
     * @param  array $profile プロファイルに含むデータの連想配列
     * @return boolean
     */
    public function changeProfile(string $userId, array $profile) : bool
    {
        $userInfo = $this->_getTargetUserInfo($userId);
        $userInfo->Profile = $profile;
        return $this->update($userInfo);
    }

    /**
     * 失効日を設定する
     *
     * @param  string $userId 管理者ユーザID
     * @param  string $datetime 失効する日時(YYYY-mm-dd HH:ii:ss)
     * @return boolean 更新に成功した場合は trueを、失敗した場合は falseを返す
     */
    public function expel(string $userId, string $datetime) : bool
    {
        $userInfo = $this->_getTargetUserInfo($userId);
        $userInfo->LapseDate = $datetime;
        return $this->update($userInfo);
    }

    /**
     * 権限を停止
     *
     * @param  string $userId 管理者ユーザID
     * @return boolean 更新に成功した場合は trueを、失敗した場合は falseを返す
     */
    public function authStop(string $userId) : bool
    {
        $userInfo = $this->_getTargetUserInfo($userId);
        $userInfo->StopFlag = PWF_ADMIN_LOGIN_NG;
        return $this->update($userInfo);
    }

    /**
     * 権限を再開
     *
     * @param  string $userId 管理者ユーザID
     * @return boolean 更新に成功した場合は trueを、失敗した場合は falseを返す
     */
    public function authStart(string $userId) : bool
    {
        $userInfo = $this->_getTargetUserInfo($userId);
        $userInfo->StopFlag = PWF_ADMIN_LOGIN_OK;
        return $this->update($userInfo);
    }

    /**
     * ログインユーザの権限を確認する
     *
     * @param integer $needCategory 要求管理カテゴリ
     * @param integer $needLevel 要求権利レベル
     * @return boolean 有効な権限を持つ場合は trueを、持たない場合は falseを返す
     */
    public function checkAuth(int $needCategory, int $needLevel) : bool
    {
        if (empty($this->currentUser)) {
            // ページ遷移後はcurrentUserが空になっているので
            // セッション情報からカテゴリとレベルを持っていくる (こっちがメインで動作する)
            $category = Session::get('Category');
            $level = Session::get('Level');
            if (!empty($category) && !empty($level)) {
                if ($category == $needCategory && $level >= $needLevel) {
                    return true;
                }
            }
            return false;
        }
        // ログイン直後でページ遷移していないならこちらでチェック
        $userInfo = $this->getCurrentUser();
        if ($userInfo->Category == $needCategory && $userInfo->Level >= $needLevel) {
            return true;
        }
        return false;
    }

    /**
     * 指定したユーザの権限を確認する
     *
     * @param  string $userId 管理者ユーザID
     * @param  integer $needCategory 要求管理カテゴリ
     * @param  integer $needLevel 要求権利レベル
     * @return boolean 有効な権限を持つ場合は trueを、持たない場合は falseを返す
     */
    public function checkUserAuth(string $userId, int $needCategory, int $needLevel) : bool
    {
        $userInfo = $this->_getTargetUserInfo($userId);
        if (empty($userInfo)) {
            return false;
        }
        if ($userInfo->Category == $needCategory && $userInfo->Level >= $needLevel) {
            return true;
        }
        return false;
    }
}