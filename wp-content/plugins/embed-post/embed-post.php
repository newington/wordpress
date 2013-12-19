<?php
/**
Plugin Name: Embed Post
Plugin URI: http://wordpress.org/extend/plugins/embed-post/
Description: Embed a Post within another Post or Page using [embed_post] shortcode.
Version: 0.3
Author: Ghanshyam Khanal
Author URI: http://gskhanal.com/
*/

/*
 * function embed_post_shortcode
 * This function embeds post excerpt, content, title or thumbnail to another post
 */
if(!function_exists('embed_post_shortcode')){
    function embed_post_shortcode( $atts ){
        extract( shortcode_atts( array(
            'post_id' => 0,
            'type' => 'excerpt',
            'limit_word' => 0,
            'hide_more' => 0
        ), $atts ) );
        if( (int)$post_id < 1 ){
            return '';
        }else{
            global $post;
            $post = get_post($post_id);
            if($post->post_status == "publish"){
                setup_postdata( $post );
                switch( $type ){
                    case 'content':
                        $contentData = get_the_content();
                        if((int)$limit_word > 0){
                            $contentData = embed_post_string_to_limited_words($contentData, (int)$limit_word);
                        }
                        $content     = '<div class="embed_post">';
                        $content    .= $contentData;
                        if($hide_more != 1){
                            $content    .= '<a href="'. get_permalink() . '" title="Read More" class="embed_post_more">Read More...</a>';
                        }
                        $content    .= '</div>';
                        wp_reset_query();
                        return $content;
                        break;
                    case 'title':
                        $title   = '<div class="embed_post">';
                        if($hide_more != 1){
                            $title  .= '<a href="'. get_permalink() . '" title="Read More">';
                        }
                        $title  .= get_the_title();
                        $title  .= '</a>';
                        $title  .= '</div>';
                        wp_reset_query();
                        return $title;
                        break;
                    case 'excerpt': default:
                        $excerpt     = '<div class="embed_post">';
                        $excerpt    .= strip_tags(get_the_excerpt());
                        if($hide_more != 1){
                            $excerpt    .= '<a href="'. get_permalink() . '" title="Read More" class="embed_post_more">Read More...</a>';
                        }
                        $excerpt    .= '</div>';
                        wp_reset_query();
                        return $excerpt;
                        break;
                }
            }
        }
    }
}
add_shortcode('embed_post', 'embed_post_shortcode');

/*
 * function embed_post_string_to_limited_words
 * This function limits words in string
 */
if(!function_exists('embed_post_string_to_limited_words')){
    function embed_post_string_to_limited_words($string, $word_limit = 20){
        $words = explode(' ', $string, ($word_limit + 1));
        if(count($words) > $word_limit)
        array_pop($words);
        return implode(' ', $words);
    }
}
?>
