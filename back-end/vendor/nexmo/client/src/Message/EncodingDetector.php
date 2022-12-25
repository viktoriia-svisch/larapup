<?php
namespace Nexmo\Message;
class EncodingDetector {
    public function requiresUnicodeEncoding($content)
    {
        $gsmCodePoints = array_map(
            $this->convertIntoUnicode(),
            [ 
                '@', '£', '$', '¥', 'è', 'é', 'ù', 'ì', 'ò', 'ç', "\r", 'Ø', 'ø', "\n", 'Å', 'å',
                'Δ', '_', 'Φ', 'Γ', 'Λ', 'Ω', 'Π', 'Ψ', 'Σ', 'Θ', 'Ξ', 'Æ', 'æ', 'ß', 'É',
                ' ', '!', '"', '#', '¤', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '.', '/',
                '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', ':', ';', '<', '=', '>', '?',
                '¡', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O',
                'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', 'Ä', 'Ö', 'Ñ', 'Ü', '§',
                '¿', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o',
                'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'ä', 'ö', 'ñ', 'ü', 'à',
                "\f", '^', '{', '}', '\\', '[', '~', ']', '|', '€',
            ]
        );
        $textChars = preg_split('
        $textCodePoints = array_map($this->convertIntoUnicode(), $textChars);
        $nonGsmCodePoints = array_diff($textCodePoints, $gsmCodePoints);
        return !empty($nonGsmCodePoints);
    }
    private function convertIntoUnicode()
    {
        return function ($char) {
            $k = mb_convert_encoding($char, 'UTF-16LE', 'UTF-8');
            $k1 = ord(substr($k, 0, 1));
            $k2 = ord(substr($k, 1, 1));
            return $k2 * 256 + $k1;
        };
    }
}
