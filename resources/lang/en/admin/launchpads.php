<?php

return [
    'notices' => [
        'created' => 'A new launchpad, :name, has been successfully created.',
        'deleted' => 'Successfully deleted the requested launchpad from the Panel.',
        'updated' => 'Successfully updated the launchpad configuration options.',
    ],
    'rockets' => [
        'notices' => [
            'imported' => 'Successfully imported this Rocket and its associated variables.',
            'updated_via_import' => 'This Rocket has been updated using the file provided.',
            'deleted' => 'Successfully deleted the requested rocket from the Panel.',
            'updated' => 'Rocket configuration has been updated successfully.',
            'script_updated' => 'Rocket install script has been updated and will run whenever servers are installed.',
            'rocket_created' => 'A new rocket was laid successfully. You will need to restart any running daemons to apply this new rocket.',
        ],
    ],
    'variables' => [
        'notices' => [
            'variable_deleted' => 'The variable ":variable" has been deleted and will no longer be available to servers once rebuilt.',
            'variable_updated' => 'The variable ":variable" has been updated. You will need to rebuild any servers using this variable in order to apply changes.',
            'variable_created' => 'New variable has successfully been created and assigned to this rocket.',
        ],
    ],
];
