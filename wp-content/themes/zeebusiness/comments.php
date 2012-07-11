<?php 
	
	if ( post_password_required()) { ?>
		<p><?php _e('Enter password to view comments.', 'themezee_lang'); ?></p>
	<?php return; } ?>

<?php if ( have_comments() ) : ?>

<div id="comments">
	<h3><?php comments_number(__('No comments', 'themezee_lang'),__('One comment','themezee_lang'),__('% comments','themezee_lang') );?></h3>

	<?php if ( get_comment_pages_count() > 1 ) : ?>
	<div class="comment_navi">
		<div class="alignleft"><?php previous_comments_link() ?></div>
		<div class="alignright"><?php next_comments_link() ?></div>
	</div>
	<div class="clear"></div>
	<?php endif; ?>
	
	<ol class="commentlist">
	<?php wp_list_comments( array('avatar_size' => 48)); ?>
	</ol>

	<div class="comment_navi">
		<div class="alignleft"><?php previous_comments_link() ?></div>
		<div class="alignright"><?php next_comments_link() ?></div>
	</div>
	<div class="clear"></div>
</div>
 <?php else : ?>

	<?php if ( ! comments_open() and !is_page() ) : ?>
		<p class="nocomments"><?php _e('Comments are closed.', 'themezee_lang'); ?></p>
	<?php endif; ?>
<?php endif; ?>

<?php if ( comments_open() ) : ?>
	<?php comment_form(); ?>
	<div class="clear"></div>
<?php endif; ?>
