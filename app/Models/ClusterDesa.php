<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClusterDesa extends Model
{
    /** {@inheritdoc} */
    protected $connection = 'openkab';

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tweb_wil_clusterdesa';
}
