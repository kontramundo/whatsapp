<?php

namespace App\Rules;

use App\Models\Contact;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InvalidEmail implements ValidationRule
{
    public $email;


    public function __construct($email = null)
    {
        $this->email = $email;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $contacts = Contact::where('user_id', auth()->user()->id)
            ->whereHas('user', function ($query) use ($value) {
                $query->where('email', $value)->when($this->email, function ($query) {
                    $query->where('email', '!=', $this->email);
                });
            })->get();

        if ($contacts->count() > 0) {
            $fail('El correo electrónico ingresado, ya se encuentra registrado.');
        }
    }
}
