<!-- #footer -->
<footer id="footer" class="clearfix">
<div class="container">
<?php if ( is_active_sidebar('st_footer') ) { ?>

<div id="footer-widgets" class="row">
<?php if ( !function_exists( 'dynamic_sidebar' ) || !dynamic_sidebar( 'Footer Widgets' ) ) ?>
</div>

<?php } ?>


<!-- #footer-bottom -->
<div id="footer-bottom" class="clearfix">
  <div id="copyright">
  <?php if (of_get_option('st_footer_copyright')) { ?>
  <small><?php echo of_get_option('st_footer_copyright'); ?></small>
  <?php } else { ?>
  <small>&copy;Copyright 2012, A <a href="http://swishthemes.com">Swish Theme</a>.</small>
  <?php } ?>
  </div>

  <?php if ( has_nav_menu( 'footer-nav' ) ) { /* if menu location 'footer-nav' exists then use custom menu */ ?>
  <nav id="footer-nav">
    <?php wp_nav_menu( array('theme_location' => 'footer-nav', 'depth' => 1, 'container' => false, 'menu_class' => 'nav-footer clearfix' )); ?>
  </nav>
  <?php } ?>
  
</div> 
<!-- /#footer-bottom -->
</div>
</footer>
<!-- /#footer -->



<?php wp_footer(); ?>
</body></html>