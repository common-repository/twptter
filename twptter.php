<?php
/*
Plugin Name: tWPtter..
Plugin URI: http://www.bochgoch.com/page/info/wp_twptter/
Description: tWPtter micro-blogging plugin that allows the addition of a micro-post carrying widget or page to your blog. 
             Use it for short posts, text snippets, facts and blurts!
Version: 0.1
Author: bochgoch
Author URI: http://www.bochgoch.com
*/

/*  Copyright 2008  Martin Ford, Bochgoch Limited (email : wordpress@bochgoch.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

add_option('twptter_numberOfPosts', '5', 'The number of tWPs (micro-posts) displayed by the tWPtter plugin', 'yes');
add_option('twptter_orderBy', 'rand()', 'The ordering method tWPs (micro-posts) displayed by the tWPtter plugin', 'yes');
add_option('twptter_showHeading', 'tWPtter says...', 'Heading shown above the tWPtter tWPs (micro-posts)', 'yes');
add_option('twptter_emailAuthor', '1', 'The Username of the author used for email posting of twps by the tWPtter plugin', 'yes');

function twptter() {
	global $wpdb;
 
	$numPosts = get_option('twptter_numberOfPosts');
	$orderBy = get_option('twptter_orderBy');
	$Heading = get_option('twptter_showHeading');

	$twptter = $wpdb->get_results("SELECT id, twppter_datetime, twptter_text, twptter_link_anchor, twptter_link_url, twptter_link_nofollow	FROM ".$wpdb->prefix."bochgoch_twptter ORDER BY ".$orderBy." LIMIT ".$numPosts);

	if (count($twptter) > 0) {
  	$twptterHTML = '<div id="twptter"><h2 class="widgettitle">'.$Heading.'</h2><ul>';
    foreach ($twptter as $twp) {
			$follow = 'follow';
		  if ($twp->twptter_link_nofollow == 'on') { $follow = 'nofollow'; }
    	$tagString .= strstr($twp->twptter_text,'[tag]'); // find tag if there is one
    	$twptterHTML .= '<li>'.str_replace ($tagString,'',$twp->twptter_text); // twp text without tag
			if (strlen($twp->twptter_link_url) > 0) { $twptterHTML .= '&nbsp;<a rel="'.$follow.'" href="http://'.$twp->twptter_link_url.'">'.$twp->twptter_link_anchor.'</a>'; }		
			preg_match("/\[tag](.*)\[\/tag]/", $twp->twptter_text, $matches); ////find tag if there is one 
			if (count($matches) > 0) { $twptterHTML .= '&nbsp;<a rel="'.$follow.'" href="'.get_bloginfo('url').'/tag/'.$matches[1].'/">tag: '.$matches[1].'</a>'; } // insert tag as a link			
			$twptterHTML .= '</li>';
    }
    $twptterHTML .= '</ul></div>';
	}	else {
    $twptterHTML = '';
	}
  _e($twptterHTML);
}

function twptter_css() {
echo '
<style type="text/css">
table.twptter 
{
border-collapse:collapse
}
table.twptter tr td
{
border-width:1px 0;
border-style:solid;
border-color:gray;
padding:3px 2px 3px 2px;
}
fieldset.twptter
{
border:1px solid gray;
}
fieldset.twptter input.textOnly
{
border:0;
background:transparent;
overflow:visible;
width:200px;
font-weight:900;
color:red;
}
fieldset.twptter .smalltext
{
font-size:0.85em;
}
fieldset.twptter label,legend 
{
float:left;
width:70px;
margin-right:0.5em;
text-align:right;
font-weight:bold;
}
</style>
';
echo "<script type=\"text/javascript\" src=\"".get_bloginfo('url')."/wp-content/plugins/twptter/form_validation.js\"></script>";
}
//poke the css into the <head>
add_action('admin_head', 'twptter_css');

function twptter_plugin_menu() {
	global $wpdb;

  $message = null;
  $message_updated = __("tWPtter Options Updated.");

  $wpdb->hide_errors();
  $checkTableExists = $wpdb->get_results("SELECT COUNT(*) FROM ".$wpdb->prefix."bochgoch_twptter");
  if (count($checkTableExists) != 1) {
    	$wpdb->get_results("CREATE TABLE ".$wpdb->prefix."bochgoch_twptter
                    	  ( 
                      		id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, 
                      		twppter_datetime DATETIME NULL,
                  				twptter_text varchar(150) NULL,
                  				twptter_link_anchor varchar(30) NULL,
                  				twptter_link_url varchar(100) NULL,
													twptter_link_nofollow varchar(2) NULL
                    		)
  		");  
  }
  $wpdb->show_errors();
  
  // update options
  if ($_POST['action'] && $_POST['action'] == 'twptter_update') {
  	$message = $message_updated;
		
  	update_option('twptter_numberOfPosts', $_POST['twptter_numberOfPosts']);
  	update_option('twptter_orderBy', $_POST['twptter_orderBy']);
  	update_option('twptter_showHeading', $_POST['twptter_showHeading']);
	  update_option('twptter_emailAuthor', $_POST['twptter_emailAuthor']);
	
	  $errormessage = "";
		
		// validate new twp
		if (strlen($_POST['twpurl'])>0)	{ $chkurl = str_replace('http://','',strtolower($_POST['twpurl']));	}
		if (strlen($_POST['twptextERR'])>0) { $errormessage .= ' twp text '.$_POST['twptextERR'].chr(13).chr(10); }
		if (strlen($_POST['twpanchorERR'])>0) { $errormessage .= ' twp anchor '.$_POST['twpanchorERR'].chr(13).chr(10); }
		if (strlen($_POST['twpurlERR'])>0) { $errormessage .= ' twp url '.$_POST['twpurlERR'].chr(13).chr(10); }
	
	  if (strlen($errormessage)>0){ 
			 $errormessage .= ' Cannot Save Changes '; 
			 $message = $errormessage;
			 $txt = $_POST['twptext'];
			 $anc = $_POST['twpanchor'];
			 $url = $_POST['twpurl'];
			 $nof = $_POST['twpnofollow'];
		}
		elseif (strlen($_POST['twptext'])>0)
		{
		 	 $INSERTTable = $wpdb->get_results("INSERT INTO ".$wpdb->prefix."bochgoch_twptter VALUES (null,null,'".$_POST['twptext']."','".$_POST['twpanchor']."','".$chkurl."','".$_POST['twpnofollow']."')");
  		 wp_cache_flush();
		}
		
		for ($counter = 1; $counter <= $_POST['twptter_TotalPosts']; $counter += 1)
		{
		 if ($_POST['twp'.$counter] != null)
		 { 
		 	 $INSERTTable = $wpdb->get_results("DELETE FROM ".$wpdb->prefix."bochgoch_twptter WHERE id=".$_POST['twp'.$counter]);
  		 wp_cache_flush();
		 }
		}
	}
  
	if ($message) : ?>
  <div id="message" class="updated fade"><p><?php echo $message; ?></p></div>
  <?php endif; ?>
  <div id="dropmessage" class="updated" style="display:none;"></div>
  <div class="wrap">
  <h2><?php _e('tWPtter Plugin Options'); ?></h2>
	<p>
	   <?php _e('<b>What is a twp?</b> ~ A twp is a micro-post, a short message, snippet, fact or blurt!') ?>
	   <?php _e('With other queries, questions or suggestions <a title="Bochgoch Home for tWPtter Wordpress Plugin" href="http://www.bochgoch.com/page/info/wp_twptter/">Visit Bochgoch</a>.') ?>
	</p>
  <form name="dofollow" method="post">
  <table>
  <tr>
  <th scope="row" style="text-align:right; vertical-align:top;"><?php _e('Heading:')?></th>
  <td>
  <input size="80" name="twptter_showHeading" value="<?php echo stripcslashes(get_option('twptter_showHeading')); ?>"/>
  </td>
  </tr>
  <tr>
  <th scope="row" style="text-align:right; vertical-align:top;"><?php _e('&nbsp;')?></th>
  <td>
  <input size="2" name="twptter_numberOfPosts" value="<?php echo stripcslashes(get_option('twptter_numberOfPosts')); ?>"/>
	<?php _e('twps displayed')?>
  </td>
  </tr>
  <tr>
  <th scope="row" style="text-align:right; vertical-align:top;"><?php _e('&nbsp;')?></th>
  <td>
  <input size="15" name="twptter_emailAuthor" value="<?php echo stripcslashes(get_option('twptter_emailAuthor')); ?>"/>
	<?php _e('the username of the author associated with emailed twps')?>
  </td>
  </tr>
  <tr>
  <th scope="row" style="text-align:right; vertical-align:top;"><?php _e('Order:')?></th>
  <td>
  <input type="radio" name="twptter_orderBy" value="rand()" <?php if (get_option('twptter_orderBy')=='rand()') echo "checked"; ?>> Random<br>
  <input type="radio" name="twptter_orderBy" value="twppter_datetime asc" <?php if (get_option('twptter_orderBy')=='twppter_datetime asc') echo "checked"; ?>> Date of post - ascending<br>
  <input type="radio" name="twptter_orderBy" value="twppter_datetime desc" <?php if (get_option('twptter_orderBy')=='twppter_datetime desc') echo "checked"; ?>> Date of post - descending<br>
  <input type="radio" name="twptter_orderBy" value="twptter_text asc" <?php if (get_option('twptter_orderBy')=='twptter_text asc') echo "checked"; ?>> Title of post - ascending<br>
  <input type="radio" name="twptter_orderBy" value="twptter_text desc" <?php if (get_option('twptter_orderBy')=='twptter_text desc') echo "checked"; ?>> Title of post - descending<br>
  </td>
  </tr>

<!-- Posts -->
	<tr>
  <th scope="row" style="text-align:right; vertical-align:top;"><?php _e(' ')?></th>
  <td>
        <fieldset id="newtwpSET" class="twptter">
          <legend>Your new twp</legend>
          <p>
					<label for="twptext">Text:</label>
					<textarea onkeyup="limitLength('twptext',150,'twptextC',true);" id="twptext" name="twptext" tabindex="1" cols="75" rows="2" ><?php _e($txt)?></textarea>
      		<span class="smalltext"><span class="smalltext" id="twptextC">150</span> characters remaining</span>
					<input name="twptextERR" id="twptextERR" class="textOnly"></input>
					</p>
					<p>
          <label for="twpanchor">Anchor:</label>
      		<input name="twpanchor" id="twpanchor" value="<?php _e($anc)?>" type="text" tabindex="1" size="30" onkeyup="limitLength('twpanchor',30,'twpanchorC',true);" />
      		<span class="smalltext"><span class="smalltext" id="twpanchorC">30</span> characters remaining</span>
					<input name="twpanchorERR" id="twpanchorERR" class="textOnly"></input>
					</p>
					<p>
          <label for="twpurl">URL:</label>
      		<input name="twpurl" id="twpurl" value="<?php _e($url)?>" type="text" tabindex="1" size="75" onkeyup="limitLength('twpurl',100,'twpurlC',false);" />
					<input type="checkbox" name="twpnofollow" <?php if ($nof == 'on') { _e('CHECKED'); } ?> />No Follow
					<span class="smalltext"><span class="smalltext" id="twpurlC">100</span> characters remaining</span>
					<br />
					<span class="smalltext" style="margin:0 0 0 80px;">in format <i>www.domain.com</i>&nbsp;&nbsp;&nbsp;</span>
					<input name="twpurlERR" id="twpurlERR" class="textOnly"></input>
					</p>
        </fieldset>
  </td>
  </tr>
	<tr>
  <th scope="row" style="text-align:right; vertical-align:top;"><?php _e('Your twps')?></th>
	<td>
	<table class="twptter">
<?php
	$orderBy = 'ID';
	$numPosts = '9999';
	$thoseWereTheDaysHTML = "";
	$counter = 1;

  $twptter = $wpdb->get_results("SELECT DISTINCT ID, twppter_datetime, twptter_text, twptter_link_anchor, twptter_link_url, twptter_link_nofollow FROM ".$wpdb->prefix."bochgoch_twptter ORDER BY ".$orderBy." LIMIT ".$numPosts);
	if (count($twptter) > 0) {
		echo '<tr><td colspan=4>Total Posts:<input size="2" readonly name="twptter_TotalPosts" value="'.count($twptter).'"/></td></tr>';
		echo '<tr><td colspan=4><b>Delete>></b> Tick checkbox then press the Update Options button.</td></tr>';
    foreach ($twptter as $twp) {
    	$twptterHTML .= '<tr><td><input type="checkbox" name="twp'.$counter.'" value="'.$twp->ID.'"';
			$twptterHTML .= '>';
			$twptterHTML .= $twp->ID;
			$twptterHTML .= '</td><td>';
			$twptterHTML .= $twp->twptter_text;
			$twptterHTML .= '</td><td>';
			$twptterHTML .= $twp->twptter_link_anchor;
			$twptterHTML .= '</td><td>';
			$twptterHTML .= $twp->twptter_link_url;
			if ($twp->twptter_link_nofollow == 'on') {$twptterHTML .='&nbsp;nofollow';} else {$twptterHTML .='&nbsp;follow';}
			$twptterHTML .= '</td><td>';
			$twptterHTML .= '</td>';
			$twptterHTML .= '</tr>';
			$counter += 1;
		}
	}
  _e($twptterHTML);
?>
	</table>
	</td></tr>
  </table>
  <div id="twpCommentary" style="font-weight:900;"></div>
  <p class="submit">
  <input type="hidden" name="action" value="twptter_update" /> 
  <input type="submit" name="Submit" value="<?php _e('Update Options')?> &raquo;" /> 
  </p>
  </form>
  </div>
  <?php
}
add_action('publish_phone', 'twptter_email_post');
function twptter_email_post($post_ID) {
	global $wpdb;
  $post = get_post($post_ID); 
  $userName = username_exists($post->post_author);
	if ($userName == get_option('twptter_emailAuthor')) // has this post been sent by the designated twptter email author?
  {
    _e('tWPtter Post detected! ~ will be converted to a twp');
		//if a link is to be included then the post title which has been set from an email subject will be in the format:
		//    anchor|url|follow or nofollow
		//eg. bochgoch ltd|www.bochgoch.com|follow
    $link = explode('|',$post->post_title);
		if (count($link) < 3) { $link = explode('|','||'); } // populate with dummy values
		$follow = '';	if ($link[2] == 'nofollow') { $follow = 'on'; }
	  // insert the post as a twp
		$INSERTTable = $wpdb->get_results("INSERT INTO ".$wpdb->prefix."bochgoch_twptter VALUES (null,null,'".addslashes($post->post_content)."','".$link[0]."','".$link[1]."','".$follow."')");
		// remove the post as a post
		wp_delete_post($post_ID); 
  }
}

add_action('plugins_loaded', 'twptter_init');
function twptter_init() {
    if (!function_exists('register_sidebar_widget')) { return; }
    register_sidebar_widget('tWPtter', 'twptter'); 
}

function twptter_add_pages() {
    // Add a new menu under Options:
    add_options_page('tWPptter options', 'tWPptter micro-blogging', 8, 'twptter.php', 'twptter_plugin_menu');
}
add_action('admin_menu', 'twptter_add_pages');
?>
