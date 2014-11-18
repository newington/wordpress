<?php if (of_get_option('st_callout')) { ?>
<div id="callout" class="clearfix">
<div class="callout-left">
<h2><?php echo of_get_option('st_callout') ?></h2>
<?php if (of_get_option('st_callout_biline')) { ?><p><?php echo of_get_option('st_callout_biline') ?></p><?php } ?>
</div>
<?php if (of_get_option('st_callout_button_txt')) { ?>
<div class="callout-right">
<a class="btn" href="<?php echo of_get_option('st_callout_button_link') ?>"><?php echo of_get_option('st_callout_button_txt') ?></a>
</div>
<?php } ?>
</div>
<?php } ?>