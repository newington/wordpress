<?php if ( is_active_sidebar('st_homepage_widgets') ) { ?>
<div id="homepage-widgets" class="row stacked">
<?php if ( !function_exists( 'dynamic_sidebar' ) || !dynamic_sidebar( 'st_homepage_widgets' ) ) ?>
</div>
<?php } ?>