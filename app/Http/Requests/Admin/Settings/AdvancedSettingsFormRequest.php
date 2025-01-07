<?php

namespace Kubectyl\Http\Requests\Admin\Settings;

use Kubectyl\Http\Requests\Admin\AdminFormRequest;

class AdvancedSettingsFormRequest extends AdminFormRequest
{
    /**
     * Return all the rules to apply to this request's data.
     */
    public function rules(): array
    {
        return [
            'recaptcha:enabled' => 'required|in:true,false',
            'recaptcha:secret_key' => 'required|string|max:191',
            'recaptcha:website_key' => 'required|string|max:191',
            'kubectyl:guzzle:timeout' => 'required|integer|between:1,60',
            'kubectyl:guzzle:connect_timeout' => 'required|integer|between:1,60',
            'kubectyl:client_features:allocations:enabled' => 'required|in:true,false',
            'kubectyl:client_features:allocations:range_start' => [
                'nullable',
                'required_if:kubectyl:client_features:allocations:enabled,true',
                'integer',
                'between:1024,65535',
            ],
            'kubectyl:client_features:allocations:range_end' => [
                'nullable',
                'required_if:kubectyl:client_features:allocations:enabled,true',
                'integer',
                'between:1024,65535',
                'gt:kubectyl:client_features:allocations:range_start',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'recaptcha:enabled' => 'reCAPTCHA Enabled',
            'recaptcha:secret_key' => 'reCAPTCHA Secret Key',
            'recaptcha:website_key' => 'reCAPTCHA Website Key',
            'kubectyl:guzzle:timeout' => 'HTTP Request Timeout',
            'kubectyl:guzzle:connect_timeout' => 'HTTP Connection Timeout',
            'kubectyl:client_features:allocations:enabled' => 'Auto Create Allocations Enabled',
            'kubectyl:client_features:allocations:range_start' => 'Starting Port',
            'kubectyl:client_features:allocations:range_end' => 'Ending Port',
        ];
    }
}
