@extends('layouts.admin')

@section('title')
    Server — {{ $server->name }}: Startup
@endsection

@section('content-header')
    <h1>{{ $server->name }}<small>Control startup command as well as variables.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.servers') }}">Servers</a></li>
        <li><a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a></li>
        <li class="active">Startup</li>
    </ol>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
<form action="{{ route('admin.servers.view.startup', $server->id) }}" method="POST">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Startup Command Modification</h3>
                </div>
                <div class="box-body">
                    <label for="pStartup" class="form-label">Startup Command</label>
                    <input id="pStartup" name="startup" class="form-control" type="text" value="{{ old('startup', $server->startup) }}" />
                    <p class="small text-muted">Edit your server's startup command here. The following variables are available by default: <code>@{{SERVER_MEMORY}}</code>, <code>@{{SERVER_IP}}</code>, and <code>@{{SERVER_PORT}}</code>.</p>
                </div>
                <div class="box-body">
                    <label for="pDefaultStartupCommand" class="form-label">Default Service Start Command</label>
                    <input id="pDefaultStartupCommand" class="form-control" type="text" readonly />
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-primary btn-sm pull-right">Save Modifications</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Service Configuration</h3>
                </div>
                <div class="box-body row">
                    <div class="col-xs-12">
                        <p class="small text-danger">
                            Changing any of the below values will result in the server processing a re-install command. The server will be stopped and will then proceed.
                            If you would like the service scripts to not run, ensure the box is checked at the bottom.
                        </p>
                        <p class="small text-danger">
                            <strong>This is a destructive operation in many cases. This server will be stopped immediately in order for this action to proceed.</strong>
                        </p>
                    </div>
                    <div class="form-group col-xs-12">
                        <label for="pLaunchpadId">Launchpad</label>
                        <select name="launchpad_id" id="pLaunchpadId" class="form-control">
                            @foreach($launchpads as $launchpad)
                                <option value="{{ $launchpad->id }}"
                                    @if($launchpad->id === $server->launchpad_id)
                                        selected
                                    @endif
                                >{{ $launchpad->name }}</option>
                            @endforeach
                        </select>
                        <p class="small text-muted no-margin">Select the Launchpad that this server will be grouped into.</p>
                    </div>
                    <div class="form-group col-xs-12">
                        <label for="pRocketId">Rocket</label>
                        <select name="rocket_id" id="pRocketId" class="form-control"></select>
                        <p class="small text-muted no-margin">Select the Rocket that will provide processing data for this server.</p>
                    </div>
                    <div class="form-group col-xs-12">
                        <div class="checkbox checkbox-primary no-margin-bottom">
                            <input id="pSkipScripting" name="skip_scripts" type="checkbox" value="1" @if($server->skip_scripts) checked @endif />
                            <label for="pSkipScripting" class="strong">Skip Rocket Install Script</label>
                        </div>
                        <p class="small text-muted no-margin">If the selected Rocket has an install script attached to it, the script will run during install. If you would like to skip this step, check this box.</p>
                    </div>
                </div>
            </div>
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Docker Image Configuration</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label for="pDockerImage">Image</label>
                        <select id="pDockerImage" name="docker_image" class="form-control"></select>
                        <input id="pDockerImageCustom" name="custom_docker_image" value="{{ old('custom_docker_image') }}" class="form-control" placeholder="Or enter a custom image..." style="margin-top:1rem"/>
                        <p class="small text-muted no-margin">This is the Docker image that will be used to run this server. Select an image from the dropdown or enter a custom image in the text field above.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="row" id="appendVariablesTo"></div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    {!! Theme::js('vendor/lodash/lodash.js') !!}
    <script>
    $(document).ready(function () {
        $('#pRocketId').select2({placeholder: 'Select a Launchpad Rocket'}).on('change', function () {
            var selectedRocket = _.isNull($(this).val()) ? $(this).find('option').first().val() : $(this).val();
            var parentChain = _.get(Kubectyl.launchpads, $("#pLaunchpadId").val());
            var objectChain = _.get(parentChain, 'rockets.' + selectedRocket);

            const images = _.get(objectChain, 'docker_images', [])
            $('#pDockerImage').html('');
            const keys = Object.keys(images);
            for (let i = 0; i < keys.length; i++) {
                let opt = document.createElement('option');
                opt.value = images[keys[i]];
                opt.innerHTML = keys[i] + " (" + images[keys[i]] + ")";
                if (objectChain.id === parseInt(Kubectyl.server.rocket_id) && Kubectyl.server.image == opt.value) {
                    opt.selected = true
                }
                $('#pDockerImage').append(opt);
            }
            $('#pDockerImage').on('change', function () {
                $('#pDockerImageCustom').val('');
            })

            if (objectChain.id === parseInt(Kubectyl.server.rocket_id)) {
                if ($('#pDockerImage').val() != Kubectyl.server.image) {
                    $('#pDockerImageCustom').val(Kubectyl.server.image);
                }
            }

            if (!_.get(objectChain, 'startup', false)) {
                $('#pDefaultStartupCommand').val(_.get(parentChain, 'startup', 'ERROR: Startup Not Defined!'));
            } else {
                $('#pDefaultStartupCommand').val(_.get(objectChain, 'startup'));
            }

            $('#appendVariablesTo').html('');
            $.each(_.get(objectChain, 'variables', []), function (i, item) {
                var setValue = _.get(Kubectyl.server_variables, item.env_variable, item.default_value);
                var isRequired = (item.required === 1) ? '<span class="label label-danger">Required</span> ' : '';
                var dataAppend = ' \
                    <div class="col-xs-12"> \
                        <div class="box"> \
                            <div class="box-header with-border"> \
                                <h3 class="box-title">' + isRequired + item.name + '</h3> \
                            </div> \
                            <div class="box-body"> \
                                <input name="environment[' + item.env_variable + ']" class="form-control" type="text" id="rocket_variable_' + item.env_variable + '" /> \
                                <p class="no-margin small text-muted">' + item.description + '</p> \
                            </div> \
                            <div class="box-footer"> \
                                <p class="no-margin text-muted small"><strong>Startup Command Variable:</strong> <code>' + item.env_variable + '</code></p> \
                                <p class="no-margin text-muted small"><strong>Input Rules:</strong> <code>' + item.rules + '</code></p> \
                            </div> \
                        </div> \
                    </div>';
                $('#appendVariablesTo').append(dataAppend).find('#rocket_variable_' + item.env_variable).val(setValue);
            });
        });

        $('#pLaunchpadId').select2({placeholder: 'Select a Launchpad'}).on('change', function () {
            $('#pRocketId').html('').select2({
                data: $.map(_.get(Kubectyl.launchpads, $(this).val() + '.rockets', []), function (item) {
                    return {
                        id: item.id,
                        text: item.name,
                    };
                }),
            });

            if (_.isObject(_.get(Kubectyl.launchpads, $(this).val() + '.rockets.' + Kubectyl.server.rocket_id))) {
                $('#pRocketId').val(Kubectyl.server.rocket_id);
            }

            $('#pRocketId').change();
        }).change();
    });
    </script>
@endsection
