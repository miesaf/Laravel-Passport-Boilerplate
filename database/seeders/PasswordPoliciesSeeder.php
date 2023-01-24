<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PasswordPolicy;

class PasswordPoliciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        PasswordPolicy::create(['name' => 'Minimum length', 'desc' => 'The minimum number of characters need to have', 'value' => 8, 'status' => false]);
        PasswordPolicy::create(['name' => 'Maximum length', 'desc' => 'The maximum number of characters can have', 'value' => 20, 'status' => false]);
        PasswordPolicy::create(['name' => 'Alphanumeric', 'desc' => 'Combination of numbers and alphabets', 'value' => null, 'status' => false]);
        PasswordPolicy::create(['name' => 'Multicase', 'desc' => 'Combination of upper and lower cases', 'value' => null, 'status' => false]);
        PasswordPolicy::create(['name' => 'Special characters', 'desc' => 'Presence of any special character/symbol', 'value' => null, 'status' => false]);
        PasswordPolicy::create(['name' => 'Maximum failed attempt', 'desc' => 'The maximum number of failed attempt', 'value' => 3, 'status' => false]);
        PasswordPolicy::create(['name' => 'Lock on max failed attempt', 'desc' => 'Lock the account upon reaching the maximum number of failed attempt', 'value' => null, 'status' => false]);
        PasswordPolicy::create(['name' => 'Grace period on max failed attempt', 'desc' => 'Grace period in minutes that prevents any login activity upon reaching the maximum number of failed attempt (regardless of any user ID)', 'value' => 2, 'status' => false]);
        PasswordPolicy::create(['name' => 'Minimum age (days)', 'desc' => 'The minimim number of days before being allowed to change new password', 'value' => 3, 'status' => false]);
        PasswordPolicy::create(['name' => 'Maximum age (days)', 'desc' => 'The maximum number of days before password will be considered expired', 'value' => 90, 'status' => false]);
        PasswordPolicy::create(['name' => 'Dormant account (days)', 'desc' => 'The number of days passed without successful login before account will be considered inactive', 'value' => 90, 'status' => false]);
        PasswordPolicy::create(['name' => 'Prevent reuse (cycles)', 'desc' => 'The number cycles the password cannot be reused again', 'value' => 7, 'status' => false]);
        PasswordPolicy::create(['name' => 'Does not contain user\'s name', 'desc' => 'The password shall not contain any part of user\'s name', 'value' => null, 'status' => false]);
        PasswordPolicy::create(['name' => 'Does not contain user\'s ID', 'desc' => 'The password shall not contain user\'s ID', 'value' => null, 'status' => false]);
        PasswordPolicy::create(['name' => 'Does not contain user\'s email', 'desc' => 'The password shall not contain user\'s email address identifier', 'value' => null, 'status' => false]);
        PasswordPolicy::create(['name' => 'Has not been compromised', 'desc' => 'The password shall not listed in the haveibeenpwned.com database', 'value' => null, 'status' => false]);
    }
}
