// Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>
//
// Permission is hereby granted, free of charge, to any person obtaining a copy
// of this software and associated documentation files (the "Software"), to deal
// in the Software without restriction, including without limitation the rights
// to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
// copies of the Software, and to permit persons to whom the Software is
// furnished to do so, subject to the following conditions:
//
// The above copyright notice and this permission notice shall be included in all
// copies or substantial portions of the Software.
//
// THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
// IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
// FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
// AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
// LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
// OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
// SOFTWARE.
$(document).ready(function() {
    $('#pLaunchpadId').select2({
        placeholder: 'Select a Launchpad',
    }).change();

    $('#pRocketId').select2({
        placeholder: 'Select a Launchpad Rocket',
    });

    $('#pPackId').select2({
        placeholder: 'Select a Service Pack',
    });

    $('#pClusterId').select2({
        placeholder: 'Select a Cluster',
    }).change();
});

// Protect against admin mistakes
$('#pCPULimit').on('input', function() {
    const value = Number($(this).val());
    const dividedValue = value / 8;
    $('#pCPURequest').val(parseFloat(dividedValue).toFixed(0));
});
$('#pCPURequest').on('input', function() {
    const input1Value = Number($(this).val());
    const input2Value = Number($('#pCPULimit').val());

    if (input1Value > input2Value) {
        $(this).val(input2Value);
    } else if (input1Value == 0) {
        $(this).val(parseFloat(input2Value / 8).toFixed(0));
    }
});
$('#pMemoryLimit').on('input', function() {
    const value = Number($(this).val());
    const dividedValue = value / 2;
    $('#pMemoryRequest').val(parseFloat(dividedValue).toFixed(0));
});
$('#pMemoryRequest').on('input', function() {
    const input1Value = Number($(this).val());
    const input2Value = Number($('#pMemoryLimit').val());

    if (input1Value > input2Value) {
        $(this).val(input2Value);
    } else if (input1Value == 0) {
        parseFloat($(this).val(input2Value / 2)).toFixed(2);
    }
});

$('input[type="radio"][name="allocation_system"]').on('load change', function() {
    if ($(this).is(':checked') && this.value === 'manual') {
        $('#pDefaultPort').closest('.form-group').remove();
        $('#pAdditionalPorts').closest('.form-group').remove();

        $(this).closest('.form-group').removeClass('new-element-added');

        // Get the parent form group
        var formGroup = $(this).closest('.form-group');

        // Check if the new element has already been added
        if (!formGroup.hasClass('new-element-added')) {
            // Create the new element you want to add
            var newElement = $('<div class="form-group col-md-6"> \
                <label for="pAllocation">Default Allocation</label> \
                <select id="pAllocation" name="allocation_id" class="form-control"></select> \
                <p class="small text-muted no-margin">The main allocation that will be assigned to this server.</p> \
            </div> \
            <div class="form-group col-md-6"> \
                <label for="pAllocationAdditional">Additional Allocation(s)</label> \
                <select id="pAllocationAdditional" name="allocation_additional[]" class="form-control" multiple></select> \
                <p class="small text-muted no-margin">Additional allocations to assign to this server on creation.</p> \
            </div>');

            // Add the new element after the form group
            formGroup.after(newElement);
  
            // Add a class to the form group to indicate that the new element has been added
            formGroup.addClass('new-element-added');
        }

        $('#pClusterId').trigger('change');
    } else if ($(this).is(':checked') && this.value === 'automatic') {
        $('#pAllocation').closest('.form-group').remove();
        $('#pAllocationAdditional').closest('.form-group').remove();

        $(this).closest('.form-group').removeClass('new-element-added');

        // Get the parent form group
        var formGroup = $(this).closest('.form-group');

        // Check if the new element has already been added
        if (!formGroup.hasClass('new-element-added')) {
            // Create the new element you want to add
            var newElement = $('<div class="form-group col-md-6"> \
                <label for="pDefaultPort">Default Port</label> \
                <input type="text" id="pDefaultPort" name="default_port" class="form-control" value=""></input> \
                <p class="small text-muted no-margin">The main port that will be assigned to this server.</p> \
            </div> \
            <div class="form-group col-md-6"> \
                <label for="pAdditionalPorts" class="control-label">Additional Port(s)</label> \
                <div> \
                    <select class="form-control" name="additional_ports[]" id="pAdditionalPorts" multiple></select> \
                    <p class="text-muted small">Enter individual ports here separated by commas or spaces. <b>Restrictions will apply</b>, please see <a href="https://kubernetes.io/docs/reference/networking/ports-and-protocols/" target="_blank">kubernetes.io/docs/reference/networking/ports-and-protocols</a> for more info.</p> \
                </div> \
            </div>');

            // Add the new element after the form group
            formGroup.after(newElement);
  
            // Add a class to the form group to indicate that the new element has been added
            formGroup.addClass('new-element-added');

            $('#pAdditionalPorts').select2({
                tags: true,
                selectOnClose: true,
                tokenSeparators: [',', ' '],
            });
        }

        $('#pClusterId').trigger('change');
    }
}).change();

let lastActiveBox = null;
$(document).on('click', function (event) {
    if (lastActiveBox !== null) {
        lastActiveBox.removeClass('box-primary');
    }

    lastActiveBox = $(event.target).closest('.box');
    lastActiveBox.addClass('box-primary');
});

$('#pClusterId').on('change', function () {
    currentCluster = $(this).val();
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

$('#pLaunchpadId').on('change', function (event) {
    $('#pRocketId').html('').select2({
        data: $.map(_.get(Kubectyl.launchpads, $(this).val() + '.rockets', []), function (item) {
            return {
                id: item.id,
                text: item.name,
            };
        }),
    }).change();

    $('#pNodeSelectorFrom').html('<option value="">None</option>').select2({
        data: $.map(_.get(Kubectyl.launchpads, $(this).val() + '.rockets', []), function (item) {
            return {
                id: item.id,
                text: item.name + ' <' + item.author + '>',
            };
        }),
    });
});

$('#pNodeSelectorFrom').on('select2:select', function(e) {
    var selector = $('#pNodeSelector')
    const itemId = e.params.data.id

    selector.val('');

    $.each(_.get(Kubectyl.launchpads, $('#pLaunchpadId').val() + '.rockets', []), function (index, item) {
        if (item && item.id == itemId) {
            var obj = item.node_selectors

            $.each(obj, function(key, value) {
                selector.val(selector.val() + key + ":" + value + "\n");
            });
        }
    });
});

$('#pRocketId').on('change', function (event) {
    let parentChain = _.get(Kubectyl.launchpads, $('#pLaunchpadId').val(), null);
    let objectChain = _.get(parentChain, 'rockets.' + $(this).val(), null);

    const images = _.get(objectChain, 'docker_images', {})
    $('#pDefaultContainer').html('');
    const keys = Object.keys(images);
    for (let i = 0; i < keys.length; i++) {
        let opt = document.createElement('option');
        opt.value = images[keys[i]];
        opt.innerHTML = keys[i] + " (" + images[keys[i]] + ")";
        $('#pDefaultContainer').append(opt);
    }

    if (!_.get(objectChain, 'startup', false)) {
        $('#pStartup').val(_.get(parentChain, 'startup', 'ERROR: Startup Not Defined!'));
    } else {
        $('#pStartup').val(_.get(objectChain, 'startup'));
    }

    $('#pPackId').html('').select2({
        data: [{ id: 0, text: 'No Service Pack' }].concat(
            $.map(_.get(objectChain, 'packs', []), function (item, i) {
                return {
                    id: item.id,
                    text: item.name + ' (' + item.version + ')',
                };
            })
        ),
    });

    const variableIds = {};
    $('#appendVariablesTo').html('');
    $.each(_.get(objectChain, 'variables', []), function (i, item) {
        variableIds[item.env_variable] = 'var_ref_' + item.id;

        let isRequired = (item.required === 1) ? '<span class="label label-danger">Required</span> ' : '';
        let dataAppend = ' \
            <div class="form-group col-sm-6"> \
                <label for="var_ref_' + item.id + '" class="control-label">' + isRequired + item.name + '</label> \
                <input type="text" id="var_ref_' + item.id + '" autocomplete="off" name="environment[' + item.env_variable + ']" class="form-control" value="' + item.default_value + '" /> \
                <p class="text-muted small">' + item.description + '<br /> \
                <strong>Access in Startup:</strong> <code>{{' + item.env_variable + '}}</code><br /> \
                <strong>Validation Rules:</strong> <code>' + item.rules + '</code></small></p> \
            </div> \
        ';
        $('#appendVariablesTo').append(dataAppend);
    });

    // If you receive a warning on this line, it should be fine to ignore. this function is
    // defined in "resources/views/admin/servers/new.blade.php" near the bottom of the file.
    serviceVariablesUpdated($('#pRocketId').val(), variableIds);
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

function initUserIdSelect(data) {
    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    $('#pUserId').select2({
        ajax: {
            url: '/admin/users/accounts.json',
            dataType: 'json',
            delay: 250,

            data: function (params) {
                return {
                    filter: { email: params.term },
                    page: params.page,
                };
            },

            processResults: function (data, params) {
                return { results: data };
            },

            cache: true,
        },

        data: data,
        escapeMarkup: function (markup) { return markup; },
        minimumInputLength: 2,
        templateResult: function (data) {
            if (data.loading) return escapeHtml(data.text);

            return '<div class="user-block"> \
                <img class="img-circle img-bordered-xs" src="https://www.gravatar.com/avatar/' + escapeHtml(data.md5) + '?s=120" alt="User Image"> \
                <span class="username"> \
                    <a href="#">' + escapeHtml(data.name_first) + ' ' + escapeHtml(data.name_last) +'</a> \
                </span> \
                <span class="description"><strong>' + escapeHtml(data.email) + '</strong> - ' + escapeHtml(data.username) + '</span> \
            </div>';
        },
        templateSelection: function (data) {
            return '<div> \
                <span> \
                    <img class="img-rounded img-bordered-xs" src="https://www.gravatar.com/avatar/' + escapeHtml(data.md5) + '?s=120" style="height:28px;margin-top:-4px;" alt="User Image"> \
                </span> \
                <span style="padding-left:5px;"> \
                    ' + escapeHtml(data.name_first) + ' ' + escapeHtml(data.name_last) + ' (<strong>' + escapeHtml(data.email) + '</strong>) \
                </span> \
            </div>';
        }

    });
}
