<?php
/**
 * Test Tool for Active Directory Integration
 *
 * @package Active Directory Integration
 * @author Christoph Steindorff
 * @since 0.9.9-dev
 */

if ( !defined('WP_LOAD_PATH') ) {

	/** classic root path if wp-content and plugins is below wp-config.php */
	$classic_root = dirname(dirname(dirname(dirname(__FILE__)))) . '/' ;
	
	if (file_exists( $classic_root . 'wp-load.php') )
		define( 'WP_LOAD_PATH', $classic_root);
	else
		if (file_exists( $path . 'wp-load.php') )
			define( 'WP_LOAD_PATH', $path);
		else
			exit("Could not find wp-load.php");
}

// let's load WordPress
require_once( WP_LOAD_PATH . 'wp-load.php');


// Let's do some security checks
 
// If the user is not logged in, die silently.
if(!$user_ID) {
	die();
}

// If the user is not an admin, die silently.
if (!current_user_can('level_10')) {
	die();
}

// If we have WordPress MU, die silently.
if ($wpmu_version != '') {
	die();
}



// Using our own debug handler for output
class TestADIntegrationPlugin extends ADAuthInt_Plugin {

	/**
	 * Output debug informations
	 * 
	 * @param integer level
	 * @param string $notice
	 */
	protected function _log($level = 0, $info = '') {
		if ($level <= $this->_loglevel) {
			$output = '<span class="';
			switch ($level) {
				case ADAI_LOG_DEBUG: 
					$output .= 'debug">[DEBUG]  ';
					break;
				case ADAI_LOG_INFO: 
					$output .= 'info">[INFO]   ';
					break;
				case ADAI_LOG_NOTICE: 
					$output .= 'NOTICE">[NOTICE] ';
					break;
				case ADAI_LOG_WARN: 
					$output .= 'warn">[WARN]   ';
					break;
				case ADAI_LOG_ERROR: 
					$output .= 'error">[ERROR]  ';
					break;
				case ADAI_LOG_FATAL: 
					$output .= 'fatal">[FATAL]  ';
						break;
				default:
					$output .= 'debug';
			}
			$output .= str_replace("\n","<br />         ",$info).'</span><br />';
			
			echo $output;
		}
	}
		
}

define('ADINTEGRATION_DEBUG', true);

if (class_exists('ADAuthInt_Plugin')) {
	$ADI = new TestADIntegrationPlugin();
} else {
	die('Plugin missing.');
}

$ADI->setLogLevel(ADAI_LOG_DEBUG);
/*$ADI->_set_masked_passwords_for_log( false );*/
?>
<html>
	<head>
		<title>ADI Test</title>
		<style type="text/css">
			h1 {
				font-size: 14pt;
			}
			#output {
				border: 1px solid #eee;
				background-color: #f9f9f9;
				font-family: consolas, courier, monospace;
				font-size: 10pt;
				white-space:pre; 
			}
			
			.debug {
				color: #606060;
			}
			
			.info {
				color: #000000;
			}
			
			.notice {
				color: #0000A0;
			}
			
			.warn {
				color: #f08000;
			}
			
			.error {
				color: #f04020;
			}
			
			.fatal  {
				color: #ff0000;
				font-weight: bold;
			}

		</style>
		
	</head>
	<body>
		<h1 style="font-size: 14pt">AD Integration Logon Test</h1>
<?php
if( isset( $_REQUEST['user'] ) && isset( $_REQUEST['password'] ) ) :
?>
<?php 
if (function_exists('ldap_connect')) {
	echo "openLDAP installed\n";
} else {
	die('openLDAP not installed');
}
?>
		<div id="output">
<?php 
$result = $ADI->authenticate(NULL, $_REQUEST['user'], $_REQUEST['password']);
?>
		</div>
<?php
if ($result) { 
	echo 'User logged on.';
} else {
	echo 'Logon failed';
}

?>
<?php
else :
?>
		<form method="post">
        	<label for="user">Username:</label> 
            <input type="text" name="user" id="user" placeholder="Username"/>
            <br/>
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" placeholder="Password"/>
            <br/>
            <input type="submit" value="Test Login" class="button"/>
        </form>
<?php
endif;
?>
	</body>
</html>
