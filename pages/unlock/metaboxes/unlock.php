<?php
$api_key = $this->get_option( 'api_key' );

$key_passed = $this->VerifyApiKey( $api_key );
?>

<input type="hidden" name="plugin_pre" value="<?php echo $this->GetPre(); ?>" />

<div style="width:600px; margin:50px auto; text-align:center;">
	
	<table class="widefat" style="padding:60px 0px;">
		<tr>
			<td style="border:none;">
				<img style="width:200px;" src="<?php echo $this->GetOwlImage(); ?>" />
			</td>
			<td style="border:none; text-align:left;">
				
				<?php
				if( $key_passed )
				{ ?>
				<h1 style="color:#090; margin-top:50px;"><?php echo $this->GetOwlName(); ?> is clocked in!</h1>
				
				<div style="padding-bottom:10px; font-size:14px;">
					<p>To clock out & change the O.I.N, click the button below.</p> 
				</div>
				<label>
					<span style="font-size:14px;">O.I.N</span>
					<input readonly="readonly" class="widefat" type="text" name="api_key" value="<?php echo $api_key; ?>" style="padding: 8px; width:200px; font-size:20px;" />
					<button class="btn btn-error">Clock Out</button>
					<input type="hidden" name="action" value="clock_out" />
					<input type="hidden" name="plugin_pre" value="<?php echo $this->GetPre(); ?>" />
				</label>
				<?php
				}
				else
				{ ?>
				<h1><?php echo $this->m_owl_name; ?> must clock in!</h1>
				
				<div style="padding-bottom:10px; font-size:14px;">
					<p>Before you can use this plugin, you must hire 
					<strong><?php echo $this->m_owl_name; ?></strong> to manage it for you! To hire this owl, click 
					<a target="_blank" href="<?php echo $this->m_owl_buy_url; ?>">here</a>.</p>
					
					<p>If <?php echo $this->m_owl_name; ?> is already working for you, enter their 
					<a target="_blank" href="http://plugins.qody.co/my-owls/" style="font-weight:strong;">Owl Identification Number</a> 
					below to get started.</p> 
				</div>
				<label>
					<span style="font-size:14px;">O.I.N</span>
					<input class="widefat" type="text" name="api_key" value="<?php echo $api_key; ?>" style="padding: 8px; width:200px; font-size:20px;" />
					<button class="btn btn-primary">Clock In</button>
				</label>
				<?php
				} ?>
			</td>
		</tr>
	</table>
	
</div>