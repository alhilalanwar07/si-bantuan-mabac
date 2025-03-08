<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hasil extends Model
{
    protected $table = 'hasils';
    protected $fillable = ['alternatif_id', 'nilai', 'keterangan', 'status', 'periode_id'];

    public function alternatif()
    {
        return $this->belongsTo(Alternatif::class);
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class);
    }
}
