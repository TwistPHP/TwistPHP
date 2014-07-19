<?php
    $arrParameters = $this -> getParameters();
    $intFrom = $arrParameters[0];
    for( $intNumber = $intFrom; $intNumber >= 0; $intNumber-- ) {
        echo sprintf( '%d...', $intNumber );
    }
    echo 'Blast Off!';