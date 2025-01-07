<?php

namespace Kubectyl\Http\Requests\Admin\Rocket;

use Kubectyl\Http\Requests\Admin\AdminFormRequest;

class RocketFormRequest extends AdminFormRequest
{
    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:191',
            'description' => 'nullable|string',
            'docker_images' => 'required|string',
            'node_selectors' => 'nullable|string',
            'file_denylist' => 'array',
            'startup' => 'required|string',
            'config_from' => 'sometimes|bail|nullable|numeric',
            'config_stop' => 'required_without:config_from|nullable|string|max:191',
            'config_startup' => 'required_without:config_from|nullable|json',
            'config_logs' => 'required_without:config_from|nullable|json',
            'config_files' => 'required_without:config_from|nullable|json',
        ];

        if ($this->method() === 'POST') {
            $rules['launchpad_id'] = 'required|numeric|exists:launchpads,id';
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        $validator->sometimes('config_from', 'exists:rockets,id', function () {
            return (int) $this->input('config_from') !== 0;
        });
    }

    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        return array_merge($data, [
            'force_outgoing_ip' => array_get($data, 'force_outgoing_ip', false),
        ]);
    }
}
