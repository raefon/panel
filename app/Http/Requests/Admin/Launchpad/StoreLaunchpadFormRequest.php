<?php

namespace Kubectyl\Http\Requests\Admin\Launchpad;

use Kubectyl\Http\Requests\Admin\AdminFormRequest;

class StoreLaunchpadFormRequest extends AdminFormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|min:1|max:191',
            'description' => 'string|nullable',
        ];
    }
}
