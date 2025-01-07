
@extends('layouts.admin')

@section('title')
    Mounts &rarr; View &rarr; {{ $mount->id }}
@endsection

@section('content-header')
    <h1>{{ $mount->name }}<small>{{ str_limit($mount->description, 75) }}</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.mounts') }}">Mounts</a></li>
        <li class="active">{{ $mount->name }}</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Mount Details</h3>
                </div>

                <form action="{{ route('admin.mounts.view', $mount->id) }}" method="POST">
                    <div class="box-body">
                        <div class="form-group">
                            <label for="PUniqueID" class="form-label">Unique ID</label>
                            <input type="text" id="PUniqueID" class="form-control" value="{{ $mount->uuid }}" disabled />
                        </div>

                        <div class="form-group">
                            <label for="pName" class="form-label">Name</label>
                            <input type="text" id="pName" name="name" class="form-control" value="{{ $mount->name }}" />
                        </div>

                        <div class="form-group">
                            <label for="pDescription" class="form-label">Description</label>
                            <textarea id="pDescription" name="description" class="form-control" rows="4">{{ $mount->description }}</textarea>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="pSource" class="form-label">Source</label>
                                <input type="text" id="pSource" name="source" class="form-control" value="{{ $mount->source }}" />
                            </div>

                            <div class="form-group col-md-6">
                                <label for="pTarget" class="form-label">Target</label>
                                <input type="text" id="pTarget" name="target" class="form-control" value="{{ $mount->target }}" />
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label class="form-label">Read Only</label>

                                <div>
                                    <div class="radio radio-success radio-inline">
                                        <input type="radio" id="pReadOnlyFalse" name="read_only" value="0" @if(!$mount->read_only) checked @endif>
                                        <label for="pReadOnlyFalse">False</label>
                                    </div>

                                    <div class="radio radio-warning radio-inline">
                                        <input type="radio" id="pReadOnly" name="read_only" value="1" @if($mount->read_only) checked @endif>
                                        <label for="pReadOnly">True</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group col-md-6">
                                <label class="form-label">User Mountable</label>

                                <div>
                                    <div class="radio radio-success radio-inline">
                                        <input type="radio" id="pUserMountableFalse" name="user_mountable" value="0" @if(!$mount->user_mountable) checked @endif>
                                        <label for="pUserMountableFalse">False</label>
                                    </div>

                                    <div class="radio radio-warning radio-inline">
                                        <input type="radio" id="pUserMountable" name="user_mountable" value="1" @if($mount->user_mountable) checked @endif>
                                        <label for="pUserMountable">True</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer">
                        {!! csrf_field() !!}
                        {!! method_field('PATCH') !!}

                        <button name="action" value="edit" class="btn btn-sm btn-primary pull-right">Save</button>
                        <button name="action" value="delete" class="btn btn-sm btn-danger pull-left muted muted-hover"><i class="fa fa-trash-o"></i></button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-sm-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Rockets</h3>

                    <div class="box-tools">
                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addRocketsModal">Add Rockets</button>
                    </div>
                </div>

                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th></th>
                        </tr>

                        @foreach ($mount->rockets as $rocket)
                            <tr>
                                <td class="col-sm-2 middle"><code>{{ $rocket->id }}</code></td>
                                <td class="middle"><a href="{{ route('admin.launchpads.rocket.view', $rocket->id) }}">{{ $rocket->name }}</a></td>
                                <td class="col-sm-1 middle">
                                    <button data-action="detach-rocket" data-id="{{ $rocket->id }}" class="btn btn-sm btn-danger"><i class="fa fa-trash-o"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>

            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Clusters</h3>

                    <div class="box-tools">
                        <button class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addClustersModal">Add Clusters</button>
                    </div>
                </div>

                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>FQDN</th>
                            <th></th>
                        </tr>

                        @foreach ($mount->clusters as $cluster)
                            <tr>
                                <td class="col-sm-2 middle"><code>{{ $cluster->id }}</code></td>
                                <td class="middle"><a href="{{ route('admin.clusters.view', $cluster->id) }}">{{ $cluster->name }}</a></td>
                                <td class="middle"><code>{{ $cluster->fqdn }}</code></td>
                                <td class="col-sm-1 middle">
                                    <button data-action="detach-cluster" data-id="{{ $cluster->id }}" class="btn btn-sm btn-danger"><i class="fa fa-trash-o"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addRocketsModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.mounts.rockets', $mount->id) }}" method="POST">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" style="color: #FFFFFF">&times;</span>
                        </button>

                        <h4 class="modal-title">Add Rockets</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for="pRockets">Rockets</label>
                                <select id="pRockets" name="rockets[]" class="form-control" multiple>
                                    @foreach ($launchpads as $launchpad)
                                        <optgroup label="{{ $launchpad->name }}">
                                            @foreach ($launchpad->rockets as $rocket)

                                                @if (! in_array($rocket->id, $mount->rockets->pluck('id')->toArray()))
                                                    <option value="{{ $rocket->id }}">{{ $rocket->name }}</option>
                                                @endif

                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        {!! csrf_field() !!}

                        <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addClustersModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.mounts.clusters', $mount->id) }}" method="POST">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true" style="color: #FFFFFF">&times;</span>
                        </button>

                        <h4 class="modal-title">Add Clusters</h4>
                    </div>

                    <div class="modal-body">
                        <div class="row">
                            <div class="form-group col-md-12">
                                <label for="pClusters">Clusters</label>
                                <select id="pClusters" name="clusters[]" class="form-control" multiple>
                                    @foreach ($locations as $location)
                                        <optgroup label="{{ $location->long }} ({{ $location->short }})">
                                            @foreach ($location->clusters as $cluster)

                                                @if (! in_array($cluster->id, $mount->clusters->pluck('id')->toArray()))
                                                    <option value="{{ $cluster->id }}">{{ $cluster->name }}</option>
                                                @endif

                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        {!! csrf_field() !!}

                        <button type="button" class="btn btn-default btn-sm pull-left" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Add</button>
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
            $('#pRockets').select2({
                placeholder: 'Select rockets..',
            });

            $('#pClusters').select2({
                placeholder: 'Select clusters..',
            });

            $('button[data-action="detach-rocket"]').click(function (event) {
                event.preventDefault();

                const element = $(this);
                const rocketId = $(this).data('id');

                $.ajax({
                    method: 'DELETE',
                    url: '/admin/mounts/' + {{ $mount->id }} + '/rockets/' + rocketId,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') },
                }).done(function () {
                    element.parent().parent().addClass('warning').delay(100).fadeOut();
                    swal({ type: 'success', title: 'Rocket detached.' });
                }).fail(function (jqXHR) {
                    console.error(jqXHR);
                    swal({
                        title: 'Whoops!',
                        text: jqXHR.responseJSON.error,
                        type: 'error'
                    });
                });
            });

            $('button[data-action="detach-cluster"]').click(function (event) {
                event.preventDefault();

                const element = $(this);
                const clusterId = $(this).data('id');

                $.ajax({
                    method: 'DELETE',
                    url: '/admin/mounts/' + {{ $mount->id }} + '/clusters/' + clusterId,
                    headers: { 'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content') },
                }).done(function () {
                    element.parent().parent().addClass('warning').delay(100).fadeOut();
                    swal({ type: 'success', title: 'Cluster detached.' });
                }).fail(function (jqXHR) {
                    console.error(jqXHR);
                    swal({
                        title: 'Whoops!',
                        text: jqXHR.responseJSON.error,
                        type: 'error'
                    });
                });
            });
        });
    </script>
@endsection
