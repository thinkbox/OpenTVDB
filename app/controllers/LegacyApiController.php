<?php

class LegacyApiController extends BaseController {

	public function getSeries()
	{
		
		$seriesSearch = Input::get('seriesname');


		$series = Episodes::with('writer')->with('guests')->with('director')->with('season')->find(1);

		print_r($series);
		die;

		if(!$seriesSearch)
		{
			$xml = new SimpleXMLElement('<Error>seriesname is required</Error>');
		}
		else
		{
			$xml = new SimpleXMLElement('<Data></Data>');
		}

		$series = Series::where('name', 'LIKE', '%'.$seriesSearch.'%')->with('aliases')->remember('10')->get();
		foreach($series as $s)
		{
			$x = $xml->addChild('Series');
			$x->addChild('seriesid', $s->id);
			
			$x->addChild('language', $s->language);
			
			$x->addChild('SeriesName', $s->name);

			foreach($s->aliases as $a) $al[] = $a->name;
			if(isset($al))
				$x->addChild('AliasNames', '|'.implode('|', $al).'|');
				
			
			if($s->overview)
				$x->addChild('Overview', $s->overview);
			
			if($s->firstaired)
				$x->addChild('FirstAired', Carbon\Carbon::createFromTimeStamp($s->firstaired)->toDateString());

			if($s->contentrating)
				$x->addChild('ContentRating', $s->contentrating);
			
			if($s->network->network)
				$x->addChild('Network', $s->network->network);

			if($s->imdbid)
				$x->addChild('IMDB_ID', $s->imdbid);
			
			$x->addChild('id', $s->id);
		}

		$response = Response::make($xml->asXML(), "200");
		$response->header('Content-Type', "application/xml");
		return $response;
	}

	public function getSeriesBaseRecord($apikey, $id)
	{
		
		$series = Series::where('id',$id)->with('genres')->with('actors')->with('aliases')->first();

		if(!$series)
			return App::abort(404);

		$xml = new SimpleXMLElement('<Data></Data>');
		$x = $xml->addChild('Series');
		$x->addChild('seriesid', $series->id);


		foreach($series->actors as $a) $ac[] = $a->name;
		if(isset($ac))
			$x->addChild('Actors', '|'.implode('|', $ac).'|');
		
		$x->addChild('language', $series->language);
		
		$x->addChild('SeriesName', $series->name);

		foreach($series->aliases as $a) $al[] = $a->name;
		if(isset($al))
			$x->addChild('AliasNames', '|'.implode('|', $al).'|');

		if($series->firstaired)
			$x->addChild('FirstAired', Carbon\Carbon::createFromTimeStamp($series->firstaired)->toDateString());
		
		if($series->airday)
			$x->addChild('Airs_DayOfWeek', $series->airday);

		if($series->airtime)
			$x->addChild('Airs_Time', $series->airtime);

		if($series->contentrating)
			$x->addChild('ContentRating', $series->contentrating);
		
		if($series->overview)
			$x->addChild('Overview', $series->overview);
		
		$x->addChild('Rating', number_format(SeriesRating::where('series_id', $series->id)->avg('rating')));
		$x->addChild('RatingCount', SeriesRating::where('series_id', $series->id)->count());

		foreach($series->genres as $g) $ge[] = $g->genre;
		if(isset($ge))
			$x->addChild('Genres', '|'.implode('|', $ge).'|');				

		
		if($series->network)
			$x->addChild('Network', $series->network->network);

		if($series->imdbid)
			$x->addChild('IMDB_ID', $series->imdbid);

		$x->addChild('lastupdated', $series->updated_at);
		
		$x->addChild('id', $series->id);
	

		$response = Response::make($xml->asXML(), "200");
		$response->header('Content-Type', "application/xml");
		return $response;
	}


	public function getSeriesAll($apikey, $id)
	{
		
		$series = Series::where('id',$id)->with('genres')->with('actors')->with('aliases')->with('episodes')->first();

		if(!$series)
			return App::abort(404);

		$xml = new SimpleXMLElement('<Data></Data>');
		$x = $xml->addChild('Series');
		$x->addChild('seriesid', $series->id);


		foreach($series->actors as $a) $ac[] = $a->name;
		if(isset($ac))
			$x->addChild('Actors', '|'.implode('|', $ac).'|');
		
		$x->addChild('language', $series->language);
		
		$x->addChild('SeriesName', $series->name);

		foreach($series->aliases as $a) $al[] = $a->name;
		if(isset($al))
			$x->addChild('AliasNames', '|'.implode('|', $al).'|');

		if($series->firstaired)
			$x->addChild('FirstAired', Carbon\Carbon::createFromTimeStamp($series->firstaired)->toDateString());
		
		if($series->airday)
			$x->addChild('Airs_DayOfWeek', $series->airday);

		if($series->airtime)
			$x->addChild('Airs_Time', $series->airtime);

		if($series->contentrating)
			$x->addChild('ContentRating', $series->contentrating);
		
		if($series->overview)
			$x->addChild('Overview', $series->overview);
		
		$x->addChild('Rating', number_format(SeriesRating::where('series_id', $series->id)->avg('rating')));
		$x->addChild('RatingCount', SeriesRating::where('series_id', $series->id)->count());

		foreach($series->genres as $g) $ge[] = $g->genre;
		if(isset($ge))
			$x->addChild('Genres', '|'.implode('|', $ge).'|');				

		
		if($series->network)
			$x->addChild('Network', $series->network->network);

		if($series->imdbid)
			$x->addChild('IMDB_ID', $series->imdbid);

		$x->addChild('lastupdated', $series->updated_at);
		
		$x->addChild('id', $series->id);

		foreach($series->episodes as $ep)
		{
			$e = $xml->addChild('Episode');

			$e->addChild('DVD_episodenumber', $ep->dvd_episode);
			$e->addChild('DVD_season', $ep->dvd_season);

			if($ep->director)
				$e->addChild('Director', $ep->director->name);

			$e->addChild('EpisodeName', $ep->name);
			$e->addChild('EpisodeNumber', $ep->ep_number);
			$e->addChild('FirstAired', Carbon\Carbon::createFromTimeStamp($ep->aired)->toDateString());

			foreach($ep->guests as $g) $gu[] = $g->name;
			if(isset($gu))
				$e->addChild('GuestStars', '|'.implode('|', $gu).'|');	

			$e->addChild('Language', 'en');
			$e->addChild('Overview', $ep->overview);

			if($ep->season)
				$e->addChild('SeasonNumber', $ep->season->season);

			if($ep->writer)
				$e->addChild('Writer', $ep->writer->name);
			
			$e->addChild('absolute_number', $ep->absolute);

			$e->addChild('lastupdated', $ep->updated_at);
			$e->addChild('seasonid', $ep->season_id);
			$e->addChild('seriesid', $ep->series_id);

			if($ep->image)
				$e->addChild('filename', 'images/'.$ep->image->type.'/'.$ep->image->data_id.'/'.$ep->image->id.'.jpg');

		}
	

		$response = Response::make($xml->asXML(), "200");
		$response->header('Content-Type', "application/xml");
		return $response;
	}
	

}