<?php

namespace TotpPhp;

/**
 * PHP Class for handling base32.
 */
class Base32
{
    /**
     * Helper class to decode base32.
     *
     * @param $secret
     *
     * @return bool|string
     */
    public function _base32Decode($secret)
    {
        if ($secret === '') {
            return '';
        }

        $base32chars = $this->_getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);

        $paddingCharCount = strlen($secret) - strlen(rtrim($secret, '='));
        $encodedLength = strlen($secret) - $paddingCharCount;
        $paddingByRemainder = [
            0 => 0,
            2 => 6,
            4 => 4,
            5 => 3,
            7 => 1,
        ];
        $remainder = $encodedLength % 8;

        if (!isset($paddingByRemainder[$remainder])) {
            return false;
        }

        if ($paddingCharCount > 0 && (
            strlen($secret) % 8 !== 0 ||
            $paddingCharCount !== $paddingByRemainder[$remainder]
        )) {
            return false;
        }

        $binaryString = '';
        $buffer = 0;
        $bitsAvailable = 0;

        for ($i = 0; $i < $encodedLength; ++$i) {
            $char = $secret[$i];
            if (!isset($base32charsFlipped[$char]) || $base32charsFlipped[$char] > 31) {
                return false;
            }

            $buffer = ($buffer << 5) | $base32charsFlipped[$char];
            $bitsAvailable += 5;

            if ($bitsAvailable >= 8) {
                $bitsAvailable -= 8;
                $binaryString .= chr(($buffer >> $bitsAvailable) & 0xFF);
                $buffer &= (1 << $bitsAvailable) - 1;
            }
        }

        return $binaryString;
    }

    /**
     * Get array with all 32 characters for decoding from/encoding to base32.
     *
     * @return array
     */
    public function _getBase32LookupTable()
    {
        return [
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '=',  // padding char
        ];
    }
}
