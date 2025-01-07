<?php

namespace Kubectyl\Http\Requests\Admin;

use Kubectyl\Models\Server;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ServerFormRequest extends AdminFormRequest
{
    /**
     * Rules to be applied to this request.
     */
    public function rules(): array
    {
        $rules = Server::getRules();
        $rules['description'][] = 'nullable';
        $rules['custom_image'] = 'sometimes|nullable|string';
        $rules['node_selectors'] = 'nullable|string';

        return $rules;
    }

    /**
     * Run validation after the rules above have been applied.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $validator->sometimes('cluster_id', 'required|numeric|bail|exists:clusters,id', function ($input) {
                return !$input->auto_deploy;
            });

            $validator->sometimes('allocation_id', [
                'required',
                'numeric',
                'bail',
                Rule::exists('allocations', 'id')->where(function ($query) {
                    $query->where('cluster_id', $this->input('cluster_id'));
                    $query->whereNull('server_id');
                }),
            ], function ($input) {
                return !$input->auto_deploy;
            });

            $validator->sometimes('allocation_additional.*', [
                'sometimes',
                'required',
                'numeric',
                Rule::exists('allocations', 'id')->where(function ($query) {
                    $query->where('cluster_id', $this->input('cluster_id'));
                    $query->whereNull('server_id');
                }),
            ], function ($input) {
                return !$input->auto_deploy;
            });
        });
    }
}
