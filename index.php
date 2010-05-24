<?php

// let t -> infinity
set_time_limit(0);

// we need the config file to exist
if (!file_exists('config.php') || !require('config.php'))
	exit('config.php does not exist');

// we need the API key!
if (!is_defined('API_KEY'))
	exit('API_KEY not defined in config.php');

// grab a particular page of recently listened tracks
function grab_page($user, $page)
{
	$url   = 'http://ws.audioscrobbler.com/2.0/?method=user.getrecenttracks';
	$url  .= '&user=' . urlencode($user);
	$url  .= '&api_key=' . urlencode(API_KEY);
	$url  .= '&limit=200&page=' . (int)$page;
	
	return file_get_contents($url);
}
	
// grab this users everything
function page_runner($user)
{
	// we grab the first page, to get our max details
	$p1 = grab_page($user, 1);
	
	// get the totalPages var
	$total = (int)strstr(substr(strstr($p1, 'totalPages="'), 12), '"', TRUE);
	
	// we've already grabbed the first page, but who cares, it's only 1 more, right?
	for ($i = 1; $i <= $total; $i++)
	{
		file_put_contents('./temp/' . $user . '_p' . $i . '_' . $total . '.xml', grab_page($user, $i));
		sleep(1); // they hate spammers!
	}
}

// go you stallion
page_runner('dcchut');

