<?php

namespace App\Rules;

use App\Models\BaseModel;
use Illuminate\Contracts\Validation\Rule;

class HaveProdeskelRule implements Rule
{
    private $value;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->value = request('value');
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if($attribute == 'key' && $value == 'home_page'){
            if($this->value == 'presisi'){
                if((new BaseModel())->getConnection()->getDoctrineSchemaManager()->tablesExist('prodeskel_potensi')){
                    return true;
                }
                return false;
            }                        
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Website presisi bisa diaktifkan jika data prodeskel telah tersedia';
    }
}
