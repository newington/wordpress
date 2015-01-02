	<div class="clear"></div>
	</div><!-- end #wrap -->
		<?php if(is_active_sidebar('sidebar-footer-left') or is_active_sidebar('sidebar-footer-center') or is_active_sidebar('sidebar-footer-right')) : ?>
		<div id="bottombar">
			<ul id="bottombar_left">
				<?php dynamic_sidebar('sidebar-footer-left'); ?>
			</ul>
			<ul id="bottombar_right">
				<?php dynamic_sidebar('sidebar-footer-right'); ?>
			</ul>
			<ul id="bottombar_center">
				<?php dynamic_sidebar('sidebar-footer-center'); ?>
			</ul>
			<div class="clear"></div>
		</div>
		<?php endif; ?>
		
		<?php themezee_footer_before(); // hook before #footer ?>
		<div id="footer">
			<?php 
				$options = get_option('themezee_options');
				if ( isset($options['themeZee_general_footer']) and $options['themeZee_general_footer'] <> "" ) { 
					echo $options['themeZee_general_footer']; } 
			?>
			<div id="foot_navi">
				<?php 
				// Get Footer Navigation out of Theme Options
					wp_nav_menu(array('theme_location' => 'foot_navi', 'container' => false, 'echo' => true, 'fallback_cb' => null, 'before' => '', 'after' => '', 'link_before' => '', 'link_after' => '', 'depth' => 1));
				?>
			</div>
		</div>
		<div class="clear"></div>
		<?php themezee_footer_after(); // hook after #footer ?>
		
</div><!-- end #wrapper -->
<?php themezee_wrapper_after(); // hook after #wrapper ?>

	<div class="credit_link"><?php themezee_credit_link(); ?></div>
	<?php wp_footer(); ?>
</body>
</html>