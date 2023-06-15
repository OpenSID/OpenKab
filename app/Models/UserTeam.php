<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTeam extends Model
{
    use HasFactory;


     /** {@inheritdoc} */
     protected $table = 'user_team';

     /**
     * The fillable with the model.
     *
     * @var array
     */
    protected $fillable = [
        'id_user',
        'id_team'
   ];

   public $timestamps = false;


}
