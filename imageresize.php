<?php

class ImageResize {

	private $max;

	private $width;

	private $height;

	private $square;


	public function __set($name, $value) {
		do {
			if ($name === 'error') break;

			if (!in_array($name, ['max', 'width', 'height', 'square'], true)) {
				trigger_error('Undefined variable: ' . htmlspecialchars($name), E_USER_ERROR);
			}

			if (!$value || !preg_match('#^[0-9]+$#', $value)) {
				trigger_error('An integer value expected for ' . htmlspecialchars($name), E_USER_ERROR);
			}

			if ($name === 'max' && ($this->width || $this->height || $this->square)) {
				trigger_error('Mode already defined', E_USER_ERROR);
			}

			if ($name === 'width' && ($this->max || $this->height || $this->square)) {
				trigger_error('Mode already defined', E_USER_ERROR);
			}

			if ($name === 'height' && ($this->max || $this->width || $this->square)) {
				trigger_error('Mode already defined', E_USER_ERROR);
			}

			if ($name === 'square' && ($this->max || $this->width || $this->height)) {
				trigger_error('Mode already defined', E_USER_ERROR);
			}
		} while (false);

		$this->$name = $value;
	}



	public function process($input, $output=NULL) {
		// Get image type
		$tmp = getimagesize($input);

		if (!$tmp) {
			$this->error = 'Error on getimagesize function';
			return false;
		}

		$type = $tmp[2];


		// Get resource from image
		if ($type === IMAGETYPE_JPEG) {
			$source = imagecreatefromjpeg($input);
		} elseif ($type === IMAGETYPE_PNG) {
			$source = imagecreatefrompng($input);
		} elseif ($type === IMAGETYPE_GIF) {
			$source = imagecreatefromgif($input);
		} else {
			$this->error = 'Image format not supported';
			return false;
		}

		if (!$source) {
			$this->error = 'Error on imagecreatefrom- function';
			return false;
		}


		// Get image size
		$srcW = imagesx($source);
		$srcH = imagesy($source);

		if (!$srcW || !$srcH) {
			$this->error = 'Error on imagesx / imagesy functions';
			return false;
		}


		// Math time!
		$srcX = 0;
		$srcY = 0;

		if ($this->max && $srcW > $srcH) {
			$newW = $this->max;
			$newH = (($srcH * $newW) / $srcW);

		} elseif ($this->max) {
			$newH = $this->max;
			$newW = (($srcW * $newH) / $srcH);

		} elseif ($this->width) {
			$newW = $this->width;
			$newH = (($srcH * $newW) / $srcW);

		} elseif ($this->height) {
			$newH = $this->height;
			$newW = (($srcW * $newH) / $srcH);

		} elseif ($this->square) {
			$newW = $this->square;
			$newH = $this->square;
		}

		if ($this->square && $srcW > $srcH) {
			$srcX = ceil(($srcW - $srcH) / 2);
			$dstW = round($srcW * ($this->square / $srcH));
			$dstH = $this->square;

		} elseif ($this->square) {
			$srcY = ceil(($srcH - $srcW) / 2);
			$dstW = $this->square;
			$dstH = round($srcH * ($this->square / $srcW));

		} else {
			$dstW = $newW;
			$dstH = $newH;
		}


		// Create new canvas
		$canvas = imagecreatetruecolor($newW, $newH);
		if (!$canvas) {
			$this->error = 'Error on imagecreatetruecolor function';
			return false;
		}

		$result = imagecopyresampled($canvas, $source, 0, 0, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH);
		if (!$result) {
			$this->error = 'Error on imagecopyresampled function';
			return false;
		}

		$result = imagejpeg($canvas, $output);
		if (!$result) {
			$this->error = 'Error on imagejpeg function';
			return false;
		}

		return true;
	}
}
