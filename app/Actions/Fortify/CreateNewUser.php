<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => $this->passwordRules(),
            'province_code' => ['required'],
            'city_code' => ['required'],
            'district_code' => ['required'],
            'village_code' => ['required'],
            'gender' => ['required', 'in:Laki-laki,Perempuan'],
            'birth_year' => ['required', 'integer', 'digits:4'],
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'province_code' => $input['province_code'],
            'city_code' => $input['city_code'],
            'district_code' => $input['district_code'],
            'village_code' => $input['village_code'],
            'gender' => $input['gender'],
            'birth_year' => $input['birth_year'],
        ]);

        $user->assignRole(2);

        return $user;
    }
}
