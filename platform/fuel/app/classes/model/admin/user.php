<?php
class Model_Admin_User extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'admin_user';

    const SUPER_ADMINISTRATOR = 1;
    const ADMINISTRATOR = 2;
    const WHITE_LABEL_SUPER_ADMINISTRATOR = 3;
    const WHITE_LABEL_ADMINISTRATOR = 4;
    
    /**
     * Get possible values for admin user role id.
     *
     * @return int[]
     */
    public static function get_roles_keys(): array
    {
        return [
            self::SUPER_ADMINISTRATOR,
            self::ADMINISTRATOR,
            self::WHITE_LABEL_SUPER_ADMINISTRATOR,
            self::WHITE_LABEL_ADMINISTRATOR
        ];
    }

    /**
     *
     * @param string $login
     * @param string $password
     * @return Object|bool
     */
    public static function login_user($login, $password)
    {
        $res = [];

        $db = DB::select('admin_user.*', 'language.code')->from('admin_user')->where('username', $login)
            ->join('language')->on('admin_user.language_id', '=', 'language.id');
        $res = $db->execute();

        if (count($res) == 0) {
            return false;
        }

        $user = $res[0];
        $hash = Lotto_Security::generate_hash($password, $user['salt']);

        if ($user['hash'] !== $hash) {
            return false;
        }

        unset($user['hash']);
        unset($user['salt']);
        return $user;
    }

    /**
     *
     * @param array $user
     * @return string
     */
    public static function add_user($user)
    {
        $salt = Lotto_Security::generate_salt();
        $hash = Lotto_Security::generate_hash($user['password'], $salt);
        $id = null;

        $query = DB::insert('admin_user');

        $query->columns([
                'username',
                'name',
                'surname',
                'email',
                'language_id',
                'timezone',
                'salt',
                'hash',
                'role_id'
        ]);

        $query->values([
                $user['username'],
                $user['name'],
                $user['surname'],
                $user['email'],
                $user['language_id'],
                $user['timezone'],
                $salt,
                $hash,
                $user['role_id'],
        ]);

        $res = $query->execute();
        $id = $res[0];

        return $id;
    }

    /**
     *
     * @param array $updated_user
     * @return array
     */
    public static function update_user($id, $updated_user)
    {
        if (isset($updated_user['password'])) {
            $updated_user['salt'] = Lotto_Security::generate_salt();
            $updated_user['hash'] = Lotto_Security::generate_hash($updated_user['password'], $updated_user['salt']);
            unset($updated_user['password']);
            unset($updated_user['confirmPassword']);
        }

        $query = DB::update('admin_user');

        $query->set($updated_user);
        $query->where('id', '=', $id);

        $query->execute();

        return true;
    }

    /**
     *
     * @access public
     * @param int $id
     * @return object
     */
    public static function get_user($id)
    {
        $res = [];

        $db = DB::select('admin_user.*', 'language.code')->from('admin_user')->where('admin_user.id', $id)
            ->join('language')->on('admin_user.language_id', '=', 'language.id');
        $res = $db->execute();

        $user = $res[0];
        unset($user['salt']);
        unset($user['hash']);
        return (object)$user;
    }

    /**
     *
     * @access public
     * @return array
     */
    public static function get_all_users_short()
    {
        $res = [];

        /** @var object $db */
        $db = DB::select('id', 'username')->from('admin_user');
        $res = $db->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     * @return array
     */
    public static function get_regular_users_short()
    {
        $res = [];

        /** @var object $db */
        $db = DB::select('id', 'username')->from('admin_user')
            ->where('role_id', '<>', '1');
        $res = $db->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_sub_whitelabel_admin_users_short($whitelabel_id)
    {
        $res = [];

        $db = DB::select('admin_user.id', 'admin_user.username')->from('admin_user')
            ->where('admin_user.role_id', '=', self::WHITE_LABEL_ADMINISTRATOR)
            ->and_where('admin_whitelabel.whitelabel_id', '=', $whitelabel_id)
            ->join('admin_whitelabel')->on('admin_whitelabel.admin_user_id', '=', 'admin_user.id');
            
        /** @var object $db */
        $res = $db->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     * @param int $id
     * @return bool
     */
    public static function delete_user($id)
    {
        $db = DB::delete('admin_user')
            ->where('id', '=', $id)
            ->execute();

        return true;
    }
}
