<?php
/*
Plugin Name: Vimeo Vortex
Plugin URI: https://github.com/ms-studio/vimeo-vortex
Description: Improved Vimeo embeds
Version: 1.0.1
Author: Manuel Schmalstieg
Author URI: https://github.com/ms-studio
*/

// Take a Vimeo URL, produce a video

function vimeovortex( $url ) {
	
			// 1: Produce Data
			$videos = vimeovortex_data( $url );
			
			// 2: Produce Output
			vimeovortex_output( $videos );
			
}

/*
 * Parameters
 * $url = vimeo URL
 * $format = can be thumbnail_small (100x75), thumbnail_medium (200x150), thumbnail_large
*/

function vimeovortex_array( $url, $format = "small" ) {
	
			$videos = vimeovortex_data( $url );
			
			return $videos;
			
}


function vimeovortex_data( $url ) {
	
	$vimeo_test = vimeovortex_discover($url);
						
			if (isset($vimeo_test['type'])) {
				$vimeo_type = $vimeo_test['type'];
			}
			if (isset($vimeo_test['id'])) {
				$vimeo_id = $vimeo_test['id'];
			}
			
			/* TEST FOR TRANSIENT
			***************************************/
			
			// delete_transient( $url );
		
		if ( false === ( $videos = get_transient( $url ) ) ) {	
	
			// echo '<p> transient is redefined </p>';
							
			if (isset($vimeo_type) && $vimeo_type == 'channel') {
			
				$api_endpoint = 'http://vimeo.com/api/v2/channel/'.$vimeo_id.'/videos.xml';
				
			} else if (isset($vimeo_type) && $vimeo_type == 'album') {
			
				$api_endpoint = 'http://vimeo.com/api/v2/album/'.$vimeo_id.'/videos.xml';
				
			} else if ($vimeo_type == 'item') { // single video item
			
				$api_endpoint = 'http://vimeo.com/api/v2/video/'.$vimeo_id.'.xml';
			
			} else if ($vimeo_type == 'user') { // user page
			
				$api_endpoint = 'http://vimeo.com/api/v2/'.$vimeo_id.'/videos.xml';
			
			}
			
			$video_xml_data = simplexml_load_string(vimeovortex_curl_get($api_endpoint));
					
			if (!$video_xml_data) {
			 
			 	if ( current_user_can( 'manage_options' ) ) {
			 		echo "<p>Erreur de chargement</p>";
			 	}

			} else {
					
					// transform into array, so we can store it as transient.
					$videos = json_decode( json_encode($video_xml_data) , 1);
					
					if ($vimeo_type == 'user') {
						// are there one or several videos?
						if ( isset( $videos["video"][1]) ) {
							// several videos
							$videos = $videos["video"];
						} else {
							// only one video
							$videos[0] = $videos["video"];
						}
					}
					
					set_transient( 
						$url, 
						$videos, 
						12 * HOUR_IN_SECONDS 
					); 
					
			} // end testing if !empty 
			
	} // end testing for the transient
	
	return $videos;
}


/*
 * Generate output
 * Parameter: $videos (array with data from Vimeo XML)
*/

function vimeovortex_output( $videos ) {

	// test if the array exists 
	if (!empty($videos)) {
 			   
		foreach ($videos as $row => $item) { 
		
				$vid_height = $item["height"];
				$vid_width = $item["width"];
				
				$vid_ratio = ($vid_height / $vid_width)*100;

				$player_url = 'https://player.vimeo.com/video/' . $item["id"] . '?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff&amp;autoplay=1';
				
				$vid_img = $item["thumbnail_large"];
				$vid_img = str_replace("http:","https:",$vid_img);
			
			?><div class="vimeo-item" style="background-image: url(<?php 
						echo $vid_img ;
			?>);" data-ratio="<?php echo $vid_ratio; ?>">
			
			<a href="<?php echo $player_url; ?>" data-vimeo="<?php echo $item["id"]; ?>" target="_blank" title="<?php echo $item["title"]; ?>" data-caption="<?php echo $item["title"]; ?>" class="vimeo-img-link vimeoframe unstyled" style="padding-bottom: <?php 
					echo $vid_ratio; ?>%">
				
					<div class="vimeo-play-icon">Play!</div>
				
					<img class="vimeo-still" src="<?php 
					
					echo $vid_img ;
					
					?>" alt="" />
				
				<div class="vid-legende">
				  <p class="img-title vid-title"><?php 
					
						echo $item["title"] ;
										
				?></p> 
				  <p class="img-caption vid-duration">Durée: <?php 
				
				// duration
				$videosecs = $item["duration"] ;
				
				$init = $videosecs;
				$hours = floor($init / 3600);
				$minutes = floor(($init / 60) % 60);
				$seconds = $init % 60;
				
				// echo "$hours:$minutes:$seconds";
				
				printf("%02d:%02d:%02d", $hours, $minutes, $seconds);
				
				?></p>
				</div><!-- div.img-legende -->
			</a><!-- .vimeoframe -->
			
			  </div><!-- .vimeo-item -->
			<?php
	   				
		} // end Vimeo Foreach 
	} // end test !empty
} // vimeovortex_output

/*
 * Load CSS and JS
 */

function vimeovortex_scripts() {
	
	wp_enqueue_style( 
		'vimeovortex', 
		plugins_url( 'vimeo-vortex.css', __FILE__ )
	);
	
	wp_enqueue_script( 
		'vimeovortexjs', 
		plugins_url( 'vimeo-vortex.js', __FILE__ ), 
		array( 'jquery' ), 
		'20171211', true 
	);

}
add_action( 'wp_enqueue_scripts', 'vimeovortex_scripts' );



/* Curl helper function
 * https://github.com/vimeo/vimeo-oembed-examples/
**/

function vimeovortex_curl_get( $url ) {
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_TIMEOUT, 30);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
	$return = curl_exec($curl);
	curl_close($curl);
	return $return;
}

// Vimeo testing function
// source: http://stackoverflow.com/questions/11304044/determining-the-vimeo-source-by-url-regex

function vimeovortex_discover( $url ) {
  if ((($url = parse_url($url)) !== false)) {
    $url = array_filter(explode('/', $url['path']), 'strlen');
    if (in_array($url[1], array('album', 'channels', 'groups')) !== true) {
        if ( is_numeric($url[1]) ) {
        	array_unshift($url, 'item');
        } else {
        	array_unshift($url, 'user');
        }
    } 
    return array(
    	'type' => rtrim( array_shift($url), 's' ), 
    	'id' => array_shift( $url )
    );
  }
  return false;
}