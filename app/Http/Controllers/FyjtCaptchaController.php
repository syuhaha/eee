<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class FyjtCaptchaController extends Controller
{
    public function image()
    {
        $code = $this->makeCode(4);
        session(['fyjt_captcha' => $code]);

        $w = 140; 
        $h = 54;
        $im = imagecreatetruecolor($w, $h);

        $bg = imagecolorallocate($im, 250, 250, 250);
        imagefilledrectangle($im, 0, 0, $w, $h, $bg);

        // 干扰线
        for ($i = 0; $i < 6; $i++) {
            $c = imagecolorallocate($im, rand(120, 200), rand(120, 200), rand(120, 200));
            imageline($im, rand(0, $w), rand(0, $h), rand(0, $w), rand(0, $h), $c);
        }

        // 干扰点
        for ($i = 0; $i < 180; $i++) {
            $c = imagecolorallocate($im, rand(120, 220), rand(120, 220), rand(120, 220));
            imagesetpixel($im, rand(0, $w - 1), rand(0, $h - 1), $c);
        }

	// 文字：每个字符随机旋转 + 随机上下偏移，内置字体，不依赖 ttf
	// ===== 字更大：先画小字 -> 放大 -> 旋转 -> 贴回主图 =====
	for ($i = 0; $i < 4; $i++) {
	    $char = $code[$i];

	    // 1) 小图写字
	    $srcW = 26; $srcH = 28;
	    $src = imagecreatetruecolor($srcW, $srcH);
	    imagesavealpha($src, true);
	    $transparent = imagecolorallocatealpha($src, 0, 0, 0, 127);
	    imagefill($src, 0, 0, $transparent);

	    $color = imagecolorallocate($src, rand(10, 70), rand(10, 70), rand(10, 70));
	    imagestring($src, 5, 4, 6, $char, $color);

	    // 2) 放大（字体“变大”）
	    $scale = 1.7; // 调大/调小：1.5~2.0
	    $dstW = (int)($srcW * $scale);
	    $dstH = (int)($srcH * $scale);
	    $big = imagecreatetruecolor($dstW, $dstH);
	    imagesavealpha($big, true);
	    $transparent2 = imagecolorallocatealpha($big, 0, 0, 0, 127);
	    imagefill($big, 0, 0, $transparent2);
	    imagecopyresampled($big, $src, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

	    // 3) 旋转
	    $angle = rand(-28, 28);
	    $rot = imagerotate($big, $angle, $transparent2);
	    imagesavealpha($rot, true);

	    // 4) 贴到主图：x 固定步进 + y 随机抖动
	    $x = 6 + $i * 28;
	    $y = rand(0, 10); // 上下不齐
	    imagecopy($im, $rot, $x, $y, 0, 0, imagesx($rot), imagesy($rot));

	    imagedestroy($src);
	    imagedestroy($big);
	    imagedestroy($rot);
	}

	// ===== 波浪干扰线（正弦曲线）=====
	// 画 2 条更像“波浪线穿过笔画”
	for ($k = 0; $k < 2; $k++) {
	    $amp = rand(5, 9);          // 振幅
	    $freq = rand(10, 16) / 10;  // 频率
	    $phase = rand(0, 628) / 100; // 相位 0~2π
	    $baseY = rand(12, 34);      // 基线高度
	    $waveColor = imagecolorallocate($im, rand(60, 140), rand(60, 140), rand(60, 140));

	    $prevX = 0;
	    $prevY = $baseY + (int)($amp * sin($phase));
	    for ($x = 1; $x < $w; $x++) {
	        $y = $baseY + (int)($amp * sin($x / 8 * $freq + $phase));
	        imageline($im, $prevX, $prevY, $x, $y, $waveColor);
	        $prevX = $x;
	        $prevY = $y;
	    }
	}

        ob_start();
        imagepng($im);
        $png = ob_get_clean();
        imagedestroy($im);

        return (new Response($png, 200))
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }

    private function makeCode(int $len): string
    {
		// 构造字符池：包含大小写字母与数字，但排除易混淆的字符（1,l,i,I,O,o,0,Q）
		// 小写 q 允许出现
		$forbidden = ['1','l','i','I','O','o','0','Q'];
		$pool = [];
		foreach (range('A', 'Z') as $c) {
			if (! in_array($c, $forbidden, true)) $pool[] = $c;
		}
		foreach (range('a', 'z') as $c) {
			if (! in_array($c, $forbidden, true)) $pool[] = $c;
		}
		foreach (range(0, 9) as $d) {
			$ds = (string) $d;
			if (! in_array($ds, $forbidden, true)) $pool[] = $ds;
		}

		$out = '';
		$max = count($pool) - 1;
		for ($i = 0; $i < $len; $i++) {
			$out .= $pool[random_int(0, $max)];
		}
		return $out;
    }
}