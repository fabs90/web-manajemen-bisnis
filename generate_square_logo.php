<?php
$srcPath = 'public/dist/assets/static/images/logo_web_new.png';
$destPath = 'public/dist/assets/static/images/logo_square.png';

$img = imagecreatefrompng($srcPath);
if (!$img) {
    die("Failed to open image");
}

$w = imagesx($img);
$h = imagesy($img);
$size = max($w, $h);

$square = imagecreatetruecolor($size, $size);
imagealphablending($square, false);
imagesavealpha($square, true);

$transparent = imagecolorallocatealpha($square, 255, 255, 255, 127);
imagefill($square, 0, 0, $transparent);

$dst_x = intval(($size - $w) / 2);
$dst_y = intval(($size - $h) / 2);

imagecopy($square, $img, $dst_x, $dst_y, 0, 0, $w, $h);

imagepng($square, $destPath);
echo "Image generated successfully at $destPath";
