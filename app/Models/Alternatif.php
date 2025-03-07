<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alternatif extends Model
{
    protected $table = 'alternatifs';
    protected $fillable = ['nik', 'no_kk', 'alamat', 'nama', 'no_hp'];

    public function hasil()
    {
        return $this->hasMany(Hasil::class);
    }

    public function subkriteria()
    {
        return $this->belongsToMany(Subkriteria::class, 'kriteriaalternatifs','alternatif_id', 'subkriteria_id');
    }


}
