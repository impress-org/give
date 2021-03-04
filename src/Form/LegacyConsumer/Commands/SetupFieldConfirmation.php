<?php

namespace Give\Form\LegacyConsumer\Commands;

use Give\Framework\FieldsAPI\FormField;
use Give\Framework\FieldsAPI\FieldCollection;

/**
 * @unreleased
 */
class SetupFieldConfirmation {

	/**
	 * @unreleased
	 *
	 * @param string $hook
	 */
	public function __construct( $payment, $receiptArgs ) {
		$this->payment     = $payment;
		$this->receiptArgs = $receiptArgs;
	}

	/**
	 * @unreleased
	 *
	 * @return void
	 */
	public function __invoke( $hook ) {

		$formID = give_get_payment_meta( $this->payment->ID, '_give_payment_form_id' );

		$fieldCollection = new FieldCollection( 'root' );
		do_action( "give_fields_{$hook}", $fieldCollection, $formID );

		$fieldCollection->walk( [ $this, 'render' ] );
	}

	public function render( FormField $field ) {

		if ( ! $field->shouldShowInReceipt() ) {
			return;
		}

		if ( $field->shouldStoreAsDonorMeta() ) {
			$donorID = give_get_payment_meta( $this->payment->ID, '_give_payment_donor_id' );
			$value   = Give()->donor_meta->get_meta( $donorID, $field->getName(), true );
		} else {
			$value = give_get_payment_meta( $this->payment->ID, $field->getName() );
		}

		if ( ! $value ) {
			return;
		}

		?>
			<tr>
				<td scope="row">
					<strong>
						<?php echo $field->getLabel(); ?>
					</strong>
				</td>
				<td>
					<?php echo $value; ?>
				</td>
			</tr>
		<?php
	}
}