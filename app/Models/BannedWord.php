<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;
class BannedWord extends Model {protected $fillable=['word','status'];public function setWordAttribute($value):void{$this->attributes['word']=mb_strtolower(trim($value));}}
