<?php if (!defined ('ABSPATH')) die ('No direct access allowed'); ?>

<div class="wrap" id="cron-gui">

	<!--<ul>
		<?php foreach( $schedules as $schedule ) { ?>
			<li><strong><?php echo $schedule[ 'display' ]; ?></strong>, every <?php echo human_time_diff( 0, $schedule[ 'interval' ] ); ?></li>
		<?php } ?>
	</ul>-->

	<h3><?php _e('Push Notification', 'universal'); ?></h3>

	<table class="widefat fixed">
		<thead>
			<tr>
				<th scope="col"><?php _e('Push timing (GMT/UTC)', 'universal'); ?></th>
				<th scope="col"><?php _e('Post', 'universal'); ?></th>
				<th scope="col"><?php _e('Scheduling', 'universal'); ?></th>
				<th scope="col"><?php _e('Action', 'universal'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ( $cron as $timestamp => $cronhooks ) { ?>
				<?php foreach ( (array) $cronhooks as $hook => $events ) { ?>
					<?php if ( $hook != "uni_plg_post_published_notification") continue; ?>
					<?php foreach ( (array) $events as $event ) { ?>
						<tr>

							<td scope="row"><?php echo $event[ 'date' ]; ?> (<?php echo $timestamp; ?>)</td>

							<td>
								<a href="<?php echo admin_url()?>post.php?post=<?php echo $event['args'][1]->ID;?>&action=edit">
									<?php echo $event[ 'args' ][1]->post_title; ?>
								</a>
							</td>

							<td>
								<?php 
									if ( $event[ 'schedule' ] ) {
										echo $schedules [ $event[ 'schedule' ] ][ 'display' ]; 
									} else {
										?><em><?php _e('One time push', 'universal'); ?></em><?php
									}
								?>
							</td>

							<td>
									<button name="cron_delete_timestamp" value="<?=$timestamp?>">Delete</button>
							</td>
						</tr>
					<?php } ?>
				<?php } ?>
			<?php } ?>
		</tbody>
	</table>
	
</div>