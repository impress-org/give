<?php

use Give\Framework\FieldsAPI\Form;
use Give\Framework\FieldsAPI\Group;
use Give\Framework\FieldsAPI\Text;
use PHPUnit\Framework\TestCase;

final class WalkNodesTest extends TestCase {

    public function testWalk() {
	    $count = 0;

	    Form::make( 'form' )
	        ->append(
		        Text::make( 'firstTextField' ),
		        Text::make( 'secondTextField' ),
		        Text::make( 'thirdTextField' ),
		        Text::make( 'fourthTextField' )
	        )->walk(function() use ( &$count ) {
			    $count++;
		    });

        $this->assertEquals( 4, $count );
    }

    public function testNestedWalk() {
	    $count = 0;

	    Form::make( 'form' )
	        ->append(
		        Text::make( 'firstTextField' ),
		        Group::make( 'nested' )
		             ->append(
			             Text::make( 'thirdTextField' ),
			             Text::make( 'fourthTextField' )
		             ),
		        Text::make( 'secondTextField' )
	        )->walk(function() use ( &$count ) {
			    $count++;
		    });

        $this->assertEquals( 5, $count );
    }
}
