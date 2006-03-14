<?

$image = imagecreate(60, 60);

$black = imagecolorallocate($image, 0x00, 0x00, 0x00);
$white = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);

$color[] = imagecolorallocate($image, 0xFF, 0xFF, 0x00);
$aacid[] = 'C';
$color[] = imagecolorallocate($image, 0xFF, 0xFF, 0x00);
$aacid[] = 'M';

$color[] = imagecolorallocate($image, 0xFF, 0x00, 0x00);
$aacid[] = 'D';
$color[] = imagecolorallocate($image, 0xFF, 0x00, 0x00);
$aacid[] = 'E';

$color[] = imagecolorallocate($image, 0x66, 0x66, 0x66);
$aacid[] = 'G';
$color[] = imagecolorallocate($image, 0x66, 0x66, 0x66);
$aacid[] = 'P';

$color[] = imagecolorallocate($image, 0x88, 0x00, 0xFF);
$aacid[] = 'H';

$color[] = imagecolorallocate($image, 0xFF, 0x00, 0xFF);
$aacid[] = 'N';
$color[] = imagecolorallocate($image, 0xFF, 0x00, 0xFF);
$aacid[] = 'Q';

$color[] = imagecolorallocate($image, 0x33, 0xFF, 0x22);
$aacid[] = 'F';
$color[] = imagecolorallocate($image, 0x33, 0xFF, 0x22);
$aacid[] = 'W';
$color[] = imagecolorallocate($image, 0x33, 0xFF, 0x22);
$aacid[] = 'Y';

$color[] = imagecolorallocate($image, 0x33, 0xFF, 0x00);
$aacid[] = 'A';
$color[] = imagecolorallocate($image, 0x33, 0xFF, 0x00);
$aacid[] = 'I';
$color[] = imagecolorallocate($image, 0x33, 0xFF, 0x00);
$aacid[] = 'L';
$color[] = imagecolorallocate($image, 0x33, 0xFF, 0x00);
$aacid[] = 'V';

$color[] = imagecolorallocate($image, 0x00, 0x5F ,0xA9);
$aacid[] = 'K';
$color[] = imagecolorallocate($image, 0x00, 0x5F ,0xA9);
$aacid[] = 'R';

$color[] = imagecolorallocate($image, 0x33, 0xFF, 0x44);
$aacid[] = 'T';
$color[] = imagecolorallocate($image, 0x33, 0xFF, 0x44);
$aacid[] = 'S';

$rows = 4;
$cols = 4;

$left = 3;
$top = 3;

$space = 1;

$size = 5;
for($row=0;$row<=$rows;$row++)
{
	for($col=0;$col<=$cols;$col++)
	{
		$random = rand(0, count($color) - 1);
		
		imagefilledrectangle(	$image, 
					$left + ( $col * 11), 
					$top + ( $row * 11 ), 
					$left + ( $col * 11 ) + 9,
					$top + ( $row * 11 ) + 9,
					$color[$random]);
/*
		imagettftext(	$image,
				8,
				0,
				$left + 2 + ( $col * 11 ),
				$top - 2 + ( ($row+1) * 11 ),
				$black,
				'arial.ttf',
				$aacid[$random]);*/
	}
}

# Send header
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>
