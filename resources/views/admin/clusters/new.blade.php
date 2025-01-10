@extends('layouts.admin')

@section('title')
    Clusters &rarr; New
@endsection

@section('content-header')
    <h1>New Cluster<small>Create a new local or remote cluster for servers to be installed to.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.clusters') }}">Clusters</a></li>
        <li class="active">New</li>
    </ol>
@endsection

@section('content')
<form action="{{ route('admin.clusters.new') }}" method="POST">
    <div class="row">
        <div class="col-sm-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Daemon</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label for="pName" class="form-label">Name</label>
                        <input type="text" name="name" id="pName" class="form-control" value="{{ old('name') }}"/>
                        <p class="text-muted small">Character limits: <code>a-zA-Z0-9_.-</code> and <code>[Space]</code> (min 1, max 100 characters).</p>
                    </div>
                    <div class="form-group">
                        <label for="pDescription" class="form-label">Description</label>
                        <textarea name="description" id="pDescription" rows="4" class="form-control">{{ old('description') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="pLocationId" class="form-label">Location</label>
                        <select name="location_id" id="pLocationId">
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ $location->id != old('location_id') ?: 'selected' }}>{{ $location->short }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Cluster Visibility</label>
                        <div>
                            <div class="radio radio-success radio-inline">

                                <input type="radio" id="pPublicTrue" value="1" name="public" checked>
                                <label for="pPublicTrue"> Public </label>
                            </div>
                            <div class="radio radio-danger radio-inline">
                                <input type="radio" id="pPublicFalse" value="0" name="public">
                                <label for="pPublicFalse"> Private </label>
                            </div>
                        </div>
                        <p class="text-muted small">By setting a cluster to <code>private</code> you will be denying the ability to auto-deploy to this cluster.
                    </div>
                    <div class="form-group">
                        <label for="pFQDN" class="form-label">FQDN</label>
                        <input type="text" name="fqdn" id="pFQDN" class="form-control" value="{{ old('fqdn') }}"/>
                        <p class="text-muted small">Please enter domain name (e.g <code>kuber.example.com</code>) to be used for connecting to the daemon. An IP address may be used <em>only</em> if you are not using SSL for this daemon.</p>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Communicate Over SSL</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pSSLTrue" value="https" name="scheme" checked>
                                <label for="pSSLTrue"> Use SSL Connection</label>
                            </div>
                            <div class="radio radio-danger radio-inline">
                                <input type="radio" id="pSSLFalse" value="http" name="scheme" @if(request()->isSecure()) disabled @endif>
                                <label for="pSSLFalse"> Use HTTP Connection</label>
                            </div>
                        </div>
                        @if(request()->isSecure())
                            <p class="text-danger small">Your Panel is currently configured to use a secure connection. In order for browsers to connect to your daemon it <strong>must</strong> use a SSL connection.</p>
                        @else
                            <p class="text-muted small">In most cases you should select to use a SSL connection. If using an IP Address or you do not wish to use SSL at all, select a HTTP connection.</p>
                        @endif
                    </div>
                    <div class="form-group">
                        <label class="form-label">Behind Proxy</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pProxyFalse" value="0" name="behind_proxy" checked>
                                <label for="pProxyFalse"> Not Behind Proxy </label>
                            </div>
                            <div class="radio radio-info radio-inline">
                                <input type="radio" id="pProxyTrue" value="1" name="behind_proxy">
                                <label for="pProxyTrue"> Behind Proxy </label>
                            </div>
                        </div>
                        <p class="text-muted small">If you are running the daemon behind a proxy such as Cloudflare, select this to have the daemon skip looking for certificates on boot.</p>
                    </div>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="pDaemonListen" class="form-label">Daemon Port</label>
                            <input type="text" name="daemonListen" class="form-control" id="pDaemonListen" value="8080" />
                        </div>
                        <div class="col-md-12">
                            <p class="text-muted small">If you will be running the daemon behind CloudFlare&reg; you should set the daemon port to <code>8443</code> to allow websocket proxying over SSL.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Cluster</h3>
                </div>
                <div class="box-body">
                    <div class="form-group">
                        <label for="pHost" class="form-label">Host</label>
                        <input type="text" name="host" id="pHost" class="form-control" value="{{ old('host') }}"/>
                        <p class="text-muted small">Host must be a host string, a <code>host:port</code> pair, or a <em>URL</em> to the base of the apiserver.</p>
                    </div>
                    <div class="form-group">
                        <label for="pBearerToken" class="form-label">Bearer Token</label>
                        <div class="input-group" id="pToggleShow">
                            <input type="password" name="bearer_token" id="pBearerToken" class="form-control"/>
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
                                    <input type="radio" id="pInsecureFalse" value="0" name="insecure" checked>
                                    <label for="pInsecureFalse"> True</label>
                                </div>
                                <div class="radio radio-danger radio-inline">
                                    <input type="radio" id="pInsecureTrue" value="1" name="insecure">
                                    <label for="pInsecureTrue"> False</label>
                                </div>
                            </div>
                            <p class="text-muted small">Server should be accessed without verifying the TLS certificate. For testing only.</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pCertFile" class="form-label">Cert File</label>
                            <input type="text" name="cert_file" id="pCertFile" class="form-control" placeholder="/path/to/client.crt"/>
                            <p class="text-muted small">Server requires TLS client certificate authentication.</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pKeyFile" class="form-label">Key File</label>
                            <input type="text" name="key_file" id="pKeyFile" class="form-control" placeholder="/path/to/client.key"/>
                            <p class="text-muted small">Server requires TLS client certificate authentication.</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pCAFile" class="form-label">CA File</label>
                            <input type="text" name="ca_file" id="pCAFile" class="form-control" placeholder="/root/ExampleCA.crt"/>
                            <p class="text-muted small">Trusted root certificates for server.</p>
                        </div>
                    </div>

                    <h4>Pod</h4>
                    <div class="form-group">
                        <label class="form-label">Metrics</label>
                        <div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pMetricsAPI" value="metrics_api" name="metrics" checked>
                                <label for="pMetricsAPI">Metrics API</label>
                            </div>
                            <div class="radio radio-success radio-inline">
                                <input type="radio" id="pMetricsPrometheus" value="prometheus" name="metrics">
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
                                <option value="clusterfirst" selected>ClusterFirst</option>
                                <option value="clusterfirstwithhostnet">ClusterFirstWithHostNet</option>
                                <option value="default">Default</option>
                                <option value="none">None</option>
                            </select>
                            <p class="text-muted small"><em>None</em> option use the default daemon DNS config.</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pImagePullPolicy" class="form-label">Image Pull Policy</label>
                            <select name="image_pull_policy" id="pImagePullPolicy">
                                <option value="ifnotpresent" selected>IfNotPresent</option>
                                <option value="always">Always</option>
                                <option value="never">Never</option>
                            </select>
                            <p class="text-muted small">Defaults to Always if <code>:latest</code> tag is specified, or IfNotPresent otherwise.</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pStorageClass" class="form-label">Storage Class</label>
                            <input type="text" name="storage_class" id="pStorageClass" class="form-control" placeholder="e.g local-path"/>
                            <p class="text-muted small">StorageClass provides a way for administrators to describe the "classes" of storage they offer.</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pNamespace" class="form-label">Namespace</label>
                            <input type="text" name="ns" data-multiplicator="true" class="form-control" id="pNamespace" value="default"/>
                            <p class="text-muted small">Namespaces provides a mechanism for isolating groups of resources within a single cluster.</p>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="pSnapshotClass" class="form-label">Volume Snapshot Class</label>
                            <input type="text" name="snapshot_class" data-multiplicator="true" class="form-control" id="pSnapshotClass" value="" placeholder="csi-rbdplugin-snapclass"/>
                            <p class="text-muted small">VolumeSnapshotClass provides a way to describe the "classes" of storage when provisioning a volume snapshot.</p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="backup-form-group" style="display:none;"></div>
                    </div>

                    <h4>Service</h4>
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="pServiceType" class="form-label">Service Type</label>
                            <select name="service_type" id="pServiceType">
                                <option value="nodeport" {{ (old('service_type') == 'nodeport') ? 'selected' : '' }}>NodePort</option>
                                <option value="loadbalancer" {{ (old('service_type') == 'loadbalancer') ? 'selected' : '' }}>LoadBalancer</option>
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
                            <input type="text" name="sftp_image" id="pContainerSFTPImage" class="form-control" value="ghcr.io/raefon/sftp-server:latest"/>
                            <p class="text-muted small">The docker image used for the SFTP server.</p>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="pContainerSFTPPort" class="form-label">Container SFTP Port</label>
                            <input type="text" name="sftp_port" class="form-control" id="pContainerSFTPPort" value="2022" />
                            <p class="text-muted small">The port on which the SFTP server will run.</p>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    {!! csrf_field() !!}
                    <button type="submit" class="btn btn-success pull-right">Create Cluster</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('footer-scripts')
    @parent
    <script>
        $('select').select2();

        $(document).ready(function() {
        $('input[type="radio"][name="metrics"]').on('load change', function() {
            if ($('#pMetricsPrometheus').is(':checked')) {
                $('.metrics-form-group').show();
                $('.metrics-form-group').html('<div class="form-group col-md-6"> \
                    <label for="pContainerSFTPPort" class="form-label">Prometheus Address</label> \
                    <input type="text" name="prometheus_address" class="form-control" id="pPrometheusAddress" value="{{ old('prometheus_address') }}" placeholder="http://localhost:9090" /> \
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
                        <option value="metallb" {{ (old('lb_provider') == 'metallb') ? 'selected' : '' }}>MetalLB</option>\
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
                                <input type="text" name="metallb_address_pool" data-multiplicator="true" class="form-control" id="pMetalLBAddressPool" value="{{ old('metallb_address_pool') }}" placeholder="production-public-ips"/>\
                                <p class="text-muted small">Supports requesting a specific address pool, if you want a certain kind of address but don’t care which one exactly..</p>\
                            </div>\
                            <div class="form-group col-md-6">\
                                <label class="form-label">Allow MetalLB Shared IP</label>\
                                <div>\
                                    <div class="radio radio-success radio-inline">\
                                        <input type="radio" id="pMLBSIPTrue" value="1" name="metallb_shared_ip" checked>\
                                        <label for="pMLBSIPTrue"> True</label>\
                                    </div>\
                                    <div class="radio radio-danger radio-inline">\
                                        <input type="radio" id="pMLBSIPFalse" value="0" name="metallb_shared_ip">\
                                        <label for="pMLBSIPFalse"> False</label>\
                                    </div>\
                                </div>\
                                <p class="text-muted small">MetalLB may colocate the two services on the same IP, but does not have to.</p>\
                            </div>');
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
