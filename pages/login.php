<?php
global $user;

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';
$redirect_to = isset($_REQUEST['redirect_to']) ? base64_decode($_REQUEST['redirect_to']) : '/';

@header_remove( 'Last-Modified' );
header("Cache-Control: no-cache");
header("Pragma: no-cache");
load_style();
//header('Content-Type: '.get_bloginfo('html_type').'; charset='.get_bloginfo('charset'));

?><body class="login-body"><div class="login"><?php
switch ($action) {
    case 'login' :
        WS_Error::reset();
        if($user->login()){
            header("Location: $redirect_to", true, '303');
            exit();
        }
        else
            WS_Error::print_error('<h2>', '</h2>');
        ?>
        <h1>Login</h1>
        <form name="loginform" id="loginform" action="/login?redirect_to=<?php echo base64_encode( $redirect_to )  ?>" method="post">
        <p class="group-inputs">
    		<input type="text" name="log" id="user_login" class="input bottom-line" value="<?php echo isset($_POST['log'])?$_POST['log']:'' ?>" size="20" placeholder="email or username">
    		<input type="password" name="pwd" id="user_pass" class="input" value="<?php echo isset($_POST['pwd'])?$_POST['pwd']:'' ?>" size="20" placeholder="password">
        </p>
    	<p class="submit">
    		<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="Log In">
    		<input type="hidden" name="testcookie" value="1">
    	</p>
        </form>
<?php
        break;
    case 'logout' :
	    if($user->logout()){
           header("Location: $redirect_to", true, '303');
        }
	    exit();
     case 'register' :
        WS_Error::reset();
        if($user->register()){
           header("Location: $redirect_to", true, '303');
           exit();
        }
        else
            WS_Error::print_error('<h2>', '</h2>');
        ?>
        <h1>Register</h1>
        <form name="loginform" id="loginform" action="/login?action=register" method="post">
            <p class="group-inputs">
        		<input type="text" name="email" id="user_email" class="input bottom-line" value="<?php echo isset($_POST['email'])?$_POST['email']:'' ?>" size="20" placeholder="email">
                <input type="text" name="name" id="user_name" class="input bottom-line" value="<?php echo isset($_POST['name'])?$_POST['name']:'' ?>" size="20" placeholder="username">
        		<input type="password" name="pwd" id="user_pass" class="input" value="<?php echo isset($_POST['pwd'])?$_POST['pwd']:'' ?>" size="20" placeholder="password">
            </p>
        	<p class="submit">
        		<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="Sign Up">
        		<input type="hidden" name="testcookie" value="1">
        	</p>
        </form>
        <?php
}
?>
</div>
</body>










