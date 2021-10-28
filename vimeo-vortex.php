<?php
/*
Plugin Name: Vimeo Vortex
Plugin URI: https://github.com/ms-studio/vimeo-vortex
Description: Improved Vimeo embeds
Version: 1.0.2
Author: Manuel Schmalstieg
Author URI: https://github.com/ms-studio
*/

// Take a Vimeo URL, produce a video

function vimeovortex( $url ) {
	
			// 1: Produce Data
			$video = vimeovortex_data( $url );
			
			// 2: Produce Output
			vimeovortex_output( $video );
			
}

/*
 * Parameters
 * $url = vimeo URL
*/

function vimeovortex_array( $url ) {
	
			$video = vimeovortex_data( $url );
			
			return $video;
			
}


function vimeovortex_data( $url ) {
	
			/* TEST FOR TRANSIENT
			*********************/

			// trim whitespaces
			$url = trim($url);

			// if $url is just the vimeo ID, add the beginning:
				if (is_numeric($url)) {
					$url = 'https://vimeo.com/'.$url;
				}

			// delete_transient( $url );
		
		if ( false === ( $video = get_transient( $url ) ) ) {	
	
			$api_endpoint_json = 'https://vimeo.com/api/oembed.json?url='.$url;
			
			$vimeo_api_data = vimeovortex_curl_get($api_endpoint_json);
					
			if (!$vimeo_api_data) {
			 
			 	if ( current_user_can( 'manage_options' ) ) {
			 		echo "<p>Erreur de chargement pour ".$api_endpoint_json."</p>";
			 	}

			} else {
					
					// vimeo API has returned data.
					// transform into array, so we can store it as transient.

					$video = json_decode($vimeo_api_data, true);

					set_transient( 
						$url, 
						$video, 
						15 * DAY_IN_SECONDS 
					); 
					
			} // end testing vimeo API if !empty 
			
	} // end testing for the transient
	
	return $video;
}


/*
 * Generate output
 * Parameter: $video (array with data from Vimeo API)
*/

function vimeovortex_output( $item ) {

	// test if the array exists 
	if (!empty($item)) {
		
//		echo '<pre>VIDEO:';
//		var_dump($item);
//		echo '</pre>';
		
 		$vid_height = $item["height"];
		$vid_width = $item["width"];
				
		if (is_numeric($vid_height)) {
					$vid_ratio = ($vid_height / $vid_width)*100;
		}
				
		$player_url = 'https://player.vimeo.com/video/' . $item["video_id"] . '?title=0&amp;byline=0&amp;portrait=0&amp;color=ffffff&amp;autoplay=1';
				
				$vid_img = $item["thumbnail_url"];
				$vid_img = str_replace("http:","https:",$vid_img);
				$vid_img = str_replace("_295x166.jpg","_".($vid_width*2)."x".($vid_height*2).".jpg",$vid_img);
				
				//$vid_img = 'https://i.vimeocdn.com/video/'.$item["video_id"].'_'.$vid_width.'x'.$vid_height.'.jpg'
			
			?><div class="vimeo-item" style="background-image: url(<?php 
						echo $vid_img ;
			?>);" data-ratio="<?php echo $vid_ratio; ?>">
 		
 		<a href="<?php echo $player_url; ?>" data-vimeo="<?php echo $item["video_id"]; ?>" target="_blank" title="<?php echo $item["title"]; ?>" data-caption="<?php echo $item["title"]; ?>" class="vimeo-img-link vimeoframe unstyled" style="padding-bottom: <?php 
					echo $vid_ratio; ?>%">
				
					<div class="vimeo-play-icon">
						<svg viewBox="0 0 20 20" preserveAspectRatio="xMidYMid" tabindex="-1">
							<title>Play!</title>
							<polygon class="fill" points="1,0 20,10 1,20"></polygon>
						</svg>
					</div>
				
					<img class="vimeo-still" src="<?php 
					
					echo $vid_img ;
					
					?>" alt="" />
				
				<div class="vid-legende">
				  <p class="img-title vid-title"><?php 
					
						echo $item["title"] ;
										
				?></p> 
				  <p class="img-caption vid-duration">Durée: <?php 
				
				// duration
				$videosecs = $item["duration"];
				
				if (is_numeric($videosecs)) {
				 
				 	$init = $videosecs;
					$hours = floor($init / 3600);
					$minutes = floor(($init / 60) % 60);
					$seconds = $init % 60;
				 
				 }
				

				// echo "$hours:$minutes:$seconds";
				
				printf("%02d:%02d:%02d", $hours, $minutes, $seconds);
				
				?></p>
				</div><!-- div.img-legende -->
			</a><!-- .vimeoframe -->
			
			  </div><!-- .vimeo-item -->
			<?php
	} // end test !empty
} // vimeovortex_output

/*
 * Load CSS and JS
 */

function vimeovortex_scripts() {
	
	wp_enqueue_style( 
		'vimeovortex', 
		plugins_url( 'vimeo-vortex.css', __FILE__ ),
		array(),
		'20171212'
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
// Source: http://stackoverflow.com/questions/11304044/determining-the-vimeo-source-by-url-regex
// Author: Alix Axel https://stackoverflow.com/users/89771/alix-axel
// Licence of this code snippet: CC-BY-SA (code published on Stack Overflow before February 1, 2016)

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