<?php
/*
Plugin Name: Interlinks
Plugin URI: http://www.harleyquine.com/php-scripts/interlinks/
Description: Make internal links in your blog simply by putting [[ and ]] (wikistyle) around the post title.
Version: 2.7
Author: Harley Quine
Author URI: http://www.harleyquine.com
*/

/*  Copyright 2008  Lisa-Marie Welsh  (email : harley@harleyquine.com)

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

add_filter('the_content', 'hq_interparse');
add_filter('the_excerpt', 'hq_interparse');

function hq_interreplace($val){

      global $table_prefix, $wpdb, $user_ID;
      $table_name = $table_prefix . "posts";
      $val = addslashes($val);

      $post_id = $wpdb->get_var("SELECT ID FROM $table_name WHERE post_title = '$val' AND post_status='publish'");
      if(!$post_id){ return 0; }
      else { return $post_id; }
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
                                 if($post_id == 0){ $content2 = "<span style='color:red;'>$new_val</span>"; }
                                 $permalink = get_permalink($post_id);
                                 if($post_id){ $content2 = "<a href='$permalink'>$link_text</a>"; }
                                 $content = str_replace($val, $content2, $content);
                              }
         else {
                                 $new_val = preg_replace('/\[\[(.+?)\]\]/', '$1', $val[0]);
                                 $post_id = hq_interreplace($new_val);
                                 if($post_id == 0){ $content2 = "<span style='color:red;'>$val[0]</span>"; }
                                 $permalink = get_permalink($post_id);
                                 if($post_id){ $content2 = "<a href='$permalink'>$new_val</a>"; }
                                 $content = str_replace($val, $content2, $content);
                              }
      }
   }

   return $content;
   }

?>
