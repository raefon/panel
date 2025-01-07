@extends('layouts.admin')

@section('title')
    Launchpads &rarr; New Rocket
@endsection

@section('content-header')
    <h1>New Rocket<small>Create a new Rocket to assign to servers.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.launchpads') }}">Launchpads</a></li>
        <li class="active">New Rocket</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('admin.launchpads.rocket.new') }}" method="POST">
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
                                <label for="pLaunchpadId" class="form-label">Associated Launchpad</label>
                                <div>
                                    <select name="launchpad_id" id="pLaunchpadId">
                                        @foreach($launchpads as $launchpad)
                                            <option value="{{ $launchpad->id }}" {{ old('launchpad_id') != $launchpad->id ?: 'selected' }}>{{ $launchpad->name }} &lt;{{ $launchpad->author }}&gt;</option>
                                        @endforeach
                                    </select>
                                    <p class="text-muted small">Think of a Launchpad as a category. You can put multiple Rockets in a launchpad, but consider putting only Rockets that are related to each other in each Launchpad.</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="pName" class="form-label">Name</label>
                                <input type="text" id="pName" name="name" value="{{ old('name') }}" class="form-control" />
                                <p class="text-muted small">A simple, human-readable name to use as an identifier for this Rocket. This is what users will see as their game server type.</p>
                            </div>
                            <div class="form-group">
                                <label for="pDescription" class="form-label">Description</label>
                                <textarea id="pDescription" name="description" class="form-control" rows="8">{{ old('description') }}</textarea>
                                <p class="text-muted small">A description of this Rocket.</p>
                            </div>
                            <div class="form-group">
                                <label for="pNodeSelector" class="control-label">Node Selector</label>
                                <div> 
                                    <textarea id="pNodeSelector" name="node_selectors" class="form-control" rows="4">{{ old('node_selectors') }}</textarea>
                                    <p class="small text-muted no-margin">
                                        You can constrain <b>Pods</b> grouped under this Launchpad so that they are <em>restricted</em> to run on particular node(s), or to prefer to run on particular nodes.
                                        Example: <code>Key:Value</code> one per line
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pDockerImage" class="control-label">Docker Images</label>
                                <textarea id="pDockerImages" name="docker_images" rows="4" placeholder="quay.io/kubectyl/service" class="form-control">{{ old('docker_images') }}</textarea>
                                <p class="text-muted small">The docker images available to servers using this rpclet. Enter one per line. Users will be able to select from this list of images if more than one value is provided.</p>
                            </div>
                            <div class="form-group">
                                <label for="pStartup" class="control-label">Startup Command</label>
                                <textarea id="pStartup" name="startup" class="form-control" rows="10">{{ old('startup') }}</textarea>
                                <p class="text-muted small">The default startup command that should be used for new servers created with this Rocket. You can change this per-server as needed.</p>
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
                                <p>All fields are required unless you select a separate option from the 'Copy Settings From' dropdown, in which case fields may be left blank to use the values from that option.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pConfigFrom" class="form-label">Copy Settings From</label>
                                <select name="config_from" id="pConfigFrom" class="form-control">
                                    <option value="">None</option>
                                </select>
                                <p class="text-muted small">If you would like to default to settings from another Rocket select it from the dropdown above.</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigStop" class="form-label">Stop Command</label>
                                <input type="text" id="pConfigStop" name="config_stop" class="form-control" value="{{ old('config_stop') }}" />
                                <p class="text-muted small">The command that should be sent to server processes to stop them gracefully. If you need to send a <code>SIGINT</code> you should enter <code>^C</code> here.</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigLogs" class="form-label">Log Configuration</label>
                                <textarea data-action="handle-tabs" id="pConfigLogs" name="config_logs" class="form-control" rows="6">{{ old('config_logs') }}</textarea>
                                <p class="text-muted small">This should be a JSON representation of where log files are stored, and whether or not the daemon should be creating custom logs.</p>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="form-group">
                                <label for="pConfigFiles" class="form-label">Configuration Files</label>
                                <textarea data-action="handle-tabs" id="pConfigFiles" name="config_files" class="form-control" rows="6">{{ old('config_files') }}</textarea>
                                <p class="text-muted small">This should be a JSON representation of configuration files to modify and what parts should be changed.</p>
                            </div>
                            <div class="form-group">
                                <label for="pConfigStartup" class="form-label">Start Configuration</label>
                                <textarea data-action="handle-tabs" id="pConfigStartup" name="config_startup" class="form-control" rows="6">{{ old('config_startup') }}</textarea>
                                <p class="text-muted small">This should be a JSON representation of what values the daemon should be looking for when booting a server to determine completion.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-success btn-sm pull-right">Create</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    {!! Theme::js('vendor/lodash/lodash.js') !!}
    <script>
    $(document).ready(function() {
        $('#pLaunchpadId').select2().change();
        $('#pConfigFrom').select2();
    });
    $('#pLaunchpadId').on('change', function (event) {
        $('#pConfigFrom').html('<option value="">None</option>').select2({
            data: $.map(_.get(Kubectyl.launchpads, $(this).val() + '.rockets', []), function (item) {
                return {
                    id: item.id,
                    text: item.name + ' <' + item.author + '>',
                };
            }),
        });
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
    </script>
@endsection
