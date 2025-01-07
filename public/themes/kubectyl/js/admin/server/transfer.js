$(document).ready(function () {
    $('#pClusterId').select2({
        placeholder: 'Select a Cluster',
    }).change();

    $('#pAllocation').select2({
        placeholder: 'Select a Default Allocation',
    });

    $('#pAllocationAdditional').select2({
        placeholder: 'Select Additional Allocations',
    });
});

$('#pClusterId').on('change', function () {
    let currentCluster = $(this).val();

    $.each(Kubectyl.clusterData, function (i, v) {
        if (v.id == currentCluster) {
            $('#pAllocation').html('').select2({
                data: v.allocations,
                placeholder: 'Select a Default Allocation',
            });

            updateAdditionalAllocations();
        }
    });
});

$('#pAllocation').on('change', function () {
    updateAdditionalAllocations();
});

function updateAdditionalAllocations() {
    let currentAllocation = $('#pAllocation').val();
    let currentCluster = $('#pClusterId').val();

    $.each(Kubectyl.clusterData, function (i, v) {
        if (v.id == currentCluster) {
            let allocations = [];

            for (let i = 0; i < v.allocations.length; i++) {
                const allocation = v.allocations[i];

                if (allocation.id != currentAllocation) {
                    allocations.push(allocation);
                }
            }

            $('#pAllocationAdditional').html('').select2({
                data: allocations,
                placeholder: 'Select Additional Allocations',
            });
        }
    });
}
