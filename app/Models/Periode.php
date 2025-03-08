<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $table = 'periodes';
    protected $fillable = ['nama', 'tahun'];

    public function hasil()
    {
        return $this->hasMany(Hasil::class);
    }
}
