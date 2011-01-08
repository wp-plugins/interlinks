<?php
/*
Plugin Name: Interlinks
Plugin URI: http://www.craftycoding.com/products/wordpress-plugins/interlinks/
Description: Make internal links in your blog simply by putting [[ and ]] (wikistyle) around the post title. You may also use the more advanced [[Post title|Link text]] style of linking. Interlinking can be used in posts and excerpts. Settings can be tweaked from the <a href='options-reading.php'>Settings -> Reading page</a>. For support or suggestions please visit the <a href='http://www.craftycoding.com/products/wordpress-plugins/interlinks/' target='_blank'>plugin page</a>.
Version: 3.0
Author: Crafty Coding
Author URI: http://www.craftycoding.com
*/

/*  Copyright 2011  Lisa-Marie Welsh  (email : coder@craftycoding.com)

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

if(get_option('interlinks_in_comments') == 1){
    add_filter('comment_text', 'hq_interparse');
    add_filter('comment_excerpt', 'hq_interparse');
}

add_filter('the_content', 'hq_interparse');
add_filter('the_excerpt', 'hq_interparse');
add_action( 'admin_init', 'hq_interlinks_register_settings' );



function hq_interlinks_register_settings(){
    add_settings_section('interlinks', 'Interlinks', 'hq_interlinks_cpanel', 'reading');
    add_settings_field('interlinks_in_comments', 'Would you like Interlinks in comments?', 'hq_interlinks_setting_callback_function3', 'reading', 'interlinks');
    add_settings_field('hide_red_links', 'Hide red links from guests?', 'hq_interlinks_setting_callback_function', 'reading', 'interlinks');
    add_settings_field('link_new_page', 'Would you like the red links to link to a new page?', 'hq_interlinks_setting_callback_function2', 'reading', 'interlinks');

    register_setting( 'reading', 'interlinks_in_comments' );
    register_setting( 'reading', 'hide_red_links' );
    register_setting( 'reading', 'link_new_page' );
}

function hq_interlinks_setting_callback_function() {
 	echo '<input name="hide_red_links" id="hide_red_links" type="checkbox" value="1" class="code" ' . checked( 1, get_option('hide_red_links'), false ) . ' /> Would you like links to missing pages to only be seen by logged in users?';
 }

function hq_interlinks_setting_callback_function2() {
 	echo '<input name="link_new_page" id="link_new_page" type="checkbox" value="1" class="code" ' . checked( 1, get_option('link_new_page'), false ) . ' /> Would you like red links to link to the Posts -> Add New page in the dashboard (only if user is logged in)?';
}

function hq_interlinks_setting_callback_function3() {
 	echo '<input name="interlinks_in_comments" id="interlinks_in_comments" type="checkbox" value="1" class="code" ' . checked( 1, get_option('interlinks_in_comments'), false ) . ' /> Would you like to allow Interlinks in comments?';
}

function hq_interlinks_cpanel(){
    echo "Settings for Interlinks. For more information on these settings or to leave a comment/suggestion, please visit the <a href='http://www.craftycoding.com/products/wordpress-plugins/interlinks/' target='_blank'>plugin page</a>.";
}

function hq_interreplace($val){
      global $table_prefix, $wpdb, $user_ID;
      $table_name = $table_prefix . "posts";
      $val = addslashes($val);

      $post_id = $wpdb->get_var("SELECT ID FROM $table_name WHERE post_title = '$val' AND post_status='publish'");
      if(!$post_id){ return 0; }
      else { return $post_id; }
}
function hq_interlinks_clean($content){
    $content = str_replace("[","",$content);
    $content = str_replace("]","",$content);
    return $content;
}

function hq_interparse($content){
   if(strpos($content, "[[")){
      preg_match_all('/(\[\[.+?\]\])/',$content,$wikilinks, PREG_SET_ORDER);
      foreach ($wikilinks as $val) {
         if(strpos($val[0], "|")){
                                 $pieces = explode("|", $val[0]);
                                 $new_val = preg_replace('/\[\[(.+?)/', '$1', $pieces[0]);
                                 $link_text = preg_replace('/(.+?)\]\]/', '$1', $pieces[1]);
                                 $post_id = hq_interreplace($new_val);
                                 if($post_id == 0){
                                     if(is_user_logged_in() && get_option('hide_red_links') == 1 || get_option('hide_red_links') != 1){
                                        if(get_option('link_new_page') == 1 && is_user_logged_in()){
                                            $content2 = "<span style='color:red;'><a href='" . get_bloginfo('url') . "/wp-admin/post-new.php?post_title=" . $new_val . "'>" . $val[0] . "</a></span>";
                                        }
                                        else { $content2 = "<span style='color:red;'>$val[0]</span>"; }
                                     }
                                     else { $content2 = hq_interlinks_clean($new_val); }
                                 }
                                 $reading = get_permalink($post_id);
                                 if($post_id){ $content2 = "<a href='$reading'>$link_text</a>"; }
                                 $content = str_replace($val, $content2, $content);
                              }
         else {
                                 $new_val = preg_replace('/\[\[(.+?)\]\]/', '$1', $val[0]);
                                 $post_id = hq_interreplace($new_val);
                                 if($post_id == 0){
                                     if(is_user_logged_in() && get_option('hide_red_links') == 1 || get_option('hide_red_links') != 1){
                                         if(get_option('link_new_page') == 1 && is_user_logged_in()){
                                             $content2 = "<span style='color:red;'><a href='" . get_bloginfo('url') . "/wp-admin/post-new.php?post_title=" . hq_interlinks_clean($val[0]) . "'>" . $val[0] . "</a></span>";
                                         }
                                         else { $content2 = "<span style='color:red;'>$val[0]</span>"; }
                                     }
                                     else { $content2 = hq_interlinks_clean($val[0]); }
                                 }
                                 $reading = get_permalink($post_id);
                                 if($post_id){ $content2 = "<a href='$reading'>$new_val</a>"; }
                                 $content = str_replace($val, $content2, $content);
                              }
      }
   }

   return $content;
   }

?>
