<?php

class Pimage {
	
	function createResizedImages($fileName) {
		
		$CI =& get_instance();
		
		$image = imagecreatefromjpeg($CI->config->item('ppo_images_path').$fileName.'.jpg');

		$_image = imagecreatetruecolor($CI->config->item('ppo_images_large_width'), $CI->config->item('ppo_images_large_height'));
		imagecopyresampled($_image,$image,0,0,0,0,$CI->config->item('ppo_images_large_width'),$CI->config->item('ppo_images_large_height'),imagesx($image),imagesy($image));
		imagejpeg($_image, $CI->config->item('ppo_images_path').$fileName.$CI->config->item('ppo_images_large_sufix').'.jpg');
		imagedestroy($_image);
		
		$_image = imagecreatetruecolor($CI->config->item('ppo_images_medium_width'), $CI->config->item('ppo_images_medium_height'));
		imagecopyresampled($_image,$image,0,0,0,0,$CI->config->item('ppo_images_medium_width'),$CI->config->item('ppo_images_medium_height'),imagesx($image),imagesy($image));
		imagejpeg($_image, $CI->config->item('ppo_images_path').$fileName.$CI->config->item('ppo_images_medium_sufix').'.jpg');
		imagedestroy($_image);
		
		$_image = imagecreatetruecolor($CI->config->item('ppo_images_small_width'), $CI->config->item('ppo_images_small_height'));
		imagecopyresampled($_image,$image,0,0,0,0,$CI->config->item('ppo_images_small_width'),$CI->config->item('ppo_images_small_height'),imagesx($image),imagesy($image));
		imagejpeg($_image, $CI->config->item('ppo_images_path').$fileName.$CI->config->item('ppo_images_small_sufix').'.jpg');
		imagedestroy($_image);

		imagedestroy($image);
		
		return true;
		
	}

	function Pimage() {

	}
	
}