<?php
class CposImage
{
    public static function wrapText($fontSize, $angle, $fontPath, $text, $maxWidth) {
        $words = explode(' ', $text);
        $lines = [];
        $currentLine = '';
    
        foreach ($words as $word) {
            $testLine = $currentLine . ' ' . $word;
            $bbox = imagettfbbox($fontSize, $angle, $fontPath, trim($testLine));
            $textWidth = $bbox[2] - $bbox[0];
    
            if ($textWidth > $maxWidth) {
                $lines[] = trim($currentLine);
                $currentLine = $word;
            } else {
                $currentLine = $testLine;
            }
        }
        $lines[] = trim($currentLine);
    
        return $lines;
    }

    public static function prepareCenterText($text) {
        return "CENTERED_TEXT: $text\n";
    }

    public static function CenterText($image, $fontSize, $fontPath, $text, $width, $y, $color, $lineHeight) {
        $lines = explode("\n", $text);
        foreach ($lines as $line) {
            if (!empty($line)) {
                $wrappedLines = self::wrapText($fontSize, 0, $fontPath, $line, $width - 20); // Wrap text within the image width
                foreach ($wrappedLines as $wrappedLine) {
                    $bbox = imagettfbbox($fontSize, 0, $fontPath, $wrappedLine);
                    $textWidth = $bbox[2] - $bbox[0];
                    $x = ($width - $textWidth) / 2;
                    imagettftext($image, $fontSize, 0, $x, $y, $color, $fontPath, $wrappedLine);
                    $y += $lineHeight;
                }
            }
        }
        return $y;
    }

    public static function addDottedLine() {
        return "DOTTED_LINE\n";
    }

    public static function addDottedLineOnImage($image, $y, $width, $color, $lineHeight) {
        $x1 = 10;
        $x2 = $width - 10;
        $dottedSpacing = 5;
        for ($x = $x1; $x < $x2; $x += $dottedSpacing * 2) {
            imageline($image, $x, $y, $x + $dottedSpacing, $y, $color);
        }
        return $y +  $lineHeight; // Adjust the vertical position after drawing the dotted line
    }

    public static function prepareColumnText($fontSize, $fontPath, $leftText, $rightText, $maxWidth = 200) {
        $leftLines = self::wrapText($fontSize, 0, $fontPath, $leftText, $maxWidth);
        $preparedText = '';
        foreach ($leftLines as $index => $line) {
            if ($index == 0) {
                $preparedText .= "COLUMN_TEXT: $line\t$rightText\n";
            } else {
                $preparedText .= "COLUMN_TEXT: $line\t\n"; // Align subsequent lines with the right column
            }
        }
        return $preparedText;
    }
    
    public static function LeftRightText($image, $fontSize, $fontPath, $leftText, $rightText, $y, $color, $lineHeight, $width) {
        $bboxLeft = imagettfbbox($fontSize, 0, $fontPath, $leftText);
        $bboxRight = imagettfbbox($fontSize, 0, $fontPath, $rightText);
        $leftX = 10;
        $rightX = $width - $bboxRight[2] - 10;
        imagettftext($image, $fontSize, 0, $leftX, $y, $color, $fontPath, $leftText);
        imagettftext($image, $fontSize, 0, $rightX, $y, $color, $fontPath, $rightText);
        $y += $lineHeight;
        return $y;
    }

    public static function AddLineBreak() {
        return "LINE_BREAK\n";
    }

    public static function AddLineBreakBig() {
        return "LINE_BREAK_BIG\n";
    }

    public static function prepareLeftText($text) {
        return "LEFT_ALIGNED_TEXT: $text\n";
    }

    public static function LeftAlignedText($image, $fontSize, $fontPath, $text, $width, $y, $color, $lineHeight) {
        $lines = self::wrapText($fontSize, 0, $fontPath, $text, $width - 20); // Adjust wrap width to avoid going out of bounds
        foreach ($lines as $line) {
            $x = 10; // Left align text
            imagettftext($image, $fontSize, 0, $x, $y, $color, $fontPath, $line);
            $y += $lineHeight;
        }
        return $y;
    }
    
    public static function prepareFont($text) {
        return "SET_FONT: $text\n";
    }    

    // RAW PRINTING STARST HERE

    public static function printDottedLine($printer, $length = 48) {
        $printer->text(str_repeat('.', $length) . "\n");
    }
    
    public static function printSpacedDottedLine($printer, $length = 48) {
        for ($i = 0; $i < $length; $i++) {
            $printer->text(($i % 2 == 0) ? '.' : ' ');
        }
        $printer->text("\n");
    }
    
    public static function addWordsToBuffer($words, $maxWidth) {
        $buffer = '';
        $line = '';
        foreach ($words as $word) {
            if (strlen($line . ' ' . $word) <= $maxWidth) {
                $line .= ($line === '' ? '' : ' ') . $word;
            } else {
                $buffer .= $line . "\n";
                $line = $word;
            }
        }
        $buffer .= $line;
        return $buffer;
    }

    public static function printTwoColumns($printer, $leftText, $rightText, $lineWidth = 48) {
        // Split the left and right text into words
        $leftWords = explode(' ', $leftText);
        $rightWords = explode(' ', $rightText);
    
        // Calculate the width of each column
        $leftWidth = $lineWidth / 2;
        $rightWidth = $lineWidth / 2;
    
        // Add words to buffers with wrapping
        $leftBuffer = self::addWordsToBuffer($leftWords, $leftWidth);
        $rightBuffer = self::addWordsToBuffer($rightWords, $rightWidth);
    
        // Split buffers into lines
        $leftLines = explode("\n", $leftBuffer);
        $rightLines = explode("\n", $rightBuffer);
    
        // Print lines ensuring both columns align correctly
        $maxLines = max(count($leftLines), count($rightLines));
        for ($i = 0; $i < $maxLines; $i++) {
            $leftLine = isset($leftLines[$i]) ? str_pad($leftLines[$i], $leftWidth) : str_pad('', $leftWidth);
            $rightLine = isset($rightLines[$i]) ? str_pad($rightLines[$i], $rightWidth, ' ', STR_PAD_LEFT) : '';
            $printer->text($leftLine . $rightLine . "\n");
        }
    }

}
// end class