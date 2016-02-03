<?php
session_start();

$homeURL = 'http://localhost:8888/frontpagevancouver';

include("includes/connection.php");

// if page requested by submitting login form
if( isset( $_REQUEST["email"] ) && isset( $_REQUEST["password"] ) )
{
	$user_exist = get_user_by_email_and_password( $_REQUEST["email"], $_REQUEST["password"] );

	// user exist?
	if( $user_exist )
	{
		// set the user as connected and redirect him to a home page or something
		//$_SESSION["user_connected"] = true;

		header("Location: http://www.example.com/user/home.php");
	}

	// wrong email or password?
	else
	{
		// redirect him to an error page
		header("Location: http://www.example.com/login-error.php");
	}
}

// else, if login page request by clicking a provider button
//SOCIAL SIGN IN LOGIC
elseif( isset( $_REQUEST["provider"] ) )
{
	// the selected provider
	$provider_name = $_REQUEST["provider"];


	try
	{
		// inlcude HybridAuth library
		// change the following paths if necessary
		$config   = 'includes/hybridauth/config.php';
		require_once( "includes/hybridauth/Hybrid/Auth.php" );

		// initialize Hybrid_Auth class with the config file
		$hybridauth = new Hybrid_Auth( $config );

		// try to authenticate with the selected provider
		$adapter = $hybridauth->authenticate( $provider_name );

		// then grab the user profile
		$user_profile = $adapter->getUserProfile();
	}

	// something went wrong?
	catch( Exception $e )
	{
		echo $config;
		echo "<br/>";
		echo "$e";
		//header("Location: $homeURL/login-error.php");
	}

	// check if the current user already have authenticated using this provider before
	$user_exist = get_user_by_provider_and_id( $provider_name, $user_profile->identifier );

	// if the user didn't authenticate using the selected provider before
	// we create a new entry on database.users for him
	if( ! $user_exist ){
		create_new_hybridauth_user(
			$user_profile->email,
			$user_profile->firstName,
			$user_profile->lastName,
			$provider_name,
			$user_profile->identifier
		);

		$_SESSION['user_id'] = $connection->insert_id;

	}else{
		//if this user already exists, just get the id and set it
		$provider_uid = $user_profile->identifier;
		$result = $connection->query( "SELECT * FROM users WHERE hybridauth_provider_uid='$provider_uid' ");
		while( $row = $result->fetch_assoc() ){
			$_SESSION['user_id'] = $row['id'];
		}
	}

	// set the user as connected and redirect him
	//$_SESSION["user_connected"] = true;


	if(isset($_SESSION['user_id'])){

		//if($_SESSION['login_status'] != "NA"){
			//if recent article is set,
			if(isset($_SESSION['recent_page'])){
				header("Location: " . $_SESSION['recent_page']);
			}else{
				header("Location: $homeURL/index.php");
			}
		//}

		/*
		else{
			echo "Log in failed. Try again later.";
			$message = "Login failed for provider $provider_name, $user_profile->identifier ";
			mail("fran.baptiste@gmail.com", "Frontpage login failed", $message);
		}
		*/

	}else{
		echo "Log in failed. Try again later.";
		$message = "Login failed for provider $provider_name, $user_profile->identifier ";
		mail("fran.baptiste@gmail.com", "Frontpage login failed", $message);
	}


}



/*
* -------------------------- utility functions
**/


/*
* We need this function cause I'm lazy
**/
function mysqli_query_excute( $sql )
{
	global $connection;

	$result = mysqli_query( $connection, $sql );

	if(  ! $result )
	{
		die( printf( "Error: %s\n", mysqli_error( $connection ) ) );
	}

	return $result->fetch_object();
}

/*
* get the user data from database by email and password
**/
function get_user_by_email_and_password( $email, $password )
{
	return mysqli_query_excute( "SELECT * FROM users WHERE email = '$email' AND password = '$password'" );
}

/*
* get the user data from database by provider name and provider user id
**/
function get_user_by_provider_and_id( $provider_name, $provider_user_id )
{
	return mysqli_query_excute( "SELECT * FROM users WHERE hybridauth_provider_name = '$provider_name' AND hybridauth_provider_uid = '$provider_user_id'" );
}

/*
* get the user data from database by provider name and provider user id
**/
function create_new_hybridauth_user( $email, $first_name, $last_name, $provider_name, $provider_user_id )
{
	// let generate a random password for the user
	$password = md5( str_shuffle( "0123456789abcdefghijklmnoABCDEFGHIJ" ) );

	mysqli_query_excute(
		"INSERT INTO users
		(
			username,
			password,
			email,
			hybridauth_provider_name,
			hybridauth_provider_uid
		)
		VALUES
		(
			'$first_name $last_name',
			'$password',
			'$email',
			'$provider_name',
			'$provider_user_id'
		)"
	);

	#$_SESSION["user_id"] = mysqli_insert_id($connection);

}


?>