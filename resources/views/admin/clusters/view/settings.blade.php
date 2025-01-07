@extends('layouts.admin')

@section('title')
    {{ $cluster->name }}: Settings
@endsection

@section('content-header')
    <h1>{{ $cluster->name }}<small>Configure your cluster settings.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.clusters') }}">Clusters</a></li>
        <li><a href="{{ route('admin.clusters.view', $cluster->id) }}">{{ $cluster->name }}</a></li>
        <li class="active">Settings</li>
    </ol>
@endsection

@section('content')
<div class="row">
    <div class="col-xs-12">
        <div class="nav-tabs-custom nav-tabs-floating">
            <ul class="nav nav-tabs">
                <li><a href="{{ route('admin.clusters.view', $cluster->id) }}">About</a></li>
                <li class="active"><a href="{{ route('admin.clusters.view.settings', $cluster->id) }}">Settings</a></li>
                <li><a href="{{ route('admin.clusters.view.configuration', $cluster->id) }}">Configuration</a></li>
                <li><a href="{{ route('admin.clusters.view.allocation', $cluster->id) }}">Allocation</a></li>
                <li><a href="{{ route('admin.clusters.view.servers', $cluster->id) }}">Servers</a></li>
            </ul>
        </div>
    </div>
</div>
<form action="{{ route('admin.clusters.view.settings', $cluster->id) }}" method="POST">
    <div class="row">
        <div class="col-sm-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Daemon</h3>
                </div>
                <div class="box-body row">
                    <div class="form-group col-xs-12">
                        <label for="name" class="control-label">Cluster Name</label>
                        <div>
                            <input type="text" autocomplete="off" name="name" class="form-control" value="{{ old('name', $cluster->name) }}" />
                            <p class="text-muted"><small>Character limits: <code>a-zA-Z0-9_.-</code> and <code>[Space]</code> (min 1, max 100 characters).</small></p>
                        </div>
                    </div>
                    <div class="form-group col-xs-12">
                        <label for="description" class="control-label">Description</label>
                        <div>
                            <textarea name="description" id="description" rows="4" class="form-control">{{ $cluster->description }}</textarea>
                        </div>
                    </div>
                    <div class="form-group col-xs-12">
                        <label for="name" class="control-label">Location</label>
                        <div>
                            <select name="location_id" class="form-control">
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ (old('location_id', $cluster->location_id) === $location->id) ? 'selected' : '' }}>{{ $location->long }} ({{ $location->short }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-xs-12">
                        <label for="public" class="control-label">Cluster Visibility</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" name="public" value="1" {{ (old('public', $cluster->public)) ? 'checked' : '' }} id="public_1" checked> <label for="public_1" style="padding-left:5px;">Public</label>
                            </div>
                            <div class="radio radio-danger radio-inline">
                                <input type="radio" name="public" value="0" {{ (old('public', $cluster->public)) ? '' : 'checked' }} id="public_0"> <label for="public_0" style="padding-left:5px;">Private</label>
                            </div>
                        </div>
                        <p class="text-muted small">By setting a cluster to <code>private</code> you will be denying the ability to auto-deploy to this cluster.
                    </div>
                    <div class="form-group col-xs-12">
                        <label for="fqdn" class="control-label">Fully Qualified Domain Name</label>
                        <div>
                            <input type="text" autocomplete="off" name="fqdn" class="form-control" value="{{ old('fqdn', $cluster->fqdn) }}" />
                        </div>
                        <p class="text-muted"><small>Please enter domain name (e.g <code>kuber.example.com</code>) to be used for connecting to the daemon. An IP address may only be used if you are not using SSL for this node.
                                <a tabindex="0" data-toggle="popover" data-trigger="focus" title="Why do I need a FQDN?" data-content="In order to secure communications between your server and this daemon we use SSL. We cannot generate a SSL certificate for IP Addresses, and as such you will need to provide a FQDN.">Why?</a>
                            </small></p>
                    </div>
                    <div class="form-group col-xs-12">
                        <label class="form-label"><span class="label label-warning"><i class="fa fa-power-off"></i></span> Communicate Over SSL</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pSSLTrue" value="https" name="scheme" {{ (old('scheme', $cluster->scheme) === 'https') ? 'checked' : '' }}>
                                <label for="pSSLTrue"> Use SSL Connection</label>
                            </div>
                            <div class="radio radio-danger radio-inline">
                                <input type="radio" id="pSSLFalse" value="http" name="scheme" {{ (old('scheme', $cluster->scheme) !== 'https') ? 'checked' : '' }}>
                                <label for="pSSLFalse"> Use HTTP Connection</label>
                            </div>
                        </div>
                        <p class="text-muted small">In most cases you should select to use a SSL connection. If using an IP Address or you do not wish to use SSL at all, select a HTTP connection.</p>
                    </div>
                    <div class="form-group col-xs-12">
                        <label class="form-label"><span class="label label-warning"><i class="fa fa-power-off"></i></span> Behind Proxy</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pProxyFalse" value="0" name="behind_proxy" {{ (old('behind_proxy', $cluster->behind_proxy) == false) ? 'checked' : '' }}>
                                <label for="pProxyFalse"> Not Behind Proxy </label>
                            </div>
                            <div class="radio radio-info radio-inline">
                                <input type="radio" id="pProxyTrue" value="1" name="behind_proxy" {{ (old('behind_proxy', $cluster->behind_proxy) == true) ? 'checked' : '' }}>
                                <label for="pProxyTrue"> Behind Proxy </label>
                            </div>
                        </div>
                        <p class="text-muted small">If you are running the daemon behind a proxy such as Cloudflare, select this to have the daemon skip looking for certificates on boot.</p>
                    </div>
                    <div class="form-group col-xs-12">
                        <label class="form-label"><span class="label label-warning"><i class="fa fa-wrench"></i></span> Maintenance Mode</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pMaintenanceFalse" value="0" name="maintenance_mode" {{ (old('behind_proxy', $cluster->maintenance_mode) == false) ? 'checked' : '' }}>
                                <label for="pMaintenanceFalse"> Disabled</label>
                            </div>
                            <div class="radio radio-warning radio-inline">
                                <input type="radio" id="pMaintenanceTrue" value="1" name="maintenance_mode" {{ (old('behind_proxy', $cluster->maintenance_mode) == true) ? 'checked' : '' }}>
                                <label for="pMaintenanceTrue"> Enabled</label>
                            </div>
                        </div>
                        <p class="text-muted small">If the cluster is marked as 'Under Maintenance' users won't be able to access servers that are on this node.</p>
                    </div>
                    <div class="form-group col-xs-12">
                        <label for="disk_overallocate" class="control-label">Maximum Web Upload Filesize</label>
                        <div class="input-group">
                            <input type="text" name="upload_size" class="form-control" value="{{ old('upload_size', $cluster->upload_size) }}"/>
                            <span class="input-group-addon">MiB</span>
                        </div>
                        <p class="text-muted"><small>Enter the maximum size of files that can be uploaded through the web-based file manager.</small></p>
                    </div>
                    <div class="col-xs-12">
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="daemonListen" class="control-label"><span class="label label-warning"><i class="fa fa-power-off"></i></span> Daemon Port</label>
                                <div>
                                    <input type="text" name="daemonListen" class="form-control" value="{{ old('daemonListen', $cluster->daemonListen) }}"/>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <p class="text-muted small">If you will be running the daemon behind CloudFlare® you should set the daemon port to <code>8443</code> to allow websocket proxying over SSL.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Cluster</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label for="pHost" class="form-label">Host</label>
                        <input type="text" name="host" id="pHost" class="form-control" value="{{ old('host', $cluster->host) }}"/>
                        <p class="text-muted small">Host must be a host string, a <code>host:port</code> pair, or a <em>URL</em> to the base of the apiserver.</p>
                    </div>
                    <div class="form-group">
                        <label for="pBearerToken" class="form-label">Bearer Token</label>
                        <div class="input-group" id="pToggleShow">
                            <input type="password" name="bearer_token" id="pBearerToken" class="form-control" value="{{ old('bearer_token', $cluster->bearer_token) }}"/>
                            <span class="input-group-addon">
                                <a href="javascript:void(0);" style="color: inherit;"><i class="fa fa-eye-slash" aria-hidden="true"></i></a>
                            </span>
                        </div>
                        <p class="text-muted small">Service account bearer tokens are perfectly valid to use outside the cluster and can be used to create identities for long standing jobs that wish to talk to the Kubernetes API.</p>
                    </div>
                    <h4>Security</h4>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label class="form-label">Transport Layer Security</label>
                            <div>
                                <div class="radio radio-success radio-inline">
                                    <input type="radio" id="pInsecureFalse" value="0" name="insecure" {{ (old('insecure', $cluster->insecure) == false) ? 'checked' : '' }}>
                                    <label for="pInsecureFalse"> True</label>
                                </div>
                                <div class="radio radio-danger radio-inline">
                                    <input type="radio" id="pInsecureTrue" value="1" name="insecure" {{ (old('insecure', $cluster->insecure) == true) ? 'checked' : '' }}>
                                    <label for="pInsecureTrue"> False</label>
                                </div>
                            </div>
                            <p class="text-muted small">Server should be accessed without verifying the TLS certificate. For testing only.</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pCertFile" class="form-label">Cert File</label>
                            <input type="text" name="cert_file" id="pCertFile" class="form-control" placeholder="/path/to/client.crt" value="{{ old('cert_file', $cluster->cert_file) }}" />
                            <p class="text-muted small">Server requires TLS client certificate authentication.</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pKeyFile" class="form-label">Key File</label>
                            <input type="text" name="key_file" id="pKeyFile" class="form-control" placeholder="/path/to/client.key" value="{{ old('key_file', $cluster->key_file) }}" />
                            <p class="text-muted small">Server requires TLS client certificate authentication.</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pCAFile" class="form-label">CA File</label>
                            <input type="text" name="ca_file" id="pCAFile" class="form-control" placeholder="/root/ExampleCA.crt" value="{{ old('ca_file', $cluster->ca_file) }}" />
                            <p class="text-muted small">Trusted root certificates for server.</p>
                        </div>
                    </div>
                    <h4>Pod</h4>
                    <div class="form-group">
                        <label class="form-label">Metrics</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pMetricsAPI" value="metrics_api" name="metrics" {{ (old('metrics', $cluster->metrics) == 'metrics_api') ? 'checked' : '' }}>
                                <label for="pMetricsAPI">Metrics API</label>
                            </div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pMetricsPrometheus" value="prometheus" name="metrics" {{ (old('metrics', $cluster->metrics) == 'prometheus') ? 'checked' : '' }}>
                                <label for="pMetricsPrometheus">Prometheus</label>
                            </div>
                        </div>
                        <p class="text-muted small">Collects metrics from various sources in the cluster.</p>
                    </div>
                    
                    <div class="row">
                        <div class="metrics-form-group" style="display:none;"></div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="pDNSPolicy" class="form-label">DNS Policy</label>
                            <select name="dns_policy" id="pDNSPolicy">
                                <option value="clusterfirst" checked>ClusterFirst</option>
                                <option value="clusterfirstwithhostnet">ClusterFirstWithHostNet</option>
                                <option value="default">Default</option>
                                <option value="none">None</option>
                            </select>
                            <p class="text-muted small"><em>None</em> option use the default daemon DNS config.</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pImagePullPolicy" class="form-label">Image Pull Policy</label>
                            <select name="image_pull_policy" id="pImagePullPolicy">
                                <option value="ifnotpresent" {{ (old('image_pull_policy', $cluster->image_pull_policy) == 'ifnotpresent') ? 'selected' : '' }}>IfNotPresent</option>
                                <option value="always" {{ (old('image_pull_policy', $cluster->image_pull_policy) == 'always') ? 'selected' : '' }}>Always</option>
                                <option value="never" {{ (old('image_pull_policy', $cluster->image_pull_policy) == 'never') ? 'selected' : '' }}>Never</option>
                            </select>
                            <p class="text-muted small">Defaults to Always if <code>:latest</code> tag is specified, or IfNotPresent otherwise.</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pStorageClass" class="form-label">Storage Class</label>
                            <input type="text" name="storage_class" id="pStorageClass" class="form-control" value="{{ old('storage_class', $cluster->storage_class) }}"/>
                            <p class="text-muted small">StorageClass provides a way for administrators to describe the "classes" of storage they offer.</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pNamespace" class="form-label">Namespace</label>
                            <input type="text" name="ns" data-multiplicator="true" class="form-control" id="pNamespace" value="{{ old('ns', $cluster->ns) }}"/>
                            <p class="text-muted small">Namespaces provides a mechanism for isolating groups of resources within a single cluster.</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pSnapshotClass" class="form-label">Volume Snapshot Class</label>
                            <input type="text" name="snapshot_class" data-multiplicator="true" class="form-control" id="pSnapshotClass" value="{{ old('snapshot_class', $cluster->snapshot_class) }}"/>
                            <p class="text-muted small">VolumeSnapshotClass provides a way to describe the "classes" of storage when provisioning a volume snapshot.</p>
                        </div>
                    </div>

                    <h4>Service</h4>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="pServiceType" class="form-label">Service Type</label>
                            <select name="service_type" id="pServiceType">
                                <option value="nodeport" {{ (old('service_type', $cluster->service_type) == 'nodeport') ? 'selected' : '' }}>NodePort</option>
                                <option value="loadbalancer" {{ (old('service_type', $cluster->service_type) == 'loadbalancer') ? 'selected' : '' }}>LoadBalancer</option>
                            </select>
                            <p class="text-muted small">ServiceTypes allow you to specify what kind of Service you want.</p>
                        </div>

                        <div class="provider-form-group" style="display:none;"></div>
                        <div class="lb-form-group" style="display:none;"></div>
                    </div>
                    
                    <h4>SFTP</h4>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="pContainerSFTPImage" class="form-label">Container SFTP Image</label>
                            <input type="text" name="sftp_image" id="pContainerSFTPImage" class="form-control" value="{{ old('sftp_image', $cluster->sftp_image) }}"/>
                            <p class="text-muted small">The docker image used for the SFTP server.</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pContainerSFTPPort" class="form-label">Container SFTP Port</label>
                            <input type="text" name="sftp_port" class="form-control" id="pContainerSFTPPort" value="{{ old('sftp_port', $cluster->sftp_port) }}" />
                            <p class="text-muted small">The port on which the SFTP server will run.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Save Settings</h3>
                </div>
                <div class="box-body row">
                    <div class="form-group col-sm-6">
                        <div>
                            <input type="checkbox" name="reset_secret" id="reset_secret" /> <label for="reset_secret" class="control-label">Reset Daemon Master Key</label>
                        </div>
                        <p class="text-muted"><small>Resetting the daemon master key will void any request coming from the old key. This key is used for all sensitive operations on the daemon including server creation and deletion. We suggest changing this key regularly for security.</small></p>
                    </div>
                </div>
                <div class="box-footer">
                    {!! method_field('PATCH') !!}
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-primary pull-right">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    <script>
    $('[data-toggle="popover"]').popover({
        placement: 'auto'
    });
    $('select').select2();

    $(document).ready(function() {
        $('input[type="radio"][name="metrics"]').on('load change', function() {
            if ($('#pMetricsPrometheus').is(':checked')) {
                $('.metrics-form-group').show();
                $('.metrics-form-group').html('<div class="form-group col-md-6"> \
                    <label for="pContainerSFTPPort" class="form-label">Prometheus Address</label> \
                    <input type="text" name="prometheus_address" class="form-control" id="pPrometheusAddress" value="{{ old('prometheus_address', $cluster->prometheus_address) }}" placeholder="http://localhost:9090" /> \
                    <p class="text-muted small">The address of the Prometheus to connect to.</p> \
                </div>');
                // $(this).trigger('change');
            } else {
                $('.metrics-form-group').hide();
            }
        });
        $('input[type="radio"][name="metrics"]').trigger('change');

        $('#pServiceType').on('load change', function() {
            // alert(this.value)
            if (this.value === 'loadbalancer') {
                $('.provider-form-group').show();
                $('.provider-form-group').html('<div class="form-group col-md-6"> \
                <label for="pExternalTrafficPolicy" class="form-label">External Traffic Policy</label> \
                    <select name="external_traffic_policy" id="pExternalTrafficPolicy"> \
                        <option value="cluster" checked>Cluster</option> \
                        <option value="local">Local</option> \
                    </select> \
                    <p class="text-muted small">Denotes if this Service desires to route external traffic to node-local or cluster-wide endpoints..</p> \
                </div> \
                <div class="form-group col-md-6">\
                <label for="pLBProvider" class="form-label">LB Provider</label>\
                    <select name="lb_provider" id="pLBProvider">\
                        <option value="metallb" {{ (old('lb_provider', $cluster->lb_provider) == 'metallb') ? 'selected' : '' }}>MetalLB</option>\
                        <option value="gce" disabled>GCE</option>\
                        <option value="aws_elb" disabled>AWS ELB</option>\
                        <option value="azure_lb" disabled>Azure Load Balancer</option>\
                        <option value="traefik" disabled>Traefik</option>\
                        <option value="nginx_ingress" disabled>Nginx Ingress</option>\
                    </select>\
                    <p class="text-muted small">Each cloud provider has its own load balancing service, and each of these services has its own unique features.</p>\
                </div>');
                $('select').select2();
                $('#pLBProvider').on('load change', function() {
                    if (this.value === 'metallb') {
                        $('.lb-form-group').show();
                        $('.lb-form-group').html('<div class="form-group col-md-6">\
                                <label for="pMetalLBAddressPool" class="form-label">MetalLB Address Pool <small class="text-warning">optional</small></label>\
                                <input type="text" name="metallb_address_pool" data-multiplicator="true" class="form-control" id="pMetalLBAddressPool" value="{{ old('metallb_address_pool', $cluster->metallb_address_pool) }}"/>\
                                <p class="text-muted small">Supports requesting a specific address pool, if you want a certain kind of address but don’t care which one exactly..</p>\
                            </div>\
                            <div class="form-group col-md-6">\
                                <label class="form-label">Allow MetalLB Shared IP</label>\
                                <div>\
                                    <div class="radio radio-success radio-inline">\
                                        <input type="radio" id="pMLBSIPTrue" value="1" name="metallb_shared_ip" {{ (old('metallb_shared_ip', $cluster->metallb_shared_ip)) ? 'checked' : '' }}>\
                                        <label for="pMLBSIPTrue"> True</label>\
                                    </div>\
                                    <div class="radio radio-danger radio-inline">\
                                        <input type="radio" id="pMLBSIPFalse" value="0" name="metallb_shared_ip" {{ !(old('metallb_shared_ip', $cluster->metallb_shared_ip)) ? 'checked' : '' }}>\
                                        <label for="pMLBSIPFalse"> False</label>\
                                    </div>\
                                </div>\
                                <p class="text-muted small">MetalLB may colocate the two services on the same IP, but does not have to.</p>\
                            </div>');
                        $('#pExternalTrafficPolicy').select2();
                    }
                });
                $('#pLBProvider').trigger('change');
            } else {
                $('.lb-form-group').empty();
                $('.provider-form-group').hide();
                $('.provider-form-group').empty();
            }
        });
        $('#pServiceType').trigger('change');
    });

    // $('select[name="predefined_preset"]').change(function(){
    //     if ($(this).val() == 'google_gke_autopilot') {
    //         $('#pCustomMetadata').val(JSON.stringify(JSON.parse(
    //             '{ "annotations": { "networking.gke.io/load-balancer-type": "Internal", "networking.gke.io/internal-load-balancer-subnet": "gke-vip-subnet" } }'
    //         ),null,2));
    //     } else {
    //         $('#pCustomMetadata').val('');
    //     }
    // });

    $("#pToggleShow span a").on('click', function(event) {
        event.preventDefault();
        if ($('#pToggleShow input').attr("type") == "text") {
            $('#pToggleShow input').attr('type', 'password');
            $('#pToggleShow i').addClass( "fa-eye-slash" );
            $('#pToggleShow i').removeClass( "fa-eye" );
        } else if ($('#pToggleShow input').attr("type") == "password") {
            $('#pToggleShow input').attr('type', 'text');
            $('#pToggleShow i').removeClass( "fa-eye-slash" );
            $('#pToggleShow i').addClass( "fa-eye" );
        }
    });
    </script>
@endsection
