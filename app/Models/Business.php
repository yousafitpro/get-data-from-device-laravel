<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;
    protected $guarded=[];
    public $SRC='images/';

    public function headerLogo()
    {
        return asset($this->SRC.$this->header_logo);
    }

    public function favicon()
    {
        return asset($this->SRC.$this->favicon);
    }

}
