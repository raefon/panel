#!/bin/ash -e
cd /app

mkdir -p /var/log/panel/logs/ /var/log/supervisord/ /var/log/nginx/ /var/log/php7/ &&
    chmod 777 /var/log/panel/logs/ &&
    ln -s /var/log/panel/logs/ /app/storage/logs/

## check for .env file and generate app keys if missing
if [ -f /app/var/.env ]; then
    echo "external vars exist."
    rm -rf /app/.env
    ln -s /app/var/.env /app/
else
    echo "external vars don't exist."
    rm -rf /app/.env
    touch /app/var/.env

    ## manually generate a key because key generate --force fails
    if [ -z $APP_KEY ]; then
        echo -e "Generating key."
        APP_KEY=$(cat /dev/urandom | tr -dc 'a-zA-Z0-9' | fold -w 32 | head -n 1)
        echo -e "Generated app key: $APP_KEY"
        echo -e "APP_KEY=$APP_KEY" >/app/var/.env
    else
        echo -e "APP_KEY exists in environment, using that."
        echo -e "APP_KEY=$APP_KEY" >/app/var/.env
    fi

    ln -s /app/var/.env /app/
fi

echo "Checking if https is required."
if [ -f /etc/nginx/http.d/panel.conf ]; then
    echo "Using nginx config already in place."
    if [ $LE_EMAIL ]; then
        if [[ ! -f "/etc/nginx/certs/tls.key" && ! -f "/etc/nginx/certs/tls.crt" ]]; then
            echo "Checking for cert update"
            certbot certonly -d $(echo $APP_URL | sed 's~http[s]*://~~g') --standalone -m $LE_EMAIL --agree-tos -n
        fi
    else
        echo "No letsencrypt email is set"
    fi
else
    echo "Checking if letsencrypt email is set."
    if [ -z $LE_EMAIL ]; then
        echo "No letsencrypt email is set using http config."
        cp .github/docker/default.conf /etc/nginx/http.d/panel.conf
    else
        echo "writing ssl config"
        cp .github/docker/default_ssl.conf /etc/nginx/http.d/panel.conf

        if [[ -f "/etc/nginx/certs/tls.key" && -f "/etc/nginx/certs/tls.crt" ]]; then
            cert_file="/etc/nginx/certs/tls.crt"
            key_file="/etc/nginx/certs/tls.key"

            sed -i "s#ssl_certificate /etc/letsencrypt/live/[^/]\+/fullchain.pem;#ssl_certificate $cert_file;#" /etc/nginx/http.d/panel.conf
            sed -i "s#ssl_certificate_key /etc/letsencrypt/live/[^/]\+/privkey.pem;#ssl_certificate_key $key_file;#" /etc/nginx/http.d/panel.conf

            echo "using existing certificates from cert-manager"

            echo "updating nginx domain name"
            sed -i "s|<domain>|$(echo $APP_URL | sed 's~http[s]*://~~g')|g" /etc/nginx/http.d/panel.conf
        else
            echo "updating nginx domain name"
            sed -i "s|<domain>|$(echo $APP_URL | sed 's~http[s]*://~~g')|g" /etc/nginx/http.d/panel.conf

            echo "generating certs"
            certbot certonly -d $(echo $APP_URL | sed 's~http[s]*://~~g') --standalone -m $LE_EMAIL --agree-tos -n
        fi
    fi
    echo "Removing the default nginx config"
    rm -rf /etc/nginx/http.d/default.conf
fi

if [[ -z $DB_PORT ]]; then
    echo -e "DB_PORT not specified, defaulting to 3306"
    DB_PORT=3306
fi

## check for DB up before starting the panel
echo "Checking database status."
until nc -z -v -w30 $DB_HOST $DB_PORT; do
    echo "Waiting for database connection..."
    # wait for 1 seconds before check again
    sleep 1
done

## make sure the db is set up
echo -e "Migrating and Seeding D.B"
php artisan migrate --seed --force

## check if running inside a pod
echo "Checking if panel is running inside a pod."
if [[ -f "/var/run/secrets/kubernetes.io/serviceaccount/token" ]]; then
    API_SERVER="https://kubernetes.default.svc.cluster.local"
    TOKEN=$(cat /var/run/secrets/kubernetes.io/serviceaccount/token)
    NAMESPACE=$(cat /var/run/secrets/kubernetes.io/serviceaccount/namespace)
    CACERT=/var/run/secrets/kubernetes.io/serviceaccount/ca.crt

    ## check and create cluster
    echo "Checking if cluster already exists."
    output=$(php artisan p:cluster:list --format=json)

    cluster_id=""
    cluster_exists=false

    # Iterate over the JSON array using jq and a for loop
    for i in $(echo "$output" | jq -c '.[]'); do
        host=$(echo "$i" | jq -r '.host')
        if echo "$host" | grep -q "$INGRESS_KUBER"; then
            cluster_id=$(echo "$i" | jq -r '.id | tostring')
        else
            cluster_exists=true
        fi
    done

    if [[ -n "$cluster_id" ]]; then
        echo "Cluster ID found: $cluster_id"
    elif [ -z "$cluster_id" ] && [ "$cluster_exists" = false ]; then
        ## check and create location
        echo "Checking if location already exists."
        output=$(php artisan p:location:make --short="local" --long="Automatically generated for Helm Chart." 2>&1)
        exit_code=$?
        location_id=""

        if [[ $exit_code -eq 0 ]]; then
            location_id=$(echo "$output" | grep -oE "ID of ([0-9]+)" | awk '{print $3}')
            echo "Location creation successful. ID: $location_id"
        else
            if [[ "$output" =~ "The short has already been taken." ]]; then
                echo "Location already exists."
            else
                echo "Location creation failed: $output"
            fi
        fi

        if [[ -n "$location_id" ]]; then
            echo "Creating a cluster."
            output=$(php artisan p:cluster:make --name=local \
                --description="Please review Daemon settings in order to use this cluster." \
                --locationId=$location_id \
                --fqdn=$INGRESS_KUBER \
                --public=0 \
                --scheme=https \
                --proxy=0 \
                --maintenance=0 \
                --uploadSize=128 \
                --daemonListeningPort=443 \
                --daemonBase=/var/lib/kubectyl/volumes \
                --host=$API_SERVER \
                --bearer_token=$TOKEN \
                --insecure=1 \
                --service_type=loadbalancer \
                --storage_class=local-path \
                --namespace=$NAMESPACE \
                --metrics=metrics_api \
                --snapshot_class=csi-rbdplugin-snapclass \
                --external_traffic_policy=cluster)
            echo $output
            cluster_id=$(echo $output | grep -oE "id of [0-9]+" | awk '{print $3}')
        else
            echo "Location ID is empty."
        fi
    else
        echo "Skipping existing clusters."
    fi

    if [[ -n "$cluster_id" ]]; then
        ## create or update kuber configmap
        echo -e "Creating or updating kuber configmap."

        if [[ -f "/etc/nginx/certs/tls.key" && -f "/etc/nginx/certs/tls.crt" ]]; then
            echo -e "using cert-manager certificates"
            YAML_DATA=$(
                php artisan p:cluster:configuration $cluster_id --format=yaml |
                    sed -e 's#^\(\s*cert:\s*\).*#\1/etc/kubectyl/certs/tls.crt#' \
                        -e 's#^\(\s*key:\s*\).*#\1/etc/kubectyl/certs/tls.key#' \
                        -e 's#^\(\s*remote:\s*\).*#\1https://'$INGRESS_PANEL'#' |
                    sed ':a;N;$!ba;s/\n/\\n/g'
            )
        else
            echo -e "using default certificates"
            YAML_DATA=$(php artisan p:cluster:configuration $cluster_id --format=yaml | sed 's#remote: http://localhost#remote: https://'$INGRESS_PANEL'#' | sed ':a;N;$!ba;s/\n/\\n/g')
        fi

        # Check if the ConfigMap exists
        HTTP_STATUS=$(curl -sk -o /dev/null -w "%{http_code}" -H "Authorization: Bearer $TOKEN" \
            "$API_SERVER/api/v1/namespaces/$NAMESPACE/configmaps/kuber-config")

        METHOD="POST"
        ENDPOINT=""
        if [ $HTTP_STATUS -eq 200 ]; then
            # ConfigMap exists, update it
            METHOD="PUT"
            ENDPOINT="kuber-config"
        fi

        curl -ks -X $METHOD -H "Authorization: Bearer $TOKEN" \
            -H "Content-Type: application/json" \
            --data-binary @- \
            "$API_SERVER/api/v1/namespaces/$NAMESPACE/configmaps/$ENDPOINT" <<EOF
{
  "apiVersion": "v1",
  "kind": "ConfigMap",
  "metadata": {
    "name": "kuber-config"
  },
  "data": {
    "config.yml": "$YAML_DATA"
  }
}
EOF

        echo -e "Setting kuber replicas to 1."

        ## prepare the JSON patch payload to update the replicas
        PATCH_PAYLOAD="{ \"spec\": { \"replicas\": 1 } }"

        ## send the patch request using curl
        curl -sk -X PATCH -H "Authorization: Bearer ${TOKEN}" --cacert ${CACERT} \
            -H "Content-Type: application/strategic-merge-patch+json" \
            -d "${PATCH_PAYLOAD}" "${API_SERVER}/apis/apps/v1/namespaces/${NAMESPACE}/deployments/${KUBER_FULLNAME}"
    fi
fi

## start cronjobs for the queue
echo -e "Starting cron jobs."
crond -L /var/log/crond -l 5

echo -e "Starting supervisord."
exec "$@"
