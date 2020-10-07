<?php
/**
 * Multi-Form Goals block/shortcode template
 * Styles for this template are defined in 'blocks/multi-form-goals/common.scss'
 */

$uniqid = uniqid();
?>

<div id="<?php echo $uniqid; ?>" class="give-progress-bar-block">
	<style>
		<?php echo file_get_contents( GIVE_PLUGIN_DIR . 'assets/dist/css/multi-form-goal-block.css' ); ?>
	</style>
	<div class="give-progress-bar-block__goal">
		<div class="give-progress-bar-block__progress">
			<?php $percent = ( $this->getTotal() / $this->getGoal() ) * 100; ?>
			<div class="give-progress-bar-block__progress-bar" style="width: <?php echo min( [ $percent, 100 ] ); ?>%; background: linear-gradient(180deg, <?php echo $this->getColor(); ?> 0%, <?php echo $this->getColor(); ?> 100%), linear-gradient(180deg, #fff 0%, #ccc 100%);"></div>
		</div>
	</div>
	<div class="give-progress-bar-block__stats">
		<div class="give-progress-bar-block__stat">
			<div><?php echo $this->getFormattedTotal(); ?></div>
			<div><?php echo __( 'raised', 'give' ); ?></div>
		</div>
		<div class="give-progress-bar-block__stat">
			<div><?php echo $this->getDonationCount(); ?></div>
			<div><?php echo _n( 'donation', 'donations', $this->getDonationCount(), 'give' ); ?></div>
		</div>
		<div class="give-progress-bar-block__stat">
			<div><?php echo $this->getFormattedGoal(); ?></div>
			<div><?php echo __( 'goal', 'give' ); ?></div>
		</div>
		<?php if ( ! empty( $this->getEndDate() ) ) : ?>
			<div class="give-progress-bar-block__stat">
				<div><?php echo $this->getTimeToGo(); ?></div>
				<div><?php echo $this->getTimeToGoLabel(); ?></div>
			</div>
		<?php endif; ?>
	</div>
</div>
<script>
	(function() {
		const container = document.getElementById('<?php echo $uniqid; ?>')
		const content = container.innerHTML
		const shadow = container.attachShadow({mode: 'open'});
		shadow.innerHTML = content
	})()
</script>
