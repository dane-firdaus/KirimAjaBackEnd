<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BranchOffice extends Model
{
    protected $table = 'mst_branch_office';

    protected $hidden = [
        'created_at', 'updated_at'
    ];
}
