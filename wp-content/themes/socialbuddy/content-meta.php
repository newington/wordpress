<?php $number = get_comments_number(get_the_ID()); ?>
<ul class="entry-meta">
<?php if ( has_post_format( 'aside' )) { ?><li><i class="icon-calendar"></i><time datetime="<?php the_time('Y-m-d')?>"><?php the_time( get_option('date_format') ); ?></time></li><?php } ?>
<?php if ( !in_category( '1' )) { ?><li><i class="icon-folder-close"></i><?php the_category('/ '); ?></li><?php } ?>
<li><i class="icon-user"></i><?php the_author_posts_link(); ?></li>
<?php if($number != 0) { ?>
<?php if ( comments_open() ){ ?><li><i class="icon-comment"></i><?php comments_popup_link( __( 'No Comments', 'framework' ), __( '1 Comment', 'framework' ), __( '% Comments', 'framework' ) ); ?></li><?php } ?>
<?php } ?>
</ul>