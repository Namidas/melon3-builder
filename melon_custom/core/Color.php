<?php

function fn_color_from_value($value = null,$format='hex',$rawValue=true) {
	switch(gettype($value))
	{
		case 'boolean':
		case 'integer':
		case 'double':
		case 'string':
			$stringy = (string)$value;
			break;
			
		case 'array':
		case 'object':
		case 'resource':
		case 'NULL':
		case 'unknown type':
			$stringy = serialize($value);
			break;
	}
	
    $md5 = md5($stringy);
    $md5 = preg_replace( '/[^0-9a-fA-F]/', '', $md5 );
    $color = substr( $md5, 0, 6 );
	
	switch($format)
	{
		case 'hex':
			return $rawValue ? $color : "#{$color}";
			break;
			
		case 'rgb':
			$hex = str_split( $color, 1 );
			$rgbd = array_map( 'hexdec', $hex );
			$rgba = array(
				( $rgbd[0] * $rgbd[1] ),
				( $rgbd[2] * $rgbd[3] ),
				( $rgbd[4] * $rgbd[5] ),
			);
			return $rawValue ? $rgba : 'rgb(' . implode(',',$rgba) . ')';
			break;
	}
	return null;
}

function fn_color_get_contrasting($hexColor)
{
        // hexColor RGB
        $R1 = hexdec(substr($hexColor, 1, 2));
        $G1 = hexdec(substr($hexColor, 3, 2));
        $B1 = hexdec(substr($hexColor, 5, 2));

        // Black RGB
        $blackColor = "#000000";
        $R2BlackColor = hexdec(substr($blackColor, 1, 2));
        $G2BlackColor = hexdec(substr($blackColor, 3, 2));
        $B2BlackColor = hexdec(substr($blackColor, 5, 2));

         // Calc contrast ratio
         $L1 = 0.2126 * pow($R1 / 255, 2.2) +
               0.7152 * pow($G1 / 255, 2.2) +
               0.0722 * pow($B1 / 255, 2.2);

        $L2 = 0.2126 * pow($R2BlackColor / 255, 2.2) +
              0.7152 * pow($G2BlackColor / 255, 2.2) +
              0.0722 * pow($B2BlackColor / 255, 2.2);

        $contrastRatio = 0;
        if ($L1 > $L2) {
            $contrastRatio = (int)(($L1 + 0.05) / ($L2 + 0.05));
        } else {
            $contrastRatio = (int)(($L2 + 0.05) / ($L1 + 0.05));
        }

        // If contrast is more than 5, return black color
        if ($contrastRatio > 5) {
            return '#000000';
        } else { 
            // if not, return white color.
            return '#FFFFFF';
        }
}

function fn_color_rgba2hex($string) {
	$rgba  = array();
	$hex   = '';
	$regex = '#\((([^()]+|(?R))*)\)#';
	if (preg_match_all($regex, $string ,$matches)) {
    	$rgba = explode(',', implode(' ', $matches[1]));
	} else {
		$rgba = explode(',', $string);
	}
	
	$rr = dechex($rgba['0']);
	$gg = dechex($rgba['1']);
	$bb = dechex($rgba['2']);
	$aa = '';
	
	if (array_key_exists('3', $rgba)) {
		$aa = dechex($rgba['3'] * 255);
	}
	
	return strtoupper("#$rr$gg$bb$aa");
}


	/*static function extract_colors_rgb($filepath,$colors=1) { return Image::extract_colors($filepath,$colors,true); }

	static function extract_colors($filepath,$colors=1,$rgb=false)
	{
		require_once("League/ColorExtractor/Client.php");
		$pinfo = pathinfo($filepath);
		$ext = strtolower($pinfo["extension"]);
		$client = new ColorExtractor;
		if($ext == "jpg" || $ext == "jpeg") $image = $client->loadJpeg($filepath);
		elseif($ext == "gif")  $image = $client->loadGif($filepath);
		elseif($ext == "png")  $image = $client->loadPng($filepath);
		else return false;
		$res = $image->extract($colors);
		if($rgb && !empty($res)) foreach($res as &$r) $r = Image::hex_to_rgb($r);
		return $res;
	}

	static function hex_to_rgb($hex)
	{
		$hex = str_replace("#", "", $hex);
		if(strlen($hex) == 3) {
		  $r = hexdec(substr($hex,0,1).substr($hex,0,1));
		  $g = hexdec(substr($hex,1,1).substr($hex,1,1));
		  $b = hexdec(substr($hex,2,1).substr($hex,2,1));
		} else {
		  $r = hexdec(substr($hex,0,2));
		  $g = hexdec(substr($hex,2,2));
		  $b = hexdec(substr($hex,4,2));
		}
		$rgb = array($r, $g, $b);
		return $rgb; // returns an array with the rgb values
	}

	static function adjustColorBrightness($hex, $steps) {
		// Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max(-255, min(255, $steps));

		// Normalize into a six character long hex string
		$hex = str_replace('#', '', $hex);
		if (strlen($hex) == 3) {
			$hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
		}

		// Split into three parts: R, G and B
		$color_parts = str_split($hex, 2);
		$return = '#';

		foreach ($color_parts as $color) {
			$color   = hexdec($color); // Convert to decimal
			$color   = max(0,min(255,$color + $steps)); // Adjust color
			$return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
		}

		return $return;
	}

	static function color_blend_by_opacity( $foreground, $opacity, $background=null )
	{
		static $colors_rgb=array(); // stores colour values already passed through the hexdec() functions below.
		$foreground = str_replace('#','',$foreground);
		if( is_null($background) )
			$background = 'FFFFFF'; // default background.

		$pattern = '~^[a-f0-9]{6,6}$~i'; // accept only valid hexadecimal colour values.
		if( !@preg_match($pattern, $foreground)  or  !@preg_match($pattern, $background) )
		{
			trigger_error( "Invalid hexadecimal colour value(s) found", E_USER_WARNING );
			return false;
		}

		$opacity = intval( $opacity ); // validate opacity data/number.
		if( $opacity>100  || $opacity<0 )
		{
			trigger_error( "Opacity percentage error, valid numbers are between 0 - 100", E_USER_WARNING );
			return false;
		}

		if( $opacity==100 )    // $transparency == 0
			return strtoupper( $foreground );
		if( $opacity==0 )    // $transparency == 100
			return strtoupper( $background );
		// calculate $transparency value.
		$transparency = 100-$opacity;

		if( !isset($colors_rgb[$foreground]) )
		{ // do this only ONCE per script, for each unique colour.
			$f = array(  'r'=>hexdec($foreground[0].$foreground[1]),
						 'g'=>hexdec($foreground[2].$foreground[3]),
						 'b'=>hexdec($foreground[4].$foreground[5])    );
			$colors_rgb[$foreground] = $f;
		}
		else
		{ // if this function is used 100 times in a script, this block is run 99 times.  Efficient.
			$f = $colors_rgb[$foreground];
		}

		if( !isset($colors_rgb[$background]) )
		{ // do this only ONCE per script, for each unique colour.
			$b = array(  'r'=>hexdec($background[0].$background[1]),
						 'g'=>hexdec($background[2].$background[3]),
						 'b'=>hexdec($background[4].$background[5])    );
			$colors_rgb[$background] = $b;
		}
		else
		{ // if this FUNCTION is used 100 times in a SCRIPT, this block will run 99 times.  Efficient.
			$b = $colors_rgb[$background];
		}

		$add = array(    'r'=>( $b['r']-$f['r'] ) / 100,
						 'g'=>( $b['g']-$f['g'] ) / 100,
						 'b'=>( $b['b']-$f['b'] ) / 100    );

		$f['r'] += intval( $add['r'] * $transparency );
		$f['g'] += intval( $add['g'] * $transparency );
		$f['b'] += intval( $add['b'] * $transparency );

		return sprintf( '%02X%02X%02X', $f['r'], $f['g'], $f['b'] );
	}

	static function color_sweetspot($hex)
	{
		return Hex::fromString($hex)->sweetspot();
	}*/

?>