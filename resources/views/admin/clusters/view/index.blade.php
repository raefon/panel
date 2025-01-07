@extends('layouts.admin')

@section('title')
    {{ $cluster->name }}
@endsection

@section('content-header')
    <h1>{{ $cluster->name }}<small>A quick overview of your cluster.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.clusters') }}">Clusters</a></li>
        <li class="active">{{ $cluster->name }}</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="nav-tabs-custom nav-tabs-floating">
            <ul class="nav nav-tabs">
                <li class="active"><a href="{{ route('admin.clusters.view', $cluster->id) }}">About</a></li>
                <li><a href="{{ route('admin.clusters.view.settings', $cluster->id) }}">Settings</a></li>
                <li><a href="{{ route('admin.clusters.view.configuration', $cluster->id) }}">Configuration</a></li>
                <li><a href="{{ route('admin.clusters.view.allocation', $cluster->id) }}">Allocation</a></li>
                <li><a href="{{ route('admin.clusters.view.servers', $cluster->id) }}">Servers</a></li>
            </ul>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-8">
        <div class="row">
            <div class="col-xs-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Information</h3>
                    </div>
                    <div class="box-body table-responsive no-padding">
                        <table class="table table-hover">
                            <tr>
                                <td>Version</td>
                                <td data-attr="info-git"><i class="fa fa-refresh fa-fw fa-spin"></i></td>
                            </tr>
                            <tr>
                                <td>Daemon Version</td>
                                <td><code data-attr="info-version"><i class="fa fa-refresh fa-fw fa-spin"></i></code> (Latest: <code>{{ $version->getDaemon() }}</code>)</td>
                            </tr>
                            <tr>
                                <td>Go Version</td>
                                <td data-attr="info-go"><i class="fa fa-refresh fa-fw fa-spin"></i></td>
                            </tr>
                            <tr>
                                <td>Platform</td>
                                <td data-attr="info-platform"><i class="fa fa-refresh fa-fw fa-spin"></i></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
            @if ($cluster->description)
                <div class="col-xs-12">
                    <div class="box box-default">
                        <div class="box-header with-border">
                            Description
                        </div>
                        <div class="box-body table-responsive">
                            <pre>{{ $cluster->description }}</pre>
                        </div>
                    </div>
                </div>
            @endif
            <div class="col-xs-12">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">Delete Cluster</h3>
                    </div>
                    <div class="box-body">
                        <p class="no-margin">Deleting a cluster is a irreversible action and will immediately remove from the panel. There must be no servers associated in order to continue.</p>
                    </div>
                    <div class="box-footer">
                        <form action="{{ route('admin.clusters.view.delete', $cluster->id) }}" method="POST">
                            {!! csrf_field() !!}
                            {!! method_field('DELETE') !!}
                            <button type="submit" class="btn btn-danger btn-sm pull-right" {{ ($cluster->servers_count < 1) ?: 'disabled' }}>Yes, Delete This Cluster</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">At-a-Glance</h3>
            </div>
            <div class="box-body">
                <div class="row">
                    @if($cluster->maintenance_mode)
                    <div class="col-sm-12">
                        <div class="info-box bg-orange">
                            <span class="info-box-icon"><i class="ion ion-wrench"></i></span>
                            <div class="info-box-content" style="padding: 23px 10px 0;">
                                <span class="info-box-text">This cluster is under</span>
                                <span class="info-box-number">Maintenance</span>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="col-sm-12">
                        <div class="info-box bg-green">
                            <span class="info-box-icon"><i class="ion ion-ios-folder-outline"></i></span>
                            <div class="info-box-content" style="padding: 25px 10px 0;">
                                <span class="info-box-text">Disk Space Used</span>
                                <span class="info-box-number">X MiB</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="info-box bg-red">
                            <span class="info-box-icon"><i class="ion ion-ios-barcode-outline"></i></span>
                            <div class="info-box-content" style="padding: 15px 10px 0;">
                                <span class="info-box-text">Memory Used</span>
                                <span class="info-box-number">X / X MiB</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="info-box bg-blue">
                            <span class="info-box-icon"><i class="ion ion-social-buffer-outline"></i></span>
                            <div class="info-box-content" style="padding: 23px 10px 0;">
                                <span class="info-box-text">Total Servers</span>
                                <span class="info-box-number">{{ $cluster->servers_count }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    (function getInformation() {
        $.ajax({
            method: 'GET',
            url: '/admin/clusters/view/{{ $cluster->id }}/system-information',
            timeout: 5000,
        }).done(function (data) {
            $('[data-attr="info-version"]').html(data.version);
            $('[data-attr="info-system"]').html(data.system.type + ' (' + data.system.arch + ') <code>' + data.system.release + '</code>');
            $('[data-attr="info-cpus"]').html(data.system.cpus);
            $('[data-attr="info-git"]').html(data.system.git);
            $('[data-attr="info-go"]').html(data.system.go);
            $('[data-attr="info-platform"]').html(data.system.platform);
        }).fail(function (jqXHR) {

        }).always(function() {
            setTimeout(getInformation, 10000);
        });
    })();
    </script>
@endsection
