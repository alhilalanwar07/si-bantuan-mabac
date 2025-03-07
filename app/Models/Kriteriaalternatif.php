<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kriteriaalternatif extends Model
{
    protected $table = 'kriteriaalternatifs';
    protected $fillable = ['subkriteria_id', 'alternatif_id', 'nilai'];

    public function alternatif()
    {
        return $this->belongsTo(Alternatif::class);
    }

    public function subkriteria()
    {
        return $this->belongsTo(Subkriteria::class);
    }
}
