<?php
/*
Plugin Name: FB-WP Events
Plugin URI: 
Description: 
Version: 
Author: Josh Ellington
Author URI: http://joshellington.com
License: GPL2
*/

// SET TIMEZONE
date_default_timezone_set('America/Los_Angeles');

function processFbEvent($post) {

	global $_POST;

	// Get event link meta value
	$event_link = get_post_meta($post, 'wvn-event-fblink');
	
	if ( $event_link ) {
		
		// Event ID processing
		$url = (string)$event_link[0];
		$parse_url = parse_url($url);
		$parse_url = str_replace('&index=1','', $parse_url);
		$event_id = str_replace('eid=', '', $parse_url['query']);
		
		// Parse JSON results and set variables
		$fbresults = fbGraph($event_id);
		$desc = $fbresults['description'];
		$name = $fbresults['name'];
		$location = $fbresults['location'];
		$date = date('F j, Y', strtotime($fbresults['start_time']));
		$start_time = date('g:i a', strtotime($fbresults['start_time']));
		$end_time = date('g:i a', strtotime($fbresults['end_time']));
		$venue = $fbresults['venue']['street'].', '.$fbresults['venue']['city'].', '.$fbresults['venue']['city'];
		
		/* DEBUGGING
		$message = $post."\n".'Name: '."\n".$name."\n\n".'Event ID: '."\n".$event_id."\n\n".'Description: '."\n".$desc."\n\n".'Time: '."\n".$start_time.'-'.$end_time;
		
		mail('joshe181@gmail.com', 'Test', $message);
		*/
		
		// NEED TO ADD - Detection of field content		
		update_post_meta($post, 'wvn-event-eventid', $event_id);
		update_post_meta($post, 'wvn-event-desc', $desc);
		update_post_meta($post, 'wvn-event-date', $date);
		update_post_meta($post, 'wvn-event-start', $start_time);
		update_post_meta($post, 'wvn-event-end', $end_time);
		update_post_meta($post, 'wvn-event-location', $location);
		update_post_meta($post, 'wvn-event-venue', $venue);
	
		return $post;
		
	}
	
}

// Retrieve JSON from Facebook Graph API
function fbGraph($id) {
	$feed = file_get_contents('http://graph.facebook.com/'.$id);
    return json_decode($feed, true);
}

add_action('save_post', 'processFbEvent', 100, 5);