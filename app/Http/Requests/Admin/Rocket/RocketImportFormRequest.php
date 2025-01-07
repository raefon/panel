<?php

namespace Kubectyl\Http\Requests\Admin\Rocket;

use Kubectyl\Http\Requests\Admin\AdminFormRequest;

class RocketImportFormRequest extends AdminFormRequest
{
    public function rules(): array
    {
        $rules = [
            'import_file' => 'bail|required|file|max:1000|mimetypes:application/json,text/plain',
        ];

        if ($this->method() !== 'PUT') {
            $rules['import_to_launchpad'] = 'bail|required|integer|exists:launchpads,id';
        }

        return $rules;
    }
}
