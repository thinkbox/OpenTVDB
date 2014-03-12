<?php

class Episodes extends BaseModel {
    protected $table = 'episodes';

    public function season()
    {
        return $this->hasOne('Seasons', 'id', 'season_id');
    }   

    public function director()
    {
        return $this->hasOne('Directors', 'id', 'director_id');
    }    

    public function writer()
    {
        return $this->hasOne('Writers', 'id', 'writer_id');
    }

    public function guests()
    {
        return  $this->belongsToMany('Actors', 'guests', 'episode_id', 'actor_id');
    }

    public function image()
    {
        return $this->hasOne('Images', 'id', 'image_id');
    }
}