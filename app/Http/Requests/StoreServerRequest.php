<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $server = $this->route('server');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                // Unique name per provider
                Rule::unique('servers')->ignore($server)->where(function ($query) {
                    return $query->where('provider', $this->input('provider'));
                }),
            ],
            'ip_address' => [
                'required',
                'ipv4',
                Rule::unique('servers')->ignore($server),
            ],
            'provider' => 'required|in:aws,digitalocean,vultr,other',
            'status' => 'nullable|in:active,inactive,maintenance',
            'cpu_cores' => 'required|integer|min:1|max:128',
            'ram_mb' => 'required|integer|min:512|max:1048576',
            'storage_gb' => 'required|integer|min:10|max:1048576',
        ];
    }
}
