<?php
/*
  Plugin Name: Z-Vote
  Plugin URI: -
  Description: Vote-system for WordPress.
  Version: 1.1
  Author: -
  Author -
  License: GPL 
*/

// --- DEFINITIONS

	//define where zvote is installed on the wordpres system. In 99.9% of the case the path below is correct.
	define('zVotePath', '/wp-content/plugins/zvote/');


// --- PUBLIC SECTION
// --- Functions and hooks that will intercept public function on the blog.

	//superglobal vote hook, if the url contains zvote it will break and register that vote before loading the page
	if ($_GET['zvote']) {
		$zVoteVoted = zVote_checkVote($_GET['zvote']);
	}

	//below content hook
	add_filter( 'the_posts', 'zVote_showButton', 1);
	

// --- ADMIN SECTION

	//hook to see if the user just installed the plugin
	register_activation_hook(__FILE__,'zVote_install');

	// vote main-page - shows entries that can have votes, number of votes and the option to reset votes for a entry.
	function zvote_votes() {
		
		//plugin header, always the same regardless of function.
		echo "
		<div class=\"wrap\">
		<div id=\"icon-plugins\" class=\"icon32\"><br /></div>
		<h2>Z-Vote - WordPress voting system</h2>";
		
		//main switch to deside what to do/show on the adminpage
		switch ($_GET['do']) {
			
			case 'resetvotes':
				//resets votes for a specific entry
				$zRemoveVotes = zVote_resetVotes($_GET['postid']);
				
				//get postdata
				$post = get_post($_GET['postid']);
				
				//return HTML
				echo '
					<h3>Votes for entry <i>' . $post->post_title . '</i> has been reset.</h3>
				
				
					<!-- back button -->
					<br />
					<form id="zvote-go-back" method="post" action="/wp-admin/plugins.php?page=zvote/zvote.php"> 
						<input type="submit" id="plugin-search-input" name="back" value="Go Back" class="button" /> 
					</form> 
				';
				
			break;
			
			case 'showvotes':
				//grab specific entry from the database and show votes
				$zEntries = zVote_getEntry($_GET['postid']);
				
				//get postdata
				$post = get_post($_GET['postid']);
				// votes HTML									
				echo '
				
				<h3>Showing votes for entry <i>' . $post->post_title . '</i></h3>
				
				<table class="widefat" cellspacing="0" id="all-plugins-table"> 
					<thead> 
						<tr> 
							<th scope="col" class="manage-column">Voted</th> 
							<th scope="col" class="manage-column">Voter IP</th> 
							<th scope="col" class="manage-column">Voter UserID</th>
						</tr> 
					</thead> 
			 
					<tfoot> 
						<tr>
							<th scope="col" class="manage-column">Voted</th> 
							<th scope="col" class="manage-column">Voter IP</th> 
							<th scope="col" class="manage-column">Voter UserID</th>
						</tr> 
					</tfoot>
					';
					
					foreach ($zEntries as $zEntry) {
						//display each entry
						echo '
							<tr>			
								<td>' . date('l jS \of F Y h:i:s A', $zEntry->time) . '</td> 
								<td>' . $zEntry->userip . '</td> 
								<td>' . $zEntry->userid . '</td> 
							</tr>
						';
					} //en entry loop
					
					echo '	
				</table>
				
				
				<!-- back button -->
				<br />
				<form id="zvote-go-back" method="post" action="/wp-admin/plugins.php?page=zvote/zvote.php"> 
					<input type="submit" id="plugin-search-input" name="back" value="Go Back" class="button" /> 
				</form> 
				
				'; //end votes HTML	
			break;
		
			default:
			
				//grab all entries from the database
				$zEntries = zVote_getEntries();
				
				// votes HTML									
				echo '
				
				<script type="text/javascript">
				<!--
				function confirmReset() {
					var answer = confirm("Are you sure you wish to reset this Entry?")
					if (answer){
						return true;
					}	else {
						return false;
					}
				}
				//-->
				</script>
				
				<h3 class="title">Options</h3>

				<table class="form-table">
					<tr valign="top"> 
					<th scope="row">Voting restrictions:</th>
						<td valign="top">
							<input type=radio value="left" name="zvote_restriction" checked>IP Address &nbsp; <input type=radio value="right" name="zvote_restriction" disabled  /> IP Address and UserID</b> 
							<p class="help" style="font-size:11px;">Z-Vote support either IP based restriction or IP and UserID based restriction. UserID require the user to login to this WordPress installation to be able to vote.</p>
						</td>
					</tr>
											
				</table>
				
				<h3 class="title">Entries</h3>
				
				<table class="widefat" cellspacing="0" id="all-plugins-table"> 
					<thead> 
						<tr> 
							<th scope="col" class="manage-column">Entries</th> 
							<th scope="col" class="manage-column">Votes</th>
							<th scope="col" class="manage-column">Reset Votes</th> 
						</tr> 
					</thead> 
			 
					<tfoot> 
						<tr>
							<th scope="col" class="manage-column">Entry Title</th> 
							<th scope="col" class="manage-column">Votes</th>
							<th scope="col" class="manage-column">Reset Votes</th> 
						</tr> 
					</tfoot>
					';
					
					foreach ($zEntries as $zEntry) {
						//display each entry
						echo '
							<tr>			
								<td width="70%"><a href="/wp-admin/plugins.php?page=zvote/zvote.php&do=showvotes&postid=' . $zEntry->ID . '">' . $zEntry->post_title . '</a></td> 
								<td width="15%" align="left">' . $zEntry->zvotes . '</td> 
								<td width="15%" align="center">
								
									<form id="zvote-reset-votes" method="post" action="/wp-admin/plugins.php?page=zvote/zvote.php&do=resetvotes&postid=' . $zEntry->ID . '"> 
										<input type="submit" id="plugin-search-input" name="search" onclick="return confirmReset();" value="Reset Votes" class="button" /> 
									</form> 
								
								</td> 
							</tr>
						';
					} //en entry loop
					
					echo '	
				</table>
				'; //end votes HTML	
			
			break;
		}
		
		
	
		
	}


// --- MENU ENTRY POINTS

//add the ActVote-menu to the AdmSystem-menu
function addZVoteSubMenu() {
    add_submenu_page('plugins.php', 'Z-Vote', 'Z-Vote', 10, __FILE__, 'zvote_votes'); 
}
add_action('admin_menu', 'addZVoteSubMenu');


// --- ZVOTE FUNCTIONS
// --- Unique functions to zVote

//zvote install function, setup database and tables for zVote called zvotedata
//this database will contain all the zVotes across the WordPress installation.
function zVote_install () {
   global $wpdb;

   $table_name = $wpdb->prefix . "zvotedata";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      $sql = "
				CREATE TABLE " . $table_name . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				time bigint(11) DEFAULT '0' NOT NULL,
				postid bigint(11) NOT NULL,
				userip varchar(255) NOT NULL,
				userid bigint(11) NOT NULL,
				UNIQUE KEY id (id)
			);";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
   }
}

//get all zVote entries
function zVote_getEntries() {
	
	//get post from wordpress
	$posts = get_posts();
	
	//loop through all the post objects
	foreach( $posts as $post ){
	
		//for each object get an array of applicable categories
		$cats = get_the_category( $post->ID );
		
		//loop through all the categories applicable to the post
		$add = false;
		foreach( $cats as $cat) {
			//if the post is assigned to our contest-entry category then add it
			if ( $cat->name == 'contest-entry' ) {
				$add = true;
			}
		}
		
		if ($add) {
			$post->zvotes = zVote_countVotes($post->ID);
			$new_posts[] = $post;
		}
	}
	
	//return our entries
	return $new_posts;
}

//get votes for a unique entry
function zVote_getEntry($postid) {
	global $wpdb;

	$entries = $wpdb->get_results($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "zvotedata WHERE postid = " . $postid . ""));
		
	return $entries;
}

//function to count votes for a unique entry/post
function zVote_countVotes($postid) {
	
	global $wpdb;
	
	$votes = 0;
	$votes = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM " . $wpdb->prefix . "zvotedata WHERE postid = " . $postid . ""));
	
	return $votes;
}

//function to reset votes for a unique entry/post
function zVote_resetVotes($postid) {
	
	global $wpdb;
	
	$votes = $wpdb->get_var($wpdb->prepare("DELETE FROM " . $wpdb->prefix . "zvotedata WHERE postid = " . $postid . ""));
	
	return $votes;
}

//function to show vote-button
function zVote_showButton($posts) {
		
	//create an array to hold the posts we want to show including our buttons (if any)
	$new_posts = array();
	
	//loop through all the post objects
	foreach( $posts as $post ){
	
		//if the post is assigned to our contest-entry category then do button injection
		if ( in_category('contest-entry', $post->ID) ) {

			//inject buttons

			//remove previous zvote hooks
			if ($_GET['zvote']) {
				$_SERVER['REQUEST_URI'] = preg_replace('/zvote=[0-9]+/', '',$_SERVER['REQUEST_URI']);
			}

			//decide which kind of injection to use, if ? is in the string we use &, if not we go with ?
			if (strpos($_SERVER['REQUEST_URI'],'?')) {				
				$injectionPoint = $_SERVER['REQUEST_URI'] . '&';
			} else {
				$injectionPoint = $_SERVER['REQUEST_URI'] . '?';
			}
			
			
			//show zvote data (voted, not voted)
			if ($_GET['zvoters'] == 1) {
				$votedRs = '<span style="color:green;font-size:10px;">Vote registered, <br />thanks for voting!<br /></span>';
			}
			
			if ($_GET['zvoters'] == 2) {
				$votedRs = '<span style="color:red;font-size:10px;">You may only vote once<br /> for the same contestant!<br /></span>';
			}
				
			$post->post_content = $post->post_content . '<div style="float:right; padding:0px; margin:0px;">' . $votedRs . '<a href="' . $injectionPoint . 'zvote=' . $post->ID . '"><img src="' . '/wp-content/plugins/zvote/thumbup.png" border="0" style="float:right;"></a></div>';

			$new_posts[] = $post;
		} else {
			//normal entry, just return
			$new_posts[] = $post;
		}

	}

	//send the new post array back to be used by WordPress
	return $new_posts;
}


//register a vote 
function zVote_registerVote($postid) {
	global $wpdb;
	
	//ipcheck for now, will expand to userid-check, based on the user setting in version 1.5
	$ipcheck = $wpdb->get_var($wpdb->prepare("SELECT id FROM " . $wpdb->prefix . "zvotedata WHERE postid = " . $postid . " AND userip = \"" . $_SERVER['REMOTE_ADDR'] . "\""));
	
	$wpdb->insert( $wpdb->prefix . 'zvotedata', array( 'postid' => $postid, 'userip' => $_SERVER['REMOTE_ADDR'], 'userid' => 0, 'time' => time() ), array( '%d','%s', '%d', '%d' ) );
	
	return true;
}

//check if we should register the vote or not.
function zVote_checkVote($postid) {
	global $wpdb, $wp_query, $redirect_meta_key;
	
	//ipcheck for now, will expand to userid-check, based on the user setting in version 1.5
	$ipcheck = $wpdb->get_var($wpdb->prepare("SELECT id FROM " . $wpdb->prefix . "zvotedata WHERE postid = " . $postid . " AND userip = \"" . $_SERVER['REMOTE_ADDR'] . "\""));
	
	if (!$ipcheck) {
		//ok to vote, register vote
		$do = zVote_registerVote($postid);
		
		//grab entry, push to entry and inform that the vote has been casted correctly
		$post = get_permalink($postid);
		
		//decide which kind of injection to use, if ? is in the string we use &, if not we go with ?
		if (strpos($post,'?')) {				
			$injectionPoint = $post . '&zvoters=1';
		} else {
			$injectionPoint = $post . '?zvoters=1';
		}
		
		//send user to post
		header('Location: ' .$injectionPoint);
		exit;
	} else {
		//user already registered, push to entry-page and inform the user.
		$post = get_permalink($postid);
		
		if (strpos($post,'?')) {				
			$injectionPoint = $post . '&zvoters=2';
		} else {
			$injectionPoint = $post . '?zvoters=2';
		}
		
		header('Location: ' .$injectionPoint);
		exit;
	}
	exit;
}
?>