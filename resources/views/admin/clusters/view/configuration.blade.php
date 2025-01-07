@extends('layouts.admin')

@section('title')
    {{ $cluster->name }}: Configuration
@endsection

@section('content-header')
    <h1>{{ $cluster->name }}<small>Your daemon configuration file.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.clusters') }}">Clusters</a></li>
        <li><a href="{{ route('admin.clusters.view', $cluster->id) }}">{{ $cluster->name }}</a></li>
        <li class="active">Configuration</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="nav-tabs-custom nav-tabs-floating">
            <ul class="nav nav-tabs">
                <li><a href="{{ route('admin.clusters.view', $cluster->id) }}">About</a></li>
                <li><a href="{{ route('admin.clusters.view.settings', $cluster->id) }}">Settings</a></li>
                <li class="active"><a href="{{ route('admin.clusters.view.configuration', $cluster->id) }}">Configuration</a></li>
                <li><a href="{{ route('admin.clusters.view.allocation', $cluster->id) }}">Allocation</a></li>
                <li><a href="{{ route('admin.clusters.view.servers', $cluster->id) }}">Servers</a></li>
            </ul>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-8">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Configuration File</h3>
            </div>
            <div class="box-body">
                <pre class="no-margin" style="overflow: hidden; white-space: break-spaces">{{ $cluster->getYamlConfiguration() }}</pre>
            </div>
            <div class="box-footer">
                <p class="no-margin">This file should be placed in your daemon's root directory (usually <code>/etc/kubectyl</code>) in a file called <code>config.yml</code>.</p>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="box box-success">
            <div class="box-header with-border">
                <h3 class="box-title">Auto-Deploy</h3>
            </div>
            <div class="box-body">
                <p class="text-muted small">
                    Use the button below to generate a custom deployment command that can be used to configure
                    daemon on the target server with a single command.
                </p>
            </div>
            <div class="box-footer">
                <button type="button" id="configTokenBtn" class="btn btn-sm btn-default" style="width:100%;">Generate Token</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('footer-scripts')
    @parent
    <script>
    $('#configTokenBtn').on('click', function (event) {
        $.ajax({
            method: 'POST',
            url: '{{ route('admin.clusters.view.configuration.token', $cluster->id) }}',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        }).done(function (data) {
            swal({
                type: 'success',
                title: 'Token created.',
                text: '<p>To auto-configure your daemon run the following command:<br /><small><pre>cd /etc/kubectyl && sudo kuber configure --panel-url {{ config('app.url') }} --token ' + data.token + ' --cluster ' + data.cluster + '{{ config('app.debug') ? ' --allow-insecure' : '' }}</pre></small></p>',
                html: true
            })
        }).fail(function () {
            swal({
                title: 'Error',
                text: 'Something went wrong creating your token.',
                type: 'error'
            });
        });
    });
    </script>
@endsection
