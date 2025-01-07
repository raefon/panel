@extends('layouts.admin')

@section('title')
    Launchpads
@endsection

@section('content-header')
    <h1>Launchpads<small> are responsible for launching and managing containers.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Launchpads</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="alert alert-danger">
            Rockets are a powerful feature of Kubectyl Panel that allow for extreme flexibility and configuration. Please note that while powerful, modifying a rocket wrongly can very easily brick your servers and cause more problems. Please avoid editing our default rockets — those provided by <code>support@kubectyl.org</code> — unless you are absolutely sure of what you are doing.
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header with-border">
                <h3 class="box-title">Configured Launchpads</h3>
                <div class="box-tools">
                    <a href="#" class="btn btn-sm btn-success" data-toggle="modal" data-target="#importServiceOptionModal" role="button"><i class="fa fa-upload"></i> Import Rocket</a>
                    <a href="{{ route('admin.launchpads.new') }}" class="btn btn-primary btn-sm">Create New</a>
                </div>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th class="text-center">Rockets</th>
                        <th class="text-center">Servers</th>
                    </tr>
                    @foreach($launchpads as $launchpad)
                        <tr>
                            <td class="middle"><code>{{ $launchpad->id }}</code></td>
                            <td class="middle"><a href="{{ route('admin.launchpads.view', $launchpad->id) }}" data-toggle="tooltip" data-placement="right" title="{{ $launchpad->author }}">{{ $launchpad->name }}</a></td>
                            <td class="col-xs-6 middle">{{ $launchpad->description }}</td>
                            <td class="text-center middle">{{ $launchpad->rockets_count }}</td>
                            <td class="text-center middle">{{ $launchpad->servers_count }}</td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" role="dialog" id="importServiceOptionModal">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Import a Rocket</h4>
            </div>
            <form action="{{ route('admin.launchpads.rocket.import') }}" enctype="multipart/form-data" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label" for="pImportFile">Rocket File <span class="field-required"></span></label>
                        <div>
                            <input id="pImportFile" type="file" name="import_file" class="form-control" accept="application/json" />
                            <p class="small text-muted">Select the <code>.json</code> file for the new rocket that you wish to import.</p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label" for="pImportToLaunchpad">Associated Launchpad <span class="field-required"></span></label>
                        <div>
                            <select id="pImportToLaunchpad" name="import_to_launchpad">
                                @foreach($launchpads as $launchpad)
                                   <option value="{{ $launchpad->id }}">{{ $launchpad->name }} &lt;{{ $launchpad->author }}&gt;</option>
                                @endforeach
                            </select>
                            <p class="small text-muted">Select the launchpad that this rocket will be associated with from the dropdown. If you wish to associate it with a new launchpad you will need to create that launchpad before continuing.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    {{ csrf_field() }}
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('#pImportToLaunchpad').select2();
        });
    </script>
@endsection
