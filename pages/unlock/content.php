<?php
wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false );
wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false );

$form_action = $this->GetAsset( 'forms', 'unlock' );
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

	<form action="<?php echo $form_action['container_link'].'/'.$form_action['file_name']; ?>" method="post" id="" class="form-horizontal">
		<?php //wp_nonce_field($this -> sections -> settings); ?>
		
		<div id="poststuff" class="metabox-holder has-right-sidebar">			
			<div id="side-info-column" class="inner-sidebar">
				<?php $this->do_meta_boxes( 'side' ); ?>
			</div>
			<div id="post-body" class="has-sidebar">
				<div id="post-body-content" class="has-sidebar-content">
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