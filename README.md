# Vimeo Vortex

A WordPress plugin that adds some functionality to improve Vimeo embeds.

Most importantly, it uses the **Vimeo oEmbed API** to load information about the video. 

Based on that, we can build a preview that is just an image, rather than an iframe. When the user clicks on it, we create the iframe and play the video.

When used on pages that load a large amount of videos, that makes a big difference in terms of performance / speed!

Based on previous work on websites including [Kunstraum Kreuzlingen](https://ms-studio.net/portfolio/kunstraum-kreuzlingen/), [Information-Fiction](https://ms-studio.net/webdesign/option-information-fiction/). It is in use on [https://eracom.ch/](https://eracom.ch/) and [https://kinogeneva.ch/](https://kinogeneva.ch/).

## Usage

Currently, there are two helper functions. Both take as input the `$url` of a Vimeo movie.

- `vimeovortex($url)` = will produce a player.
- `vimeovortex_array($url)` = will return the vimeo object as array.

With the second function, if you want to **display the video thumbnail**, you can do the following:

```php
if ( !empty( $video_url ) ) {

	if (function_exists('vimeovortex')) {
		
		$video = vimeovortex_array($video_url);
		
		$video_img = $video["video"]["thumbnail_url"];
						    			
		echo '<img src="'. $video_img .'" alt="" width="'. $video["video"]["width"] .'" height="'. $video["video"]["height"] .'" />';
		
	}
}
```

## Aknowledgements

This plugin uses some code examples kindly provided by the web:

* Curl function courtesy [Vimeo oEmbed API Examples](https://github.com/vimeo/vimeo-oembed-examples/).
* Vimeo testing function courtesy Alix Axel, on [Stack Overflow](https://stackoverflow.com/questions/11304044/determining-the-vimeo-source-by-url-regex).

See also:

* https://ms-studio.net/notes/using-the-vimeo-api-in-wordpress/
* https://developer.vimeo.com/api/oembed/videos

