<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class CuratedCollectionEntryUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        // Solved by model binding
        $curatedCollectionEntry = $this->curatedCollectionEntry;

        // validations for draft entries
        $draftvalidations = [
            'publish_order' => [
                'nullable',
                'integer',
                'min:1'
            ],
            'expiration_time' => [
                'nullable',
                'integer',
                'min:1'
            ]
        ];

        // validations for published entries
        $publishedValidations = [
            'order' => [
                'sometimes',
                'integer',
                'min:1',
            ],
        ];

        // resolve the entry to know if it's a draft or published entry
        $entry = $curatedCollectionEntry->entry();

        // no resolution. Basic validation will handle this
        if (!$entry) {
            throw ValidationException::withMessages(['entry' => 'The entry cant be resolved']);
        }

        // Published validation rules
        if ($entry->status() === 'published') {
            return $publishedValidations;
        }

        // Draft validation rules
        return $draftvalidations;
    }
}
