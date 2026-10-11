<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Unique;

class UpdatePostRequest extends StorePostRequest
{
    /**
     * The slug must be unique among other posts; keeping the post's own slug is fine.
     */
    protected function uniqueSlugRule(): Unique
    {
        return parent::uniqueSlugRule()->ignore($this->route('post'));
    }
}
