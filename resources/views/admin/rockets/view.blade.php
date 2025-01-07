@extends('layouts.admin')

@section('title')
    Launchpads &rarr; Rocket: {{ $rocket->name }}
@endsection

@section('content-header')
    <h1>{{ $rocket->name }}<small>{{ str_limit($rocket->description, 50) }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.launchpads') }}">Launchpads</a></li>
        <li><a href="{{ route('admin.launchpads.view', $rocket->launchpad->id) }}">{{ $rocket->launchpad->name }}</a></li>
        <li class="active">{{ $rocket->name }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="nav-tabs-custom nav-tabs-floating">
            <ul class="nav nav-tabs">
                <li class="active"><a href="{{ route('admin.launchpads.rocket.view', $rocket->id) }}">Configuration</a></li>
                <li><a href="{{ route('admin.launchpads.rocket.variables', $rocket->id) }}">Variables</a></li>
                <li><a href="{{ route('admin.launchpads.rocket.scripts', $rocket->id) }}">Install Script</a></li>
            </ul>
        </div>
    </div>
</div>
<form action="{{ route('admin.launchpads.rocket.view', $rocket->id) }}" enctype="multipart/form-data" method="POST">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-danger">
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-8">
                            <div class="form-group no-margin-bottom">
                                <label for="pName" class="control-label">Rocket File</label>
                                <div>
                                    <input type="file" name="import_file" class="form-control" style="border: 0;margin-left:-10px;" />
                                    <p class="text-muted small no-margin-bottom">If you would like to replace settings for this Rocket by uploading a new JSON file, simply select it here and press "Update Rocket". This will not change any existing startup strings or Docker images for existing servers.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            {!! csrf_field() !!}
                            <button type="submit" name="_method" value="PUT" class="btn btn-sm btn-danger pull-right">Update Rocket</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<form action="{{ route('admin.launchpads.rocket.view', $rocket->id) }}" method="POST">
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Configuration</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pName" class="control-label">Name <span class="field-required"></span></label>
                                <input type="text" id="pName" name="name" value="{{ $rocket->name }}" class="form-control" />
                                <p class="text-muted small">A simple, human-readable name to use as an identifier for this Rocket.</p>
                            </div>
                            <div class="form-group">
                                <label for="pUuid" class="control-label">UUID</label>
                                <input type="text" id="pUuid" readonly value="{{ $rocket->uuid }}" class="form-control" />
                                <p class="text-muted small">This is the globally unique identifier for this Rocket which the Daemon uses as an identifier.</p>
                            </div>
                            <div class="form-group">
                                <label for="pAuthor" class="control-label">Author</label>
                                <input type="text" id="pAuthor" readonly value="{{ $rocket->author }}" class="form-control" />
                                <p class="text-muted small">The author of this version of the Rocket. Uploading a new Rocket configuration from a different author will change this.</p>
                            </div>
                            <div class="form-group">
                                <label for="pDockerImage" class="control-label">Docker Images <span class="field-required"></span></label>
                                <textarea id="pDockerImages" name="docker_images" class="form-control" rows="4">{{ implode(PHP_EOL, $images) }}</textarea>
                                <p class="text-muted small">
                                    The docker images available to servers using this rocket. Enter one per line. Users
                                    will be able to select from this list of images if more than one value is provided.
                                    Optionally, a display name may be provided by prefixing the image with the name
                                    followed by a pipe character, and then the image URL. Example: <code>Display Name|ghcr.io/my/rocket</code>
                                </p>
                            </div>
                            <div class="form-group">
                                <label for="pNodeSelector" class="control-label">Node Selector</label>
                                <div> 
                                    <textarea id="pNodeSelector" name="node_selectors" class="form-control" rows="4">{{ implode(PHP_EOL, $selectors) }}</textarea>
                                    <p class="small text-muted no-margin">
                                        You can constrain <b>Pods</b> grouped under this Launchpad so that they are <em>restricted</em> to run on particular node(s), or to prefer to run on particular nodes.
                                        Example: <code>Key:Value</code> one per line
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pDescription" class="control-label">Description</label>
                                <textarea id="pDescription" name="description" class="form-control" rows="8">{{ $rocket->description }}</textarea>
                                <p class="text-muted small">A description of this Rocket that will be displayed throughout the Panel as needed.</p>
                            </div>
                            <div class="form-group">
                                <label for="pStartup" class="control-label">Startup Command <span class="field-required"></span></label>
                                <textarea id="pStartup" name="startup" class="form-control" rows="8">{{ $rocket->startup }}</textarea>
                                <p class="text-muted small">The default startup command that should be used for new servers using this Rocket.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Process Management</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="alert alert-warning">
                                <p>The following configuration options should not be edited unless you understand how this system works. If wrongly modified it is possible for the daemon to break.</p>
                                <p>All fields are required unless you select a separate option from the 'Copy Settings From' dropdown, in which case fields may be left blank to use the values from that Rocket.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pConfigFrom" class="form-label">Copy Settings From</label>
                                <select name="config_from" id="pConfigFrom" class="form-control">
                                    <option value="">None</option>
                                    @foreach($rocket->launchpad->rockets as $o)
                                        <option value="{{ $o->id }}" {{ ($rocket->config_from !== $o->id) ?: 'selected' }}>{{ $o->name }} &lt;{{ $o->author }}&gt;</option>
                                    @endforeach
                                </select>
                                <p class="text-muted small">If you would like to default to settings from another Rocket select it from the menu above.</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigStop" class="form-label">Stop Command</label>
                                <input type="text" id="pConfigStop" name="config_stop" class="form-control" value="{{ $rocket->config_stop }}" />
                                <p class="text-muted small">The command that should be sent to server processes to stop them gracefully. If you need to send a <code>SIGINT</code> you should enter <code>^C</code> here.</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigLogs" class="form-label">Log Configuration</label>
                                <textarea data-action="handle-tabs" id="pConfigLogs" name="config_logs" class="form-control" rows="6">{{ ! is_null($rocket->config_logs) ? json_encode(json_decode($rocket->config_logs), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '' }}</textarea>
                                <p class="text-muted small">This should be a JSON representation of where log files are stored, and whether or not the daemon should be creating custom logs.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pConfigFiles" class="form-label">Configuration Files</label>
                                <textarea data-action="handle-tabs" id="pConfigFiles" name="config_files" class="form-control" rows="6">{{ ! is_null($rocket->config_files) ? json_encode(json_decode($rocket->config_files), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '' }}</textarea>
                                <p class="text-muted small">This should be a JSON representation of configuration files to modify and what parts should be changed.</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigStartup" class="form-label">Start Configuration</label>
                                <textarea data-action="handle-tabs" id="pConfigStartup" name="config_startup" class="form-control" rows="6">{{ ! is_null($rocket->config_startup) ? json_encode(json_decode($rocket->config_startup), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '' }}</textarea>
                                <p class="text-muted small">This should be a JSON representation of what values the daemon should be looking for when booting a server to determine completion.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <button type="submit" name="_method" value="PATCH" class="btn btn-primary btn-sm pull-right">Save</button>
                    <a href="{{ route('admin.launchpads.rocket.export', $rocket->id) }}" class="btn btn-sm btn-info pull-right" style="margin-right:10px;">Export</a>
                    <button id="deleteButton" type="submit" name="_method" value="DELETE" class="btn btn-danger btn-sm muted muted-hover">
                        <i class="fa fa-trash-o"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    <script>
    $('#pConfigFrom').select2();
    $('#deleteButton').on('mouseenter', function (event) {
        $(this).find('i').html(' Delete Rocket');
    }).on('mouseleave', function (event) {
        $(this).find('i').html('');
    });
    $('textarea[data-action="handle-tabs"]').on('keydown', function(event) {
        if (event.keyCode === 9) {
            event.preventDefault();

            var curPos = $(this)[0].selectionStart;
            var prepend = $(this).val().substr(0, curPos);
            var append = $(this).val().substr(curPos);

            $(this).val(prepend + '    ' + append);
        }
    });

    $('#pAdditionalPorts').select2({
        tags: true,
        selectOnClose: true,
        tokenSeparators: [',', ' '],
    });

    @if (old('additional_ports'))
        const additional_ports = [];

        @for ($i = 0; $i < count(old('additional_ports')); $i++)
                additional_ports.push('{{ old('additional_ports.'.$i)}}');
        @endfor

        $('#pAdditionalPorts').val(additional_ports).change();
    @endif
    </script>
@endsection
