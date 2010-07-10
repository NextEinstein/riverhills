<?php

if ($userpref === false || $changeuserpref || $saveuserpref) {
    if ($saveuserpref) {
        if (!confirm_sesskey()) {
            error('There was a problem with your session key. Please try again or contact the system administrator if this continues';
        }

        
    }
    print_box_start('boxaligncenter');

    print_heading(get_string('choosemethod', 'memorization'));

    $methods = memorization_method_id_filename_mapping_array();

    echo '<form method="post">';
    echo '<input type="hidden" name="id" value="'.$id.'"/>';
    echo '<input type="hidden" name="userid" value="'.$userid.'"/>';
    echo '<input type="hidden" name="saveuserpref" value="1"/>';
    echo '<input type="hidden" name="sesskey" value="'.sesskey().'"/>';

    if (!empty($methods)) {
        foreach ($methods as $methodid => $method) {
            echo '<input type="radio" name="methodid" value="'.$methodid.'"> '.get_string($method, 'memorization'). '<br />';
        }
    }
    echo '</form>';
}
