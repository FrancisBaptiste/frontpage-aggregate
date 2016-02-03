<?php

/**
 * HybridAuth
 * http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
 * (c) 2009-2015, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
 */
// ----------------------------------------------------------------------------------------
//	HybridAuth Config file: http://hybridauth.sourceforge.net/userguide/Configuration.html
// ----------------------------------------------------------------------------------------

return
		array(
			"base_url" => "http://localhost:8888/frontpagevancouver/includes/hybridauth/",
			"providers" => array(
				// openid providers
				"OpenID" => array(
					"enabled" => false
				),
				"Yahoo" => array(
					"enabled" => false,
					"keys" => array("key" => "", "secret" => ""),
				),
				"AOL" => array(
					"enabled" => false
				),
				"Google" => array(
					"enabled" => false,
					"keys" => array("id" => "", "secret" => ""),
				),
				"Facebook" => array(
					"enabled" => true,
					"keys" => array("id" => "738633746236394", "secret" => "f3dcb287f2bcfc1c23ab1cce7cfb5dc2"),
					'scope' => 'email, user_about_me, user_birthday, user_hometown, user_website, read_stream',
					"trustForwarded" => false
				),
				"Twitter" => array(
					"enabled" => true,
					"keys" => array("key" => "WlizqSplh6Rs8DTmkfrZAnaxj", "secret" => "om2vzVktXA6CLFkbVXntMFHl5VZySx4Ccke7bf1GFTA78op00V"),
					"includeEmail" => false
				),
				// windows live
				"Live" => array(
					"enabled" => false,
					"keys" => array("id" => "", "secret" => "")
				),
				"LinkedIn" => array(
					"enabled" => false,
					"keys" => array("key" => "", "secret" => "")
				),
				"Foursquare" => array(
					"enabled" => false,
					"keys" => array("id" => "", "secret" => "")
				),
			),
			// If you want to enable logging, set 'debug_mode' to true.
			// You can also set it to
			// - "error" To log only error messages. Useful in production
			// - "info" To log info and error messages (ignore debug messages)
			"debug_mode" => false,
			// Path to file writable by the web server. Required if 'debug_mode' is not false
			"debug_file" => "",
);
