<?php

use \League\ColorExtractor\Client as ColorExtractor;
use \MischiefCollective\ColorJizz\Formats\Hex;

class Image
{
	static function getOPTS($opts=Array())
	{
		$on_broken_file = Config::get("file_broken_do","1");
		$on_broken_file_use = Config::get("file_broken_use","default");
		$on_broken_file_src = $on_broken_file_use == "default" ? "file_broken_default.jpg" : Config::get("file_broken_custom","file_broken_custom.jpg");

		$__OPTS = Array(
			"class" => "",
			"alt" => "-",
			"noGIF" => true,
			"addPath" => false,
			"zcGIF" => false,
			"width" => false,
			"height" => false,
			"zc" => false,

			"resizeUp" => false,
			"jpegQuality" => Config::get("gd_jpeg_quality",100),
			"correctPermissions" => false,
			"preserveAlpha" => true,
			"alphaMaskColor" => array(255,255,255),
			"preserveTransparency" => true,
			"transparencyMaskColor" => array(255,255,255),

			"convertSVG" => false,

			"overwrite" => false,
			"src" => false,

			"includeDimensionsInLocation" => true,
			"outputLocation" => false,

			"on_broken_file" => $on_broken_file,
			"on_broken_file_src" => $on_broken_file_src,
		);
		return array_merge($__OPTS,$opts);
	}

	static function getURL($location,$file,$data = Array(),$auto=true)
	{
		M3::reqCore('FileSystem');
		M3::reqCore('URL');
		
		//__vdump("image get url",$location,$file,$data);
		
		//$fullsrc = Config::get("base_path") . "{$location}{$file}";
		$fullsrc = "{$location}{$file}";
		//__vdump("-",$fullsrc,$location,$file);
		$pathinfo = pathinfo($fullsrc);
		$ext = strtolower(@$pathinfo['extension']);

		$data = Image::getOPTS($data);
		if($data["outputLocation"] === false) $data["outputLocation"] = $location;
		$data["zcGIF"] = false;
		
		$readable = is_readable($fullsrc);
		$valid_exif = true;
		if($readable)
			if($ext != "svg")
				$valid_exif = @exif_imagetype($fullsrc);
		
		if(!$readable || $valid_exif === false || trim($file) == "")
		{
			//M3::trace($readable,$valid_exif,$fullsrc);
			if($data["on_broken_file"] == "1" || $file == $data["on_broken_file_src"]) return false;
			else
			{
				if($file == "Luismi.jpg")
				{
					M3::trace("ELSE");
					exit;
				}
				//M3::trace("ELSE");exit;
				//M3::trace("ELSE");
				//M3::trace($data["on_broken_file_src"]);
				return Image::getURL(Config::get("uploads_folder") . "melon/",$data["on_broken_file_src"],$data,$auto);
			}
		}
		$retOrigFile = false;
		

		/*if(!is_readable($fullsrc) || trim($file) == "")
		{
			if($data["on_broken_file"] == "1" || $file == $data["on_broken_file_src"]) return false;
			else
			{
				//M3::trace($data["on_broken_file_src"]);
				return Image::getURL(Config::get("uploads_folder") . "melon/",$data["on_broken_file_src"],$data,$auto);
			}
		}
		$retOrigFile = false;*/

		//SVGs
		if($ext == "svg" && !$data["convertSVG"]) $retOrigFile = true;

		//GIFs
		if($ext == "gif" && !$data["zcGIF"]) $retOrigFile = true;

		if($retOrigFile)
		{
			if($data["outputLocation"] == $location) return str_replace(" ","%20",Config::get("base_url")  . "{$location}{$file}");
			FileSystem::copy(/*Config::get("base_path") . */"{$location}{$file}",/*Config::get("base_path") . */"{$data["outputLocation"]}{$file}");
			return str_replace(" ","%20",/*Config::get("base_url") . */"{$data["outputLocation"]}{$file}");
		}
		else
		{
			$pathEXTRA = "";
			if($data["includeDimensionsInLocation"] === true)
			{
				$pathEXTRA = "{$data["width"]}x{$data["height"]}";
				if($data["zc"] === true) $pathEXTRA .= "/zc";
				if($data["resizeUp"] === true) $pathEXTRA .= "/resize-up";
			}

			$outputLocation = "{$data["outputLocation"]}{$pathEXTRA}/";
			if($data["src"] === false) $data["src"] = $fullsrc;
			//$fullpath = /*Config::get("base_path") .*/"{$outputLocation}{$file}";
			$fullpath = "{$outputLocation}{$file}";
			//__vdump("PRE FULL URL",$outputLocation,$file);
			$fullurl = fn_url_from_path("{$outputLocation}{$file}");
			//__vdump("POST",$fullurl,str_replace(Config::get("overall_base_path"),Config::get("overall_base_url"),$fullurl),Config::get("base_path"),Config::get("base_url")); 	 
			$exists = is_readable($fullpath);

			if(!$exists && $auto)
			{
				FileSystem::mkdir(/*Config::get("base_path") . */$outputLocation);
				Image::renderToFile($data,$fullpath);
			}
			else if(!$exists && !$auto)
			{
				if($data["on_broken_file"] == "1") return false;
				else return Image::getURL(Config::get("uploads_folder") . "melon/",$data["on_broken_file_src"],$data,$auto);
			}

			return str_replace(" ","%20",$fullurl);
		}
	}

	static function output($src,$data = Array(),$print=true)
	{
		$data = Image::getOPTS($data);

		$imgSrc = Config::get("melon_full_url") . 'Image.php?';
		foreach($data as $k => $v) $imgSrc .= $k . "=" . $v . "&";
		$imgSrc .= 'src=' .  ($data["addPath"]?Config::get("BASE_URL"):"") . $src;

		$pathinfo = pathinfo($src);
		$ext = strtolower(@$pathinfo['extension']);
		if($ext == "gif" && $data["noGIF"]) $output = '<img class="' . $data["class"] . '" style="width:' . $data["width"] . 'px;' . ($data["zcGIF"] ? "height:" . $data["height"] . "px;" : "") . '" src="' . ($data["addPath"]?Config::get("base_url"):"") . $src . '" alt="' . $data["alt"] . '">';
		else $output = '<img class="' . $data["class"] . '" src="' . URL::parse($imgSrc) . '" alt="' . $data["alt"] . '">';
		if($print) echo $output;
		return $output;
	}

	static function renderToFile($userOpts,$outputSRC = "",$debug = false)
	{
		$userOpts = Image::getOPTS($userOpts);
		if(@$userOpts["overwrite"] === false) if(is_readable($outputSRC)) return $outputSRC;

		$outputSRC = trim($outputSRC) == "" ? $userOpts["src"] : $outputSRC;
		//Log::write($outputSRC);
		$img = Image::createIMG($userOpts);
		$res = $img->save($outputSRC);
		return $outputSRC;
	}

	static function render($data)
	{
		ob_start();
		$data = Image::getOPTS($data);

		M3::reqLib('phpthumb');
		try
		{
			$thumb = PhpThumbFactory::create($data["src"]);
			if($data["zc"]) $thumb->adaptiveResize($data["width"], $data["height"]);
			else $thumb->resize($data["width"], $data["height"]);
			$thumb->show();
		}
		catch (Exception $e)
		{
			var_dump($e);
			 // handle error here however you'd like
		}
		header('Content-Disposition: filename=' . $data["src"]);
		header("Content-Length: ". ob_get_length());
		ob_end_flush();
	}

	static function &createIMG($userOpts)
	{
		//M3::trace($userOpts);
		$data = Image::getOPTS($userOpts);
		M3::reqLib('phpthumb');
		$thumb = null;
		$error = false;
		try
		{
			$thumb = PhpThumbFactory::create($data["src"],$data);
			if($data["zc"] || $data["width"] === false || $data["height"] === false) $thumb->adaptiveResize($data["width"], $data["height"]);
			else $thumb->resize($data["width"], $data["height"]);
			//Log::write($thumb->getWorkingImage());
			if($data["resizeUp"])
			{
				$dims = $thumb->getCurrentDimensions();
				if($dims["width"] < $data["width"] || $dims["height"] < $data["height"])
				{
					//mi implementación de resize up
				}
			}
		}
		catch (Exception $e)
		{
			var_dump($e);
			Log::write($e);
			$error = true;
			 // handle error here however you'd like
		}
		if(!$error) return $thumb;
		return null;
	}












	static function extract_colors_rgb($filepath,$colors=1) { return Image::extract_colors($filepath,$colors,true); }

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
	}

	static function perspective_temp($rel_path,$file,$gradient=0.85,$rightdown=0,$background=0xFFFFFF, $alpha=0)
	{
		define("TOP",0);
		define("BOTTOM",1);
		define("LEFT",2);
		define("RIGHT",3);

		$abs_path = $rel_path . $file;

		$i = null;
		$pathinfo = pathinfo($file);
		$ext = strtolower($pathinfo["extension"]);
		switch($ext)
		{
			case "jpg":
			case "jpeg":
				$i = imagecreatefromjpeg($abs_path);
				break;

			case "gif":
				$i = imagecreatefromgif($abs_path);
				break;

			case "png":
				$i = imagecreatefrompng($abs_path);
				break;

			default:
				die("Image::perspective - IMAGE FORMAT NOT SUPPORTED");
				break;
		}

		$w=imagesx($i);
        $h=imagesy($i);
        $col=imagecolorallocatealpha($i,($background>>16)&0xFF,($background>>8)&0xFF,$background&0xFF,$alpha);

        $mult=5;
        $li=imagecreatetruecolor($w*$mult,$h*$mult);
        imagealphablending($li,false);
        imagefilledrectangle($li,0,0,$w*$mult,$h*$mult,$col);
        imagesavealpha($li,true);

        imagecopyresized($li,$i,0,0,0,0,$w*$mult,$h*$mult,$w,$h);
        imagedestroy($i);
        $w*=$mult;
        $h*=$mult;

        $image=imagecreatetruecolor($w,$h);
        imagealphablending($image,false);
        imagefilledrectangle($image,0,0,$w,$h,$col);
        imagealphablending($image,true);

        imageantialias($image,true);
        $test=$h*$gradient;

		$natx = 0;
		$naty = 0;

        $rdmod=$rightdown%2;
        $min=1;
        if($rightdown<2){
            for($y=0;$y<$h;$y++){
                $ny=$rdmod? $y : $h-$y;
                $off=round((1-$gradient)*$w*($ny/$h));
                $t=((1-pow(1-pow(($ny/$h),2),0.5))*(1-$gradient)+($ny/$h)*$gradient);
                $nt=$rdmod? $t : 1-$t;
                if(abs(0.5-$nt)<$min){
                    $min=abs(0.5-$nt);
                    $naty=$off;
                }
                imagecopyresampled($image,$li,
                                    round($off/2),$y,
                                    0,abs($nt*$h),
                                    $w-$off,1,
                                    $w,1);
            }
        } else {
            for($x=0;$x<$w;$x++){
                $nx=$rdmod? $x : $w-$x;
                $off=round((1-$gradient)*$h*($nx/$w));
                $t=((1-pow(1-pow(($nx/$w),2),0.5))*(1-$gradient)+($nx/$w)*$gradient);
                $nt=$rdmod? $t : 1-$t;
                if(abs(0.5-$nt)<$min){
                    $min=abs(0.5-$nt);
                    $natx=$off;
                }
                imagecopyresampled($image,$li,
                                    $x,round($off/2),
                                    abs($nt*$w),0,
                                    1,$h-$off,
                                    1,$h);
            }
        }
        imagedestroy($li);

        imageantialias($image,false);
        imagealphablending($image,false);
        imagesavealpha($image,true);

        $i=imagecreatetruecolor(($w+$naty)/$mult,($h+$natx)/$mult);
        imagealphablending($i,false);
        imagefilledrectangle($i,0,0,($w+$naty)/$mult,($h+$natx)/$mult,$col);
        imagealphablending($i,true);
        imageantialias($i,true);
        imagecopyresampled($i,$image,0,0,0,0,($w+$naty)/$mult,($h+$natx)/$mult,$w,$h);
        imagedestroy($image);
        imagealphablending($i,false);
        imageantialias($i,false);
        imagesavealpha($i,true);
        return $i;
    }

	/*static function perspective()
	{
		//Create new object
		$im = new Imagick();

		//Create new checkerboard pattern
		$im->newPseudoImage(100, 100, "pattern:checkerboard");

		// Set the image format to png
		$im->setImageFormat('png');

		// Fill new visible areas with transparent
		$im->setImageVirtualPixelMethod(Imagick::VIRTUALPIXELMETHOD_TRANSPARENT);

		// Activate matte
		$im->setImageMatte(true);

		// Control points for the distortion
		$controlPoints = array( 10, 10,
								10, 5,

								10, $im->getImageHeight() - 20,
								10, $im->getImageHeight() - 5,

								$im->getImageWidth() - 10, 10,
								$im->getImageWidth() - 10, 20,

								$im->getImageWidth() - 10, $im->getImageHeight() - 10,
								$im->getImageWidth() - 10, $im->getImageHeight() - 30);

		//Perform the distortion
		$im->distortImage(Imagick::DISTORTION_PERSPECTIVE, $controlPoints, true);

		// Ouput the image
		header("Content-Type: image/png");
		echo $im;
	}*/
}

?>
