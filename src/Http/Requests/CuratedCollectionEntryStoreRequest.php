<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Statamic\Facades\Entry;

class CuratedCollectionEntryStoreRequest extends FormRequest
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
        $curatedCollection = $this->curatedCollection;

        // validation rules for both draft and published entries
        $basicValidation = [
            'curated_collection' => [
                'required',
                Rule::exists('curated_collections', 'handle'),
            ],
            'entry.0' => [
                'required',
                Rule::unique('curated_collection_entries', 'entry_id')
                    ->where('curated_collection_id', $curatedCollection->id),
            ],
        ];

        // validations for draft entries
        $draftvalidations = [
            'publish_order' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'expiration_time' => [
                'sometimes',
                'integer',
                'min:1',
            ],
        ];

        // validations for published entries
        $publishedValidations = [
            'order' => [
                'nullable',
                'integer',
                'min:1',
            ],
        ];

        // resolve the entry to know if it's a draft or published entry
        $entry = Entry::find($this->input('entry.0'));

        // no resolution. Basic validation will handle this
        if (! $entry) {
            return $basicValidation;
        }

        // Published validation rules
        if ($entry->status() === 'published') {
            return array_merge($basicValidation, $publishedValidations);
        }

        // Draft validation rules
        return array_merge($basicValidation, $draftvalidations);
    }
}
