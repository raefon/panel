<?php

namespace Kubectyl\Http\Requests\Api\Remote;

use Illuminate\Foundation\Http\FormRequest;

class ReportSnapshotCompleteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'successful' => 'required|boolean',
            'snapcontent' => 'nullable|string|required_if:successful,true',
            'size' => 'nullable|numeric|required_if:successful,true',
            'parts' => 'nullable|array',
            'parts.*.etag' => 'required|string',
            'parts.*.part_number' => 'required|numeric',
        ];
    }
}
