<?php
class WS_User {
    /**
     *$data['ID']
     *$data['user_login']
     *$data['user_pass']
     *$data['nick_name']
     *$data['user_email']
     *$data['user_status']
    **/
    public $data;
    private function __construct($user_data) {
        $this->data = $user_data;
    }
    public static function instance($user = [ 'name' => '', 'email' => '', 'pwd' => '']) {
        global $wsdb;
        $u_data = WS_User::get_user_data($user);

        if(!$u_data ){
            WS_Error::add_error( __('This username is invalid.'));
            return false;
        }
        
        if(!password_verify($user['pwd'], $u_data['user_pass'])){
            WS_Error::add_error( __('This password is error.'));
            return false;
        }
        return new WS_User($u_data);
    }
    public static function get_user_data($user = [ 'name' => '', 'email' => '', 'ID' => '']) {
        global $wsdb;
        $u_data = false;
        if (!empty($user['name'])){
            $u_data = $wsdb->get_row( $wsdb->prepare(
			"SELECT * FROM users WHERE user_login = %s", $user['name']
		  ), ARRAY_A );
        }
        elseif (!empty($user['email'])){
            $u_data = $wsdb->get_row( $wsdb->prepare(
			"SELECT * FROM users WHERE user_email = %s", $user['email']
		  ), ARRAY_A );
        }
        elseif (!empty($user['ID'])){
            $u_data = $wsdb->get_row( $wsdb->prepare(
			"SELECT * FROM users WHERE ID = %s", $user['ID']
		  ), ARRAY_A );
        }
        return $u_data;
    }
    public static function check_login() {
        global $user;
        global $wsdb;

        if (isset($user))
            return $user;

        if (isset($_COOKIE["logined"])){
            $l_name = strstr($_COOKIE["logined"],'|',true);
            if (WS_User::validate_username($l_name)){
                $u_data = $wsdb->get_row( $wsdb->prepare(
    			"SELECT * FROM users WHERE user_login = %s", $l_name
                ), ARRAY_A );

                if ($u_data && password_verify ($u_data['user_pass'], substr(strstr($_COOKIE["logined"],'|',false), 1))){
                    $user =  new WS_User($u_data);
                    return $user;
                }
            }
        }
        return false;
    }
    public static function login() {
        global $user;
        
        if (empty($_POST['log']) || empty($_POST['pwd']))
            return false;
            
        if (WS_User::is_email($_POST['log']))
            $new_user = WS_User::instance(['email'=>$_POST['log'],'pwd' => $_POST['pwd']]);
        else
            $new_user = WS_User::instance(['name'=>$_POST['log'],'pwd' => $_POST['pwd']]);
            
        if($new_user){
            setcookie('logined',$new_user->data['user_login'] . '|' . password_hash($new_user->data['user_pass'], PASSWORD_DEFAULT ),time()+MONTH_IN_SECONDS,'/','whatwesaid.xyz' );
            $user = $new_user;
        }
        return $user;
    }
    public static function logout() {
        global $user;
        if (isset($user)){

            unset($GLOBALS['user']);

            unset($_COOKIE['logined']);
            setcookie('logined', null, -1, '/', 'whatwesaid.xyz');
            
            return true;
        }
        return false;   
    }    
    public static function get_current_user() {

        return WS_User::check_login();
    }
    public static function username_exists( $name ) {
    	global $wsdb;
        $ID = $wsdb->get_row( $wsdb->prepare(
			"SELECT ID FROM users WHERE user_login = %s", $name
		  ), ARRAY_A );
        return $ID;
    }
    public static function email_exists( $email ) {
    	global $wsdb;
        $ID = $wsdb->get_row( $wsdb->prepare(
			"SELECT ID FROM users WHERE user_email = %s", $email
		  ), ARRAY_A );
        return $ID;
    }
    public static function register() {
        if (empty($_POST['email']) ||empty($_POST['name']) || empty($_POST['pwd']) )
            return false;
        $sanitized_user_login = WS_User::sanitize_user($_POST['name']);

        // Check the username
        if ($sanitized_user_login == '') {
            WS_Error::add_error(__('Please enter a username.'));
        } elseif (!WS_User::validate_username($_POST['name'])) {
            WS_Error::add_error( __('This username is invalid because it uses illegal characters. Please enter a valid username.'));
            $sanitized_user_login = '';
        } elseif (WS_User::username_exists($sanitized_user_login)) {
            WS_Error::add_error(__('This username is already registered. Please choose another one.'));

        }
        
        
        $user_email = $_POST['email'];
        // Check the email address
        if ($user_email == '') {
            WS_Error::add_error( __('Please type your email address.'));
        } elseif (!WS_User::is_email($user_email)) {
            WS_Error::add_error(__('The email address isn&#8217;t correct.'));
            $user_email = '';
        } elseif (WS_User::email_exists($user_email)) {
            WS_Error::add_error(__('This email is already registered, please choose another one.'));
        }
        
        
        
        $user_id = WS_User::create_user($sanitized_user_login, $_POST['pwd'], $user_email);
        if (!$user_id) {
            WS_Error::add_error('registerfail');
            return false;
        }


        return $user_id;
    }
    public static function is_email ( $email ) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    public static function create_user($username, $password, $email) {
        $user_login = addslashes($username);
        $user_email = addslashes($email);
        $user_pass = $password;

        $userdata = compact('user_login', 'user_email', 'user_pass');
        return WS_User::insert_user($userdata);
    }

    public static function validate_username($username) {
        $sanitized = WS_User::sanitize_user($username, true);
        $valid = ($sanitized == $username && !empty($sanitized));

        return $valid;
    }

    public static function insert_user($userdata) {
        global $wsdb;

        if ($userdata instanceof stdClass) {
            $userdata = get_object_vars($userdata);
        }

        // Are we updating or creating?
        if (!empty($userdata['ID'])) {
            $ID = (int)$userdata['ID'];
            $update = true;
            $old_user_data = WS_User::get_user_data(['ID' => $ID]);

            if (!$old_user_data) {
                WS_Error::add_error(__('Invalid user ID.'));
                return false;
            }

            // hashed in wp_update_user(), plaintext if called directly
            $user_pass = !empty($userdata['user_pass']) ? $userdata['user_pass'] : $old_user_data->
                user_pass;
        } else {
            $update = false;
            // Hash the password
            $user_pass = password_hash($userdata['user_pass'], PASSWORD_DEFAULT );
        }

        $sanitized_user_login = WS_User::sanitize_user($userdata['user_login'], true);
        $user_login = trim($sanitized_user_login);

        // user_login must be between 0 and 60 characters.
        if (empty($user_login)) {
            WS_Error::add_error(__('Cannot create a user with an empty login name.'));
            return false;
        } elseif (strlen($user_login) > 60) {
            WS_Error::add_error(__('Username may not be longer than 60 characters.'));
            return false;
        }

        if (!$update && WS_User::username_exists($user_login)) {
            WS_Error::add_error( __('Sorry, that username already exists!'));
            return false;
        }
  
        /*
        * If a nicename is provided, remove unsafe user characters before using it.
        * Otherwise build a nicename from the user_login.
        */
        if (!empty($userdata['nick_name'])) {
            $nick_name = WS_User::sanitize_user($userdata['nick_name'], true);
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
            user_email))) && WS_User::email_exists($userdata['user_email'])) {
            WS_Error::add_error('Sorry, that email address is already used!');
            return false;
        }
        $nickname = empty($userdata['nickname']) ? $user_login : $userdata['nickname'];

        $meta['nickname'] = $nickname;

        $compacted = compact('user_pass', 'user_email', 'nick_name');

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
    public static function sanitize_user($username, $strict = false) {
        $raw_username = $username;
        $username = WS_User::strip_all_tags($username);
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
    public static function strip_all_tags($string, $remove_breaks = false) {
        $string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
        $string = strip_tags($string);

        if ($remove_breaks)
            $string = preg_replace('/[\r\n\t ]+/', ' ', $string);

        return trim($string);
    }
    

}
