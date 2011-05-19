<?php


$mod_resource_capabilities = array(

    'mod/resource:view' => array(
        'captype' => 'read',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'user' => CAP_ALLOW,
            'student' => CAP_ALLOW,
        )
    ),
);
