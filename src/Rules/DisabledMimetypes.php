<?php

namespace Gigcodes\AssetManager\Rules;

use Illuminate\Contracts\Validation\Rule;

class DisabledMimetypes implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $mimetype_blacklist = explode(',', config('asset-manager.mimes.blacklisted'));
        $file_mimetype = explode('/', $value->getMimeType());

        return ! array_intersect($file_mimetype, $mimetype_blacklist);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
