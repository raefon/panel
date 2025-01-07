@extends('layouts.admin')

@section('title')
    {{ $cluster->name }}: Servers
@endsection

@section('content-header')
    <h1>{{ $cluster->name }}<small>All servers currently assigned to this cluster.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.clusters') }}">Clusters</a></li>
        <li><a href="{{ route('admin.clusters.view', $cluster->id) }}">{{ $cluster->name }}</a></li>
        <li class="active">Servers</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="nav-tabs-custom nav-tabs-floating">
            <ul class="nav nav-tabs">
                <li><a href="{{ route('admin.clusters.view', $cluster->id) }}">About</a></li>
                <li><a href="{{ route('admin.clusters.view.settings', $cluster->id) }}">Settings</a></li>
                <li><a href="{{ route('admin.clusters.view.configuration', $cluster->id) }}">Configuration</a></li>
                <li><a href="{{ route('admin.clusters.view.allocation', $cluster->id) }}">Allocation</a></li>
                <li class="active"><a href="{{ route('admin.clusters.view.servers', $cluster->id) }}">Servers</a></li>
            </ul>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-12">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Process Manager</h3>
            </div>
            <div class="box-body table-responsive no-padding">
                <table class="table table-hover">
                    <tr>
                        <th>ID</th>
                        <th>Server Name</th>
                        <th>Owner</th>
                        <th>Service</th>
                    </tr>
                    @foreach($servers as $server)
                        <tr data-server="{{ $server->uuid }}">
                            <td><code>{{ $server->uuidShort }}</code></td>
                            <td><a href="{{ route('admin.servers.view', $server->id) }}">{{ $server->name }}</a></td>
                            <td><a href="{{ route('admin.users.view', $server->owner_id) }}">{{ $server->user->username }}</a></td>
                            <td>{{ $server->launchpad->name }} ({{ $server->rocket->name }})</td>
                        </tr>
                    @endforeach
                </table>
                @if($servers->hasPages())
                    <div class="box-footer with-border">
                        <div class="col-md-12 text-center">{!! $servers->render() !!}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
