<?php

abstract class LastFMAnalyser
{
	protected $_xml;
	
	/* constructor functions */
	private function __construct(SimpleXMLElement $xml) 
	{
		$this->_xml = $xml;
	}

	public static function byFile($filename)
	{
		if (!file_exists($filename))
			return FALSE;
			
		$class = get_called_class();
		$lfa   = new $class(simplexml_load_file($filename));
		return $lfa;
	}
	
	public static function byString($xml_string)
	{
		$class = get_called_class();
		$lfa   = new $class(simplexml_load_string($xml));
		return $lfa;
	}

}

class LastFMRecentTracksAnalyser extends LastFMAnalyser
{
	public function getTrackNames()
	{
		// we suppress this rubbish in case there are funny buggers afoot
		$tracks = @$this->_xml->recenttracks->track;

		if (count($tracks) == 0)
			return FALSE;
		
		$track_names = array();
			
		foreach ($tracks as $track)
			$track_names[] = (string)$track->name;
			
		return $track_names;
	}
}

$track_names = array();

for ($i = 1; $i <= 204; $i++)
{
	$file = './temp/dcchut_p' . $i . '_204.xml';
	
	if (!file_exists($file))
		continue;
		
	$track_names = array_merge($track_names, LastFMRecentTracksAnalyser::byFile($file)->getTrackNames());
}

// create a unique listing of tracks, such that was may give an #id to each track
$unique_tracks = array_unique($track_names);

// reverse the listing of tracks (hence ordered chronologically)
$tracks = array_reverse($track_names);

// create a barebones array detailing transitions between songs
$markov = array();

for ($i = 1; $i < count($tracks); $i++)
{
	$m_key = $tracks[$i - 1];
	$n_key = $tracks[$i];
	
	if (!array_key_exists($m_key, $markov) || !array_key_exists($n_key, $markov[$m_key]))
		$markov[$m_key][$n_key] = 1;
	else 
		$markov[$m_key][$n_key] += 1;
}

$track_listing = '';
$transition_matrix = '';

// create a 'unweighted' transition matrix (will be a 40,000 x 40,000 matrix, I think)
foreach ($unique_tracks as $t1)
{
	// find the total transitions from $t1 to anywhere
	$t = (float)array_sum($markov[$t1]);

	foreach ($unique_tracks as $t2)
	{
		// this cant divide by zero, as the existence of both of these keys guarantees that sum($markov[$t1]) >= 1
		if (array_key_exists($t1, $markov) && array_key_exists($t2, $markov[$t1]))
			$transition_matrix .= $markov[$t1][$t2] / $t . "\t";
		else
			$transition_matrix .= "0\t";
	}
	
	$transition_matrix .= "\n";
	$track_listing     .= $t1 . "\n";
}

file_put_contents('transition.dat', $transition_matrix);
file_put_contents('tracks.txt', $track_listing);
