<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subkriteria extends Model
{
    protected $table = 'subkriterias';
    protected $fillable = ['kriteria_id', 'nama', 'bobot', 'deskripsi'];

    public function kriteria()
    {
        return $this->belongsTo(Kriteria::class);
    }

    public function alternatif()
    {
        return $this->belongsToMany(Alternatif::class, 'hasil', 'subkriteria_id', 'alternatif_id');
    }
}
