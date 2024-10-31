<?php
wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );
?>

<style>
#side-sortables.empty-container {
	border: none;
	height: 350px;
}
</style>

<div class="wrap">
	
	<h2><?php echo $this->GetName(); ?></h2>

	<?php $this->GetClass('postman')->DisplayMessages(); ?>

	<form action="<?php echo $this->GetAsset( 'forms', 'save', 'url' ); ?>" method="post" id="">
		<?php //wp_nonce_field($this -> sections -> settings); ?>
		
		<div id="poststuff" class="metabox-holder">			
			
			<div id="post-body">
				<div id="post-body-content">
                	<div id="normal-sortables" class="meta-box-sortables ui-sortable">
						<?php $this->do_meta_boxes( 'normal' ); ?>
						<?php $this->do_meta_boxes( 'advanced' ); ?>
                    </div>
				</div>
			</div>
		</div>
	</form>
</div>
	
<script type="text/javascript">
//<![CDATA[
jQuery(document).ready( function($) {
	// close postboxes that should be closed
	$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
	// postboxes setup
	postboxes.add_postbox_toggles('<?php echo $pagehook; ?>');
});
//]]>
</script>