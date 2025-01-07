@extends('layouts.admin')

@section('title')
    New Server
@endsection

@section('content-header')
    <h1>Create Server<small>Add a new server to the panel.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.servers') }}">Servers</a></li>
        <li class="active">Create Server</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('admin.servers.new') }}" method="POST">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Core Details</h3>
                </div>

                <div class="box-body row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pName">Server Name</label>
                            <input type="text" class="form-control" id="pName" name="name" value="{{ old('name') }}" placeholder="Server Name">
                            <p class="small text-muted no-margin">Character limits: <code>a-z A-Z 0-9 _ - .</code> and <code>[Space]</code>.</p>
                        </div>

                        <div class="form-group">
                            <label for="pUserId">Server Owner</label>
                            <select id="pUserId" name="owner_id" class="form-control" style="padding-left:0;"></select>
                            <p class="small text-muted no-margin">Email address of the Server Owner.</p>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="pDescription" class="control-label">Server Description</label>
                            <textarea id="pDescription" name="description" rows="3" class="form-control">{{ old('description') }}</textarea>
                            <p class="text-muted small">A brief description of this server.</p>
                        </div>

                        <div class="form-group">
                            <div class="checkbox checkbox-primary no-margin-bottom">
                                <input id="pStartOnCreation" name="start_on_completion" type="checkbox" {{ \Kubectyl\Helpers\Utilities::checked('start_on_completion', 1) }} />
                                <label for="pStartOnCreation" class="strong">Start Server when Installed</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="overlay" id="allocationLoader" style="display:none;"><i class="fa fa-refresh fa-spin"></i></div>
                <div class="box-header with-border">
                    <h3 class="box-title">Allocation Management</h3>
                </div>

                <div class="box-body row">
                    <div class="form-group col-md-4">
                        <label for="pClusterId">Cluster</label>
                        <select name="cluster_id" id="pClusterId" class="form-control">
                            @foreach($locations as $location)
                                <optgroup label="{{ $location->long }} ({{ $location->short }})">
                                @foreach($location->clusters as $cluster)

                                <option value="{{ $cluster->id }}"
                                    @if($location->id === old('location_id')) selected @endif
                                >{{ $cluster->name }}</option>

                                @endforeach
                                </optgroup>
                            @endforeach
                        </select>

                        <p class="small text-muted no-margin">The cluster which this server will be deployed to.</p>
                    </div>

                    <div class="form-group col-md-4">
                        <label for="pNodeSelectorFrom" class="form-label">Copy <em>Node Selector</em> From</label>
                        <select name="config_from" id="pNodeSelectorFrom" class="form-control">
                            <option value="">None</option>
                        </select>
                        <p class="text-muted small">
                            If you would like to add node selector values from an existing Rocket select it from the menu above. <br>
                            <em>To change the Launchpad scroll down to Configuration.</em>
                        </p>
                    </div>

                    <div class="form-group col-md-4">
                        <label for="pNodeSelector">Node Selector</label>
                        <textarea id="pNodeSelector" name="node_selectors" class="form-control" rows="4"></textarea>

                        <p class="small text-muted no-margin">
                            You can constrain a <b>Pod</b> so that it is <em>restricted</em> to run on particular node(s), or to prefer to run on particular nodes.
                            Example: <code>Key:Value</code> one per line
                        </p>
                    </div>

                    <div class="form-group col-md-12">
                        <label class="form-label">IP Allocation System</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pAllocationSystemAutomatic" value="automatic" name="allocation_system" checked>
                                <label for="pAllocationSystemAutomatic"> Automatic</label>
                            </div>

                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pAllocationSystemManual" value="manual" name="allocation_system" {{ (old('allocation_system', $cluster->allocation_system) == 'manual') ? 'checked' : '' }}>
                                <label for="pAllocationSystemManual"> Manual</label>
                            </div>
                        </div>
                        <p class="text-muted small">This feature allows you to select how IP address is assigned to your server.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="overlay" id="allocationLoader" style="display:none;"><i class="fa fa-refresh fa-spin"></i></div>
                <div class="box-header with-border">
                    <h3 class="box-title">Application Feature Limits</h3>
                </div>

                <div class="box-body row">
                    <div class="form-group col-xs-6">
                        <label for="pDatabaseLimit" class="control-label">Database Limit</label>
                        <div>
                            <input type="text" id="pDatabaseLimit" name="database_limit" class="form-control" value="{{ old('database_limit', 0) }}"/>
                        </div>
                        <p class="text-muted small">The total number of databases a user is allowed to create for this server.</p>
                    </div>
                    <div class="form-group col-xs-6">
                        <label for="pSnapshotLimit" class="control-label">Snapshot Limit</label>
                        <div>
                            <input type="text" id="pSnapshotLimit" name="snapshot_limit" class="form-control" value="{{ old('snapshot_limit', 0) }}"/>
                        </div>
                        <p class="text-muted small">The total number of snapshots that can be created for this server.</p>
                    </div>
                    <div class="form-group col-xs-6">
                        <label for="pAllocationLimit" class="control-label">Allocation Limit</label>
                        <div>
                            <input type="text" id="pAllocationLimit" name="allocation_limit" class="form-control" value="{{ old('allocation_limit', 0) }}"/>
                        </div>
                        <p class="text-muted small">The total number of allocations a user is allowed to create for this server.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Resource Management</h3>
                </div>

                <div class="box-body">
                    <div class="row">
                        <div class="form-group col-xs-6">
                            <label for="pCPULimit">CPU Limit</label>

                            <div class="input-group">
                                <input type="text" id="pCPULimit" name="cpu_limit" class="form-control" value="{{ old('cpu_limit', 0) }}" />
                                <span class="input-group-addon">%</span>
                            </div>

                            <p class="text-muted small">If you do not want to limit CPU usage, set the value to <code>0</code>. To determine a value, take the number of threads and multiply it by 100. For example, on a quad core system without hyperthreading <code>(4 * 100 = 400)</code> there is <code>400%</code> available. To limit a server to using half of a single thread, you would set the value to <code>50</code>. To allow a server to use up to two threads, set the value to <code>200</code>.<p>
                        </div>

                        <div class="form-group col-xs-6">
                            <label for="pCPURequest">CPU Request</label>

                            <div class="input-group">
                                <input type="text" id="pCPURequest" name="cpu_request" class="form-control" value="{{ old('cpu_request', 0) }}" />
                                <span class="input-group-addon">%</span>
                            </div>

                            <p class="text-muted small">Specifies the minimum amount of CPU resources that the pod requires. The value cannot be greater than the CPU limit or <code>0</code> if limit is specified.<p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-xs-6">
                            <label for="pMemoryLimit">Memory Limit</label>

                            <div class="input-group">
                                <input type="text" id="pMemoryLimit" name="memory_limit" class="form-control" value="{{ old('memory_limit', 128) }}" />
                                <span class="input-group-addon">MiB</span>
                            </div>

                            <p class="text-muted small">The maximum amount of memory resources that the pod can use. Minimum value is <code>128</code>.</p>
                        </div>

                        <div class="form-group col-xs-6">
                            <label for="pMemoryRequest">Memory Request</label>

                            <div class="input-group">
                                <input type="text" id="pMemoryRequest" name="memory_request" class="form-control" value="{{ old('memory_request', 64) }}" />
                                <span class="input-group-addon">MiB</span>
                            </div>

                            <p class="text-muted small">The minimum amount of memory resources that the pod requires. The value cannot be greater than the Memory limit or <code>0</code> if limit is specified.</p>
                        </div>
                    </div>
                </div>

                <div class="box-body row">
                    <div class="form-group col-xs-6">
                        <label for="pDisk">Disk Space</label>

                        <div class="input-group">
                            <input type="text" id="pDisk" name="disk" class="form-control" value="{{ old('disk', 128) }}" />
                            <span class="input-group-addon">MiB</span>
                        </div>

                        <p class="text-muted small">This server will not be allowed to boot if it is using more than this amount of space. If a server goes over this limit while running it will be safely stopped and locked until enough space is available. Minimum value is <code>128</code>.</p>
                    </div>

                    <div class="form-group col-xs-6">
                        <label for="pStorageClass">Override Storage Class</label>
                        <div>
                            <input type="text" id="pStorageClass" name="storage_class" class="form-control" placeholder="rook-ceph-block" value="{{ old('storage_class') }}" />
                        </div>
                        <p class="text-muted small">Specify a storage class name to override the default storage class. If left blank, the default storage class defined in Cluster settings will be used.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Launchpad Configuration</h3>
                </div>

                <div class="box-body row">
                    <div class="form-group col-xs-12">
                        <label for="pLaunchpadId">Launchpad</label>

                        <select id="pLaunchpadId" name="launchpad_id" class="form-control">
                            @foreach($launchpads as $launchpad)
                                <option value="{{ $launchpad->id }}"
                                    @if($launchpad->id === old('launchpad_id'))
                                        selected="selected"
                                    @endif
                                >{{ $launchpad->name }}</option>
                            @endforeach
                        </select>

                        <p class="small text-muted no-margin">Select the Launchpad that this server will be grouped under.</p>
                    </div>

                    <div class="form-group col-xs-12">
                        <label for="pRocketId">Rocket</label>
                        <select id="pRocketId" name="rocket_id" class="form-control"></select>
                        <p class="small text-muted no-margin">Select the Rocket that will define how this server should operate.</p>
                    </div>
                    <div class="form-group col-xs-12">
                        <div class="checkbox checkbox-primary no-margin-bottom">
                            <input type="checkbox" id="pSkipScripting" name="skip_scripts" value="1" {{ \Kubectyl\Helpers\Utilities::checked('skip_scripts', 0) }} />
                            <label for="pSkipScripting" class="strong">Skip Rocket Install Script</label>
                        </div>

                        <p class="small text-muted no-margin">If the selected Rocket has an install script attached to it, the script will run during the install. If you would like to skip this step, check this box.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Docker Configuration</h3>
                </div>

                <div class="box-body row">
                    <div class="form-group col-xs-12">
                        <label for="pDefaultContainer">Docker Image</label>
                        <select id="pDefaultContainer" name="image" class="form-control"></select>
                        <input id="pDefaultContainerCustom" name="custom_image" value="{{ old('custom_image') }}" class="form-control" placeholder="Or enter a custom image..." style="margin-top:1rem"/>
                        <p class="small text-muted no-margin">This is the default Docker image that will be used to run this server. Select an image from the dropdown above, or enter a custom image in the text field above.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Startup Configuration</h3>
                </div>

                <div class="box-body row">
                    <div class="form-group col-xs-12">
                        <label for="pStartup">Startup Command</label>
                        <input type="text" id="pStartup" name="startup" value="{{ old('startup') }}" class="form-control" />
                        <p class="small text-muted no-margin">The following data substitutes are available for the startup command: <code>@{{SERVER_MEMORY}}</code>, <code>@{{SERVER_IP}}</code>, and <code>@{{SERVER_PORT}}</code>. They will be replaced with the allocated memory, server IP, and server port respectively.</p>
                    </div>
                </div>

                <div class="box-header with-border" style="margin-top:-10px;">
                    <h3 class="box-title">Service Variables</h3>
                </div>

                <div class="box-body row" id="appendVariablesTo"></div>

                <div class="box-footer">
                    {!! csrf_field() !!}
                    <input type="submit" class="btn btn-success pull-right" value="Create Server" />
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    {!! Theme::js('vendor/lodash/lodash.js') !!}

    <script type="application/javascript">
        // Persist 'Service Variables'
        function serviceVariablesUpdated(rocketId, ids) {
            @if (old('rocket_id'))
                // Check if the rocket id matches.
                if (rocketId != '{{ old('rocket_id') }}') {
                    return;
                }

                @if (old('environment'))
                    @foreach (old('environment') as $key => $value)
                        $('#' + ids['{{ $key }}']).val('{{ $value }}');
                    @endforeach
                @endif
            @endif
            @if(old('image'))
                $('#pDefaultContainer').val('{{ old('image') }}');
            @endif
        }
        // END Persist 'Service Variables'
    </script>

    {!! Theme::js('js/admin/new-server.js?v=20220530') !!}

    <script type="application/javascript">
        $(document).ready(function() {
            // Persist 'Server Owner' select2
            @if (old('owner_id'))
                $.ajax({
                    url: '/admin/users/accounts.json?user_id={{ old('owner_id') }}',
                    dataType: 'json',
                }).then(function (data) {
                    initUserIdSelect([ data ]);
                });
            @else
                initUserIdSelect();
            @endif
            // END Persist 'Server Owner' select2

            // Persist 'Cluster' select2
            @if (old('cluster_id'))
                $('#pClusterId').val('{{ old('cluster_id') }}').change();

                // Persist 'Default Allocation' select2
                @if (old('allocation_id'))
                    $('#pAllocation').val('{{ old('allocation_id') }}').change();
                @endif
                // END Persist 'Default Allocation' select2
                // Persist 'Additional Allocations' select2
                @if (old('allocation_additional'))
                    const additional_allocations = [];
                    @for ($i = 0; $i < count(old('allocation_additional')); $i++)
                        additional_allocations.push('{{ old('allocation_additional.'.$i)}}');
                    @endfor
                    $('#pAllocationAdditional').val(additional_allocations).change();
                @endif
                // END Persist 'Additional Allocations' select2

                // Persist 'Default Allocation' select2
                @if (old('default_port'))
                    $('#pDefaultPort').val('{{ old('default_port') }}').change();
                @endif
                // END Persist 'Default Allocation' select2

                // Persist 'Additional Allocations' select2
                @if (old('additional_ports'))
                    const additional_ports = [];

                    @for ($i = 0; $i < count(old('additional_ports')); $i++)
                        additional_ports.push('{{ old('additional_ports.'.$i)}}');
                    @endfor

                    $('#pAdditionalPorts').val(additional_ports).change();
                @endif
                // END Persist 'Additional Allocations' select2
            @endif
            // END Persist 'Cluster' select2

            // Persist 'Launchpad' select2
            @if (old('launchpad_id'))
                $('#pLaunchpadId').val('{{ old('launchpad_id') }}').change();

                // Persist 'Rocket' select2
                @if (old('rocket_id'))
                    $('#pRocketId').val('{{ old('rocket_id') }}').change();
                @endif
                // END Persist 'Rocket' select2
            @endif
            // END Persist 'Launchpad' select2
        });
    </script>
@endsection
