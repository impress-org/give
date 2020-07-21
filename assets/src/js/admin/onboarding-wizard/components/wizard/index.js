import PropTypes from 'prop-types';
import React from 'react';
import './style.scss';

import { useStoreValue } from '../../app/store';

import StepNavigation from '../step-navigation';
import Step from '../step';

const Wizard = ( { children } ) => {
	const [ { currentStep } ] = useStoreValue();
	const steps = children;

	return (
		<div className="give-obw">
			<StepNavigation steps={ steps } />
			{ steps.map( ( step, index ) => {
				if ( currentStep === index ) {
					return step;
				}
			} ) }
		</div>
	);
};

Wizard.propTypes = {
	children: function( props, propName, componentName ) {
		const prop = props[ propName ];

		let error = null;
		React.Children.forEach( prop, function( child ) {
			if ( child.type !== Step ) {
				error = new Error( '`' + componentName + '` children should be of type `Step`.' );
			}
		} );
		return error;
	},
};

Wizard.propTypes = {
	children: PropTypes.node.isRequired,
};

Wizard.defaultProps = {
	children: null,
};

export default Wizard;