@extends('layouts.admin')

@section('title')
    Server — {{ $server->name }}
@endsection

@section('content-header')
    <h1>{{ $server->name }}<small>{{ str_limit($server->description) }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.servers') }}">Servers</a></li>
        <li class="active">{{ $server->name }}</li>
    </ol>
@endsection

@section('content')
@include('admin.servers.partials.navigation')
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
                                <td>Internal Identifier</td>
                                <td><code>{{ $server->id }}</code></td>
                            </tr>
                            <tr>
                                <td>External Identifier</td>
                                @if(is_null($server->external_id))
                                    <td><span class="label label-default">Not Set</span></td>
                                @else
                                    <td><code>{{ $server->external_id }}</code></td>
                                @endif
                            </tr>
                            <tr>
                                <td>UUID / Docker Container ID</td>
                                <td><code>{{ $server->uuid }}</code></td>
                            </tr>
                            <tr>
                                <td>Current Rocket</td>
                                <td>
                                    <a href="{{ route('admin.launchpads.view', $server->launchpad_id) }}">{{ $server->launchpad->name }}</a> ::
                                    <a href="{{ route('admin.launchpads.rocket.view', $server->rocket_id) }}">{{ $server->rocket->name }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td>Server Name</td>
                                <td>{{ $server->name }}</td>
                            </tr>
                            <tr>
                                <td>CPU Limit</td>
                                <td>
                                    @if($server->cpu_limit === 0)
                                        <code>Unlimited</code>
                                    @else
                                        <code>{{ $server->cpu_limit }}%</code>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Memory</td>
                                <td>
                                    @if($server->memory_limit === 0)
                                        <code>Unlimited</code>
                                    @else
                                        <code>{{ $server->memory_limit }}MiB</code>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Disk Space</td>
                                <td>
                                    @if($server->disk === 0)
                                        <code>Unlimited</code>
                                    @else
                                        <code>{{ $server->disk }}MiB</code>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4">
        <div class="box box-primary">
            <div class="box-body" style="padding-bottom: 0px;">
                <div class="row">
                    @if($server->isSuspended())
                        <div class="col-sm-12">
                            <div class="small-box bg-yellow">
                                <div class="inner">
                                    <h3 class="no-margin">Suspended</h3>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if(!$server->isInstalled())
                        <div class="col-sm-12">
                            <div class="small-box {{ (! $server->isInstalled()) ? 'bg-blue' : 'bg-maroon' }}">
                                <div class="inner">
                                    <h3 class="no-margin">{{ (! $server->isInstalled()) ? 'Installing' : 'Install Failed' }}</h3>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-sm-12">
                        <div class="small-box bg-gray">
                            <div class="inner">
                                <h3>{{ str_limit($server->user->username, 16) }}</h3>
                                <p>Server Owner</p>
                            </div>
                            <div class="icon"><i class="fa fa-user"></i></div>
                            <a href="{{ route('admin.users.view', $server->user->id) }}" class="small-box-footer">
                                More info <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="small-box bg-gray">
                            <div class="inner">
                                <h3>{{ str_limit($server->cluster->name, 16) }}</h3>
                                <p>Server Cluster</p>
                            </div>
                            <div class="icon"><i class="fa fa-codepen"></i></div>
                            <a href="{{ route('admin.clusters.view', $server->cluster->id) }}" class="small-box-footer">
                                More info <i class="fa fa-arrow-circle-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
