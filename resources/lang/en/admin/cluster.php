<?php

return [
    'validation' => [
        'fqdn_not_resolvable' => 'The FQDN or IP address provided does not resolve to a valid IP address.',
        'fqdn_required_for_ssl' => 'A fully qualified domain name that resolves to a public IP address is required in order to use SSL for this cluster.',
    ],
    'notices' => [
        'allocations_added' => 'Allocations have successfully been added to this cluster.',
        'cluster_deleted' => 'Cluster has been successfully removed from the panel.',
        'location_required' => 'You must have at least one location configured before you can add a cluster to this panel.',
        'cluster_created' => 'Successfully created new cluster. You can automatically configure the daemon on this machine by visiting the \'Configuration\' tab. <strong>Before you can add any servers you must first allocate at least one IP address and port.</strong>',
        'cluster_updated' => 'Cluster information has been updated. If any daemon settings were changed you will need to reboot it for those changes to take effect.',
        'unallocated_deleted' => 'Deleted all un-allocated ports for <code>:ip</code>.',
    ],
];
