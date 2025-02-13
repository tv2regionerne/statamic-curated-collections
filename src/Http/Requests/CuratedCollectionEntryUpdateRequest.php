<?php

namespace Tv2regionerne\StatamicCuratedCollection\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Tv2regionerne\StatamicCuratedCollection\Models\CuratedCollectionEntry;

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
     * The validation depends on the curated collection object, but for Private API
     * requests this is not bound to the route automatically, so we need to fetch it.
     */
    protected function prepareForValidation(): void
    {
        if (! $this->curatedCollectionEntry && $this->entry) {
            $this->curatedCollectionEntry = CuratedCollectionEntry::find($this->entry);
        }
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
                'min:1',
            ],
            'expiration_time' => [
                'nullable',
                'integer',
                'min:1',
            ],
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
        if (! $entry) {
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
