@extends('layouts.admin')

@section('title')
    Server — {{ $server->name }}: Build Details
@endsection

@section('content-header')
    <h1>{{ $server->name }}<small>Control allocations and system resources for this server.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.servers') }}">Servers</a></li>
        <li><a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a></li>
        <li class="active">Build Configuration</li>
    </ol>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
<div class="row">
    <form action="{{ route('admin.servers.view.build', $server->id) }}" method="POST">
        <div class="col-sm-5">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Resource Management</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label for="cpu_request" class="control-label">CPU Request</label>
                        <div class="input-group">
                            <input type="text" id="pCPURequest" name="cpu_request" class="form-control" value="{{ old('cpu_request', $server->cpu_request) }}"/>
                            <span class="input-group-addon">%</span>
                        </div>
                        <p class="text-muted small">Specifies the minimum amount of CPU resources that the pod requires. The value cannot be greater than the CPU limit or <code>0</code> if limit is specified.<p>
                    </div>
                    <div class="form-group">
                        <label for="cpu_limit" class="control-label">CPU Limit</label>
                        <div class="input-group">
                            <input type="text" id="pCPULimit" name="cpu_limit" class="form-control" value="{{ old('cpu_limit', $server->cpu_limit) }}"/>
                            <span class="input-group-addon">%</span>
                        </div>
                        <p class="text-muted small">Each <em>virtual</em> core (thread) on the system is considered to be <code>100%</code>. Setting this value to <code>0</code> will allow a server to use CPU time without restrictions.</p>
                    </div>
                    <div class="form-group">
                        <label for="memory_request" class="control-label">Memory Request</label>
                        <div class="input-group">
                            <input type="text" id="pMemoryRequest" name="memory_request" data-multiplicator="true" class="form-control" value="{{ old('memory_request', $server->memory_request) }}"/>
                            <span class="input-group-addon">MiB</span>
                        </div>
                        <p class="text-muted small">The minimum amount of memory resources that the pod requires. The value cannot be greater than the Memory limit or <code>0</code> if limit is specified.</p>
                    </div>
                    <div class="form-group">
                        <label for="memory_limit" class="control-label">Memory Limit</label>
                        <div class="input-group">
                            <input type="text" id="pMemoryLimit" name="memory_limit" data-multiplicator="true" class="form-control" value="{{ old('memory_limit', $server->memory_limit) }}"/>
                            <span class="input-group-addon">MiB</span>
                        </div>
                        <p class="text-muted small">The maximum amount of memory resources that the pod can use. Minimum value is <code>128</code>.</p>
                    </div>
                    <div class="form-group">
                        <label for="cpu" class="control-label">Disk Space Limit</label>
                        <div class="input-group">
                            <input type="text" name="disk" class="form-control" value="{{ old('disk', $server->disk) }}"/>
                            <span class="input-group-addon">MiB</span>
                        </div>
                        <p class="text-muted small">This server will not be allowed to boot if it is using more than this amount of space. If a server goes over this limit while running it will be safely stopped and locked until enough space is available. Minimum value is <code>128</code>.</p>
                    </div>
                    <div class="form-group">
                        <label for="pStorageClass">Override Storage Class</label>
                        <div>
                            <input type="text" id="pStorageClass" name="storage_class" class="form-control" placeholder="rook-ceph-block" value="{{ old('storage_class', $server->storage_class) }}" />
                        </div>
                        <p class="text-muted small">Specify a storage class name to override the default storage class. If left blank, the default storage class defined in Cluster settings will be used.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-7">
            <div class="row">
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Application Feature Limits</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="form-group col-xs-6">
                                    <label for="database_limit" class="control-label">Database Limit</label>
                                    <div>
                                        <input type="text" name="database_limit" class="form-control" value="{{ old('database_limit', $server->database_limit) }}"/>
                                    </div>
                                    <p class="text-muted small">The total number of databases a user is allowed to create for this server.</p>
                                </div>
                                <div class="form-group col-xs-6">
                                    <label for="allocation_limit" class="control-label">Allocation Limit</label>
                                    <div>
                                        <input type="text" name="allocation_limit" class="form-control" value="{{ old('allocation_limit', $server->allocation_limit) }}"/>
                                    </div>
                                    <p class="text-muted small">The total number of allocations a user is allowed to create for this server.</p>
                                </div>
                                <div class="form-group col-xs-6">
                                    <label for="snapshot_limit" class="control-label">Snapshot Limit</label>
                                    <div>
                                        <input type="text" name="snapshot_limit" class="form-control" value="{{ old('snapshot_limit', $server->snapshot_limit) }}"/>
                                    </div>
                                    <p class="text-muted small">The total number of snapshots that can be created for this server.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Allocation Management</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="form-group col-xs-12">
                                    <label for="pNodeSelector" class="control-label">Node Selector</label>
                                    <div>
                                        <textarea id="pNodeSelector" name="node_selectors" class="form-control" rows="4">{{ implode(PHP_EOL, $selectors) }}</textarea>
                                        <p class="small text-muted no-margin">
                                            You can constrain a <b>Pod</b> so that it is <em>restricted</em> to run on particular node(s), or to prefer to run on particular nodes.
                                            Example: <code>Key:Value</code> one per line
                                        </p>
                                    </div>
                                </div>
                                @if ($server->allocation_id == null)
                                    <div class="form-group col-xs-6">
                                        <label for="pDefaultPort" class="control-label">Default Port</label>
                                        <div>
                                            <input type="text" name="default_port" class="form-control" value="{{ old('default_port', $server->default_port) }}"/>
                                        </div>
                                        <p class="text-muted small">The default connection address that will be used for this game server.</p>
                                    </div>
                                    <div class="form-group col-xs-6">
                                        <label for="pAdditionalPorts" class="control-label">Assign Additional Ports</label>
                                        <div>
                                            <select name="add_ports[]" class="form-control" multiple id="pAdditionalPorts">

                                            </select>
                                        </div>
                                        <p class="text-muted small">Please note that due to software limitations you cannot assign identical ports on different IPs to the same server.</p>
                                    </div>
                                    <div class="form-group col-xs-6">
                                        <label for="pRemovePorts" class="control-label">Remove Additional Ports</label>
                                        <div>
                                            <select name="remove_ports[]" class="form-control" multiple id="pRemovePorts">
                                            @if ($ports != null)
                                                @foreach ($ports as $port)
                                                    <option value="{{ $port }}">{{ $port }}</option>
                                                @endforeach
                                            @endif
                                            </select>
                                        </div>
                                        <p class="text-muted small">Simply select which ports you would like to remove from the list above. If you want to assign a port on a different IP that is already in use you can select it from the left and delete it here.</p>
                                    </div>
                                @else
                                <div class="form-group col-xs-6">
                                    <label for="pAllocation" class="control-label">Game Port</label>
                                    <select id="pAllocation" name="allocation_id" class="form-control">
                                        @foreach ($assigned as $assignment)
                                            <option value="{{ $assignment->id }}"
                                                @if($assignment->id === $server->allocation_id)
                                                    selected="selected"
                                                @endif
                                            >{{ $assignment->alias }}:{{ $assignment->port }}</option>
                                        @endforeach
                                    </select>
                                    <p class="text-muted small">The default connection address that will be used for this game server.</p>
                                </div>
                                <div class="form-group col-xs-6">
                                    <label for="pAddAllocations" class="control-label">Assign Additional Ports</label>
                                    <div>
                                        <select name="add_allocations[]" class="form-control" multiple id="pAddAllocations">
                                            @foreach ($unassigned as $assignment)
                                                <option value="{{ $assignment->id }}">{{ $assignment->alias }}:{{ $assignment->port }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <p class="text-muted small">Please note that due to software limitations you cannot assign identical ports on different IPs to the same server.</p>
                                </div>
                                <div class="form-group col-xs-6">
                                    <label for="pRemoveAllocations" class="control-label">Remove Additional Ports</label>
                                    <div>
                                        <select name="remove_allocations[]" class="form-control" multiple id="pRemoveAllocations">
                                            @foreach ($assigned as $assignment)
                                                <option value="{{ $assignment->id }}">{{ $assignment->alias }}:{{ $assignment->port }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <p class="text-muted small">Simply select which ports you would like to remove from the list above. If you want to assign a port on a different IP that is already in use you can select it from the left and delete it here.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="box-footer">
                            {!! csrf_field() !!}
                            <button type="submit" class="btn btn-primary pull-right">Update Build Configuration</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    $('#pAdditionalPorts').select2({
            tags: true,
            selectOnClose: true,
            tokenSeparators: [',', ' '],
        });
    $('#pAddAllocations').select2();
    $('#pRemovePorts').select2();
    $('#pRemoveAllocations').select2();
    $('#pAllocation').select2();

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
    </script>
@endsection
