<?php

namespace Kubectyl\Http\Requests\Admin;

use Kubectyl\Models\Mount;

class MountFormRequest extends AdminFormRequest
{
    /**
     * Set up the validation rules to use for these requests.
     */
    public function rules(): array
    {
        if ($this->method() === 'PATCH') {
            return Mount::getRulesForUpdate($this->route()->parameter('mount')->id);
        }

        return Mount::getRules();
    }
}
