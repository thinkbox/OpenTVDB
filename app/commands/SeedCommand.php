<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SeedCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'seed:series';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Seeds the database with a series from tvdb.com';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
	    $x = simplexml_load_string(file_get_contents('http://thetvdb.com/api/3AD69EBF739A8C76/series/'.$this->argument('series_id').'/all/en.xml'));

	    $se = $x->Series;
	    $s = new Series;
	    $s->id = $se->id;
	    $s->name = $se->SeriesName;
	    $s->language = $se->Language;
	    $s->overview = $se->Overview;
	    $s->firstaired = strtotime($se->FirstAired);


	    $n = Network::where('network', $se->Network)->first();
	    if(!$n)
	    {
	    	$n = new Network;
	    	$n->network = $se->Network;
	    	$n->save();
	    }
	    if($n->id)
	    	$s->network_id = $n->id;

	    $s->airday = $se->Airs_DayOfWeek;
	    $s->airtime = $se->Airs_Time;
	    $s->contentrating = $se->ContentRating;
	    $s->runtime = $se->Runtime;
	    $s->status = $se->Status;

	    $s->save();


       if($se->Actors)
       {
            $actors = explode('|', trim($se->Actors, "|"));

            foreach($actors as $ac)
            {
                if(empty(trim($ac))) continue;
                $a = Actors::where('name', $ac)->first();
                if(!$a)
                {
                    $a = new Actors;
                    $a->name = $ac;
                    $a->save();
                }
                $at[] = $a->id;
            }
            if($at)
                $s->actors()->sync($at);
        }	

        if($se->Genre)
        {
        	$genres = explode('|', trim($se->Genre, "|"));
        	foreach($genres as $gen)
        	{
        		$g = Genres::where('genre', $gen)->first();
        		if(!$g)
        		{
        			$g = new Genres;
        			$g->genre = $gen;
        			$g->save();
        		}
        		$gs[] = $g->id;
        	}
        	if($gs)
        		$s->genres()->sync($gs);
        }    

        if($se->banner)
        {
            $img_id = MongoStor::put('http://thetvdb.com/banners/'.$se->banner);
            $i = new Images;
            $i->type = 'banner';
            $i->data_id = $s->id;
            $i->image_id = $img_id;
            $i->save();
        }

        if($se->fanart)
        {
            $img_id = MongoStor::put('http://thetvdb.com/banners/'.$se->fanart);
            $i = new Images;
            $i->type = 'fanart';
            $i->data_id = $s->id;
            $i->image_id = $img_id;
            $i->save();
        }

        if($se->poster)
        {
            $img_id = MongoStor::put('http://thetvdb.com/banners/'.$se->poster);
            $i = new Images;
            $i->type = 'poster';
            $i->data_id = $s->id;
            $i->image_id = $img_id;
            $i->save();
        }

        $s->save();

	    foreach($x->Episode as $e)
	    {
	        $ep = new Episodes;

	        if(!empty($e->Director))
	        {
                $director = explode('|', trim($e->Director, '|'))[0];
	            $d = Directors::where('name', $director)->first();
	            if(!$d)
	            {
	                $d = new Directors;
	                $d->name = $e->Director;
	                $d->save();
	            }
	            $ep->director_id = $d->id;
	        }

	        $ep->series_id = $x->Series->id;

	        $ep->name = $e->EpisodeName;
	        $ep->ep_number = $e->EpisodeNumber;
	        $ep->aired = strtotime($e->FirstAired);
	        $ep->overview = $e->Overview;

	        $s = Seasons::where('season', $e->SeasonNumber)->where('series_id', $x->Series->id)->first();
	        if(!$s)
	        {
	            $s = new Seasons;
	            $s->id = $e->seasonid;
	            $s->series_id = $x->Series->id;
	            $s->season = $e->SeasonNumber;
	            $s->save();
	        }
	        $ep->season_id = $s->id;

	        if($e->Writer)
	        {
	            $writer = explode('|', trim($e->Writer, '|'))[0];
	            $w = Writers::where('name', $writer)->first();
	            if(!$w)
	            {

	                $w = new Writers;
	                $w->name = $writer;
	                $w->save();
	            }
	            $ep->writer_id = $w->id;            
	        }

	        $ep->absolute = (int)$e->absolute_number;
	        $ep->dvd_episode = (int)$e->DVD_episodenumber;
	        $ep->dvd_season = (int)$e->DVD_season;
	        $ep->scene_episode = $ep->dvd_episode;
	        $ep->scene_season = $ep->dvd_season;

	        $ep->save();

	        if($e->GuestStars)
	       {
                $ge = array();
	            $guests = explode('|', trim($e->GuestStars, "|"));
	            foreach($guests as $gs)
	            {
                    if(empty(trim($gs))) continue;
	                $g = Actors::where('name', $gs)->first();
	                if(!$g)
	                {
	                    $g = new Actors;
	                    $g->name = $gs;
	                    $g->save();
	                }
	                $ge[] = $g->id;
	            }
	            if($ge)
	                $ep->guests()->sync($ge);

	        }

	        if($e->filename)
	        {
	            $img_id = MongoStor::put('http://thetvdb.com/banners/'.$e->filename);
	            $i = new Images;
	            $i->type = 'episode';
	            $i->data_id = $ep->id;
	            $i->image_id = $img_id;
	            $i->save();
	            $ep->image_id = $i->id;
	        }

	        $ep->save();
	    }
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('series_id', InputArgument::REQUIRED, 'The series to seed using.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
		);
	}

}
