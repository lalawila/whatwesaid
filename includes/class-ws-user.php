<?php
class WS_User {
    
    protected $user;
    private $_db;

    public function __construct( $user, $db = Null ) {
        $this->user = $user;
        if( isset($db) && $db instanceof WS_DB)
            $this->_db = $db;
        else
            $this->_db = $GLOBALS['wsdb']; 
    }

    public function __get ( $name ) {
            return $this->user->$name;   
    }
    
    public function pwd_verify( $pwd ) {
        return password_verify ( $pwd, $this->user->user_pwd); 
    
    }
    public function change_password( $new_password ){
        

    }

    public function send_letter( $to, $content ) {
    
    
    }

    public function get_status(  ) {


    }

    public function chang_avatar( ) {
    
    }
}


final class WS_UserManage {
    /**
     *$data['ID']
     *$data['user_login']
     *$data['user_pwd']
     *$data['nick_name']
     *$data['user_email']
     *$data['user_status']
    **/
    protected $loged_user = null;
    protected $page_user  = null;
    private $_db = null;
    private $has_logined = false;
    private $_is_user_page = false;
    private static $_single = null;

    private function __construct($db = null ) {

        global $splited_uri;

        if( isset($db) && $db instanceof WS_DB)
            $this->_db = $db;
        else
            $this->_db = $GLOBALS['wsdb']; 
        
        if( count($splited_uri) == 2 && $splited_uri[0] == "user" && is_numeric($splited_uri[1]) && $splited_uri[1] > 0 ) {
            $this->page_user = $this->get_user( [ "ID" => $splited_uri[1] ] );
            if( $this->page_user )
                $this->_is_user_page = true;
        }
    }
    public function __get( $name ) {
        return $this->$name;
    }
    public static function instance( $db = null ) {
        return isset(WS_UserManage::$_single) ? WS_UserManage::$_single : new WS_UserManage( $db );
    
    }
    
    public function is_user_page() {
        return $this->_is_user_page;
    }

    public function get_user($user = [ 'name' => '', 'email' => '', 'ID' => '']) {
        $u_data = $this->get_user_data($user);

        if(!$u_data ){
            WS_Error::add_error( __('This username is invalid.'));
            return false;
        }
        
        return new WS_User($u_data);
    }
    public function get_user_data($user = [ 'name' => '', 'email' => '', 'ID' => '']) {
        $u_data = false;
        if (!empty($user['name'])){
            $u_data = $this->_db->get_row( $this->_db->prepare(
			"SELECT * FROM users WHERE user_login = %s", $user['name']
		  ) );
        }
        elseif (!empty($user['email'])){
            $u_data = $this->_db->get_row( $this->_db->prepare(
			"SELECT * FROM users WHERE user_email = %s", $user['email']
		  ) );
        }
        elseif (!empty($user['ID'])){
            $u_data = $this->_db->get_row( $this->_db->prepare(
			"SELECT * FROM users WHERE ID = %s", $user['ID']
		  ) );
        }
        return $u_data;
    }
    public function check_login() {

        $this->has_logined = true;
        if (isset($this->loged_user))
            return $this->loged_user;
        if (isset($_COOKIE["logined"])){
            $l_name = strstr($_COOKIE["logined"],'|',true);
            if ($this->validate_username($l_name)){
                $user = $this->_db->get_row( $this->_db->prepare(
    			"SELECT * FROM users WHERE user_login = %s", $l_name
                ) );

                if ($user && password_verify ( $user->user_pwd, substr(strstr($_COOKIE["logined"],'|',false), 1))){
                    $this->loged_user =  new WS_User($user);
                    return $user;
                }
            }
        }
        $this->has_logined = false;
        return false;
    }
    public function login() {
        $user = false; 
        if (empty($_POST['log']) || empty($_POST['pwd']))
            return false;
            
        if ($this->is_email($_POST['log']))
            $new_user = $this->get_user( ['email'=>$_POST['log']]);
        else
            $new_user = $this->get_user( ['name'=>$_POST['log']] );
            
        if($new_user->pwd_verify( $_POST['pwd'] )){
            setcookie('logined',$new_user->user_login . '|' . password_hash($new_user->user_pwd, PASSWORD_DEFAULT ),time()+MONTH_IN_SECONDS,'/', "." . site_name );
            $user = $new_user;
            $this->loged_user = $new_user;
            $this->has_logined = true;
        }
        return $user;
    }
    public function logout() {
        global $user;
        if (isset($user)){

            unset($this->loged_user);

            unset($_COOKIE['logined']);
            setcookie('logined', null, -1, '/', site_name);
            $this->has_logined = false;

            return true;
        }
        return false;   
    }    
    public function get_current_user() {

        return $this->check_login();
    }
    public function username_exists( $name ) {
    	global $wsdb;
        $ID = $wsdb->get_row( $wsdb->prepare(
			"SELECT ID FROM users WHERE user_login = %s", $name
		  ), ARRAY_A );
        return $ID;
    }
    public function email_exists( $email ) {
    	global $wsdb;
        $ID = $wsdb->get_row( $wsdb->prepare(
			"SELECT ID FROM users WHERE user_email = %s", $email
		  ), ARRAY_A );
        return $ID;
    }
    public function register() {
        if (empty($_POST['email']) ||empty($_POST['name']) || empty($_POST['pwd']) )
            return false;
        $sanitized_user_login = $this->sanitize_user($_POST['name']);

        // Check the username
        if ($sanitized_user_login == '') {
            WS_Error::add_error(__('Please enter a username.'));
        } elseif (!$this->validate_username($_POST['name'])) {
            WS_Error::add_error( __('This username is invalid because it uses illegal characters. Please enter a valid username.'));
            $sanitized_user_login = '';
        } elseif ($this->username_exists($sanitized_user_login)) {
            WS_Error::add_error(__('This username is already registered. Please choose another one.'));

        }
        
        
        $user_email = $_POST['email'];
        // Check the email address
        if ($user_email == '') {
            WS_Error::add_error( __('Please type your email address.'));
        } elseif (!$this->is_email($user_email)) {
            WS_Error::add_error(__('The email address isn&#8217;t correct.'));
            $user_email = '';
        } elseif ($this->email_exists($user_email)) {
            WS_Error::add_error(__('This email is already registered, please choose another one.'));
        }
        
        
        
        $user_id = $this->create_user($sanitized_user_login, $_POST['pwd'], $user_email);
        if (!$user_id) {
            WS_Error::add_error('registerfail');
            return false;
        }


        return $user_id;
    }
    public function is_email ( $email ) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    public function create_user($username, $password, $email) {
        $user_login = addslashes($username);
        $user_email = addslashes($email);
        $user_pwd = $password;

        $userdata = compact('user_login', 'user_email', 'user_pwd');
        return $this->insert_user($userdata);
    }

    public function validate_username($username) {
        $sanitized = $this->sanitize_user($username, true);
        $valid = ($sanitized == $username && !empty($sanitized));

        return $valid;
    }

    public function insert_user($userdata) {
        global $wsdb;

        if ($userdata instanceof stdClass) {
            $userdata = get_object_vars($userdata);
        }

        // Are we updating or creating?
        if (!empty($userdata['ID'])) {
            $ID = (int)$userdata['ID'];
            $update = true;
            $old_user_data = $this->get_user_data(['ID' => $ID]);

            if (!$old_user_data) {
                WS_Error::add_error(__('Invalid user ID.'));
                return false;
            }

            // hashed in wp_update_user(), plaintext if called directly
            $user_pwd = !empty($userdata['user_pwd']) ? $userdata['user_pwd'] : $old_user_data->
                user_pwd;
        } else {
            $update = false;
            // Hash the password
            $user_pwd = password_hash($userdata['user_pwd'], PASSWORD_DEFAULT );
        }

        $sanitized_user_login = $this->sanitize_user($userdata['user_login'], true);
        $user_login = trim($sanitized_user_login);

        // user_login must be between 0 and 60 characters.
        if (empty($user_login)) {
            WS_Error::add_error(__('Cannot create a user with an empty login name.'));
            return false;
        } elseif (strlen($user_login) > 60) {
            WS_Error::add_error(__('Username may not be longer than 60 characters.'));
            return false;
        }

        if (!$update && $this->username_exists($user_login)) {
            WS_Error::add_error( __('Sorry, that username already exists!'));
            return false;
        }
  
        /*
        * If a nicename is provided, remove unsafe user characters before using it.
        * Otherwise build a nicename from the user_login.
        */
        if (!empty($userdata['nick_name'])) {
            $nick_name = $this->sanitize_user($userdata['nick_name'], true);
            if (mb_strlen($nick_name) > 50) {
                WS_Error::add_error(__('Nicename may not be longer than 50 characters.'));
                return false;
            }
        } else {
            $nick_name = mb_substr($user_login, 0, 50);
        }


        // Store values to save in user meta.
        $meta = array();

        $user_email = $userdata['user_email'];
        $raw_user_email = empty($userdata['user_email']) ? '' : $userdata['user_email'];

        /*
        * If there is no update, just check for `email_exists`. If there is an update,
        * check if current email and new email are the same, or not, and check `email_exists`
        * accordingly.
        */
        if ((!$update || (!empty($old_user_data) && 0 !== strcasecmp($userdata['user_email'], $old_user_data->
            user_email))) && $this->email_exists($userdata['user_email'])) {
            WS_Error::add_error('Sorry, that email address is already used!');
            return false;
        }
        $nickname = empty($userdata['nickname']) ? $user_login : $userdata['nickname'];

        $meta['nickname'] = $nickname;

        $compacted = compact('user_pwd', 'user_email', 'nick_name');

        if ($update) {
            if ($user_email !== $old_user_data->user_email) {
                $data['user_activation_key'] = '';
            }
            $wsdb->update($wpdb->users, $compacted, compact('ID'));
            $user_id = (int)$ID;
        } else {
            $wsdb->insert('users', $compacted + compact('user_login'));
            //$user_id = (int)$wpdb->insert_id;
        }
        $user_id = 1;
        return $user_id;
    }
    /**
     * Sanitizes a username, stripping out unsafe characters.
     *
     * Removes tags, octets, entities, and if strict is enabled, will only keep
     * alphanumeric, _, space, ., -, @. After sanitizing, it passes the username,
     * raw username (the username in the parameter), and the value of $strict as
     * parameters for the {@see 'sanitize_user'} filter.
     *
     * @param string $username The username to be sanitized.
     * @param bool   $strict   If set limits $username to specific characters. Default false.
     * @return string The sanitized username, after passing through filters.
     */
    public function sanitize_user($username, $strict = false) {
        $raw_username = $username;
        $username = $this->strip_all_tags($username);
        $username = remove_accents($username);
        // Kill octets
        $username = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '', $username);
        $username = preg_replace('/&.+?;/', '', $username); // Kill entities

        // If strict, reduce to ASCII for max portability.
        if ($strict)
            $username = preg_replace('|[^a-z0-9 _.\-@]|i', '', $username);

        $username = trim($username);
        // Consolidate contiguous whitespace
        $username = preg_replace('|\s+|', ' ', $username);

        return $username;
    }
    /**
     * Properly strip all HTML tags including script and style
     *
     * This differs from strip_tags() because it removes the contents of
     * the `<script>` and `<style>` tags. E.g. `strip_tags( '<script>something</script>' )`
     * will return 'something'. wp_strip_all_tags will return ''
     *
     *
     * @param string $string        String containing HTML tags
     * @param bool   $remove_breaks Optional. Whether to remove left over line breaks and white space chars
     * @return string The processed string.
     */
    public function strip_all_tags($string, $remove_breaks = false) {
        $string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
        $string = strip_tags($string);

        if ($remove_breaks)
            $string = preg_replace('/[\r\n\t ]+/', ' ', $string);

        return trim($string);
    }
    

}
