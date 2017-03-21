<?php
function slash( $value ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $k => $v ) {
			if ( is_array( $v ) ) {
				$value[$k] = slash( $v );
			} else {
				$value[$k] = addslashes( $v );
			}
		}
	} else {
		$value = addslashes( $value );
	}

	return $value;
}
/* Converts all accent characters to ASCII characters.
*
* If there are no accent characters, then the string given is just returned.
*
* **Accent characters converted:**
*/
function remove_accents($string) {
    if (!preg_match('/[\x80-\xff]/', $string))
        return $string;

    if (seems_utf8($string)) {
        $chars = array(
            // Decompositions for Latin-1 Supplement
            'a' => 'a',
            'o' => 'o',
            '��' => 'A',
            '��' => 'A',
            '?' => 'A',
            '?' => 'A',
            '?' => 'A',
            '?' => 'A',
            '?' => 'AE',
            '?' => 'C',
            '��' => 'E',
            '��' => 'E',
            '��' => 'E',
            '?' => 'E',
            '��' => 'I',
            '��' => 'I',
            '?' => 'I',
            '?' => 'I',
            'D' => 'D',
            '?' => 'N',
            '��' => 'O',
            '��' => 'O',
            '?' => 'O',
            '?' => 'O',
            '?' => 'O',
            '��' => 'U',
            '��' => 'U',
            '?' => 'U',
            '��' => 'U',
            'Y' => 'Y',
            'T' => 'TH',
            '?' => 's',
            '��' => 'a',
            '��' => 'a',
            'a' => 'a',
            '?' => 'a',
            '?' => 'a',
            '?' => 'a',
            '?' => 'ae',
            '?' => 'c',
            '��' => 'e',
            '��' => 'e',
            '��' => 'e',
            '?' => 'e',
            '��' => 'i',
            '��' => 'i',
            '?' => 'i',
            '?' => 'i',
            'e' => 'd',
            '?' => 'n',
            '��' => 'o',
            '��' => 'o',
            '?' => 'o',
            '?' => 'o',
            '?' => 'o',
            '?' => 'o',
            '��' => 'u',
            '��' => 'u',
            '?' => 'u',
            '��' => 'u',
            'y' => 'y',
            't' => 'th',
            '?' => 'y',
            '?' => 'O',
            // Decompositions for Latin Extended-A
            '��' => 'A',
            '��' => 'a',
            '?' => 'A',
            '?' => 'a',
            '?' => 'A',
            '?' => 'a',
            '?' => 'C',
            '?' => 'c',
            '?' => 'C',
            '?' => 'c',
            '?' => 'C',
            '?' => 'c',
            '?' => 'C',
            '?' => 'c',
            '?' => 'D',
            '?' => 'd',
            '?' => 'D',
            '?' => 'd',
            '��' => 'E',
            '��' => 'e',
            '?' => 'E',
            '?' => 'e',
            '?' => 'E',
            '?' => 'e',
            '?' => 'E',
            '?' => 'e',
            '��' => 'E',
            '��' => 'e',
            '?' => 'G',
            '?' => 'g',
            '?' => 'G',
            '?' => 'g',
            '?' => 'G',
            '?' => 'g',
            '?' => 'G',
            '?' => 'g',
            '?' => 'H',
            '?' => 'h',
            '?' => 'H',
            '?' => 'h',
            '?' => 'I',
            '?' => 'i',
            '��' => 'I',
            '��' => 'i',
            '?' => 'I',
            '?' => 'i',
            '?' => 'I',
            '?' => 'i',
            '?' => 'I',
            '?' => 'i',
            '?' => 'IJ',
            '?' => 'ij',
            '?' => 'J',
            '?' => 'j',
            '?' => 'K',
            '?' => 'k',
            '?' => 'k',
            '?' => 'L',
            '?' => 'l',
            '?' => 'L',
            '?' => 'l',
            '?' => 'L',
            '?' => 'l',
            '?' => 'L',
            '?' => 'l',
            '?' => 'L',
            '?' => 'l',
            '?' => 'N',
            '��' => 'n',
            '?' => 'N',
            '?' => 'n',
            '?' => 'N',
            '��' => 'n',
            '?' => 'n',
            '?' => 'N',
            '?' => 'n',
            '��' => 'O',
            '��' => 'o',
            '?' => 'O',
            '?' => 'o',
            '?' => 'O',
            '?' => 'o',
            '?' => 'OE',
            '?' => 'oe',
            '?' => 'R',
            '?' => 'r',
            '?' => 'R',
            '?' => 'r',
            '?' => 'R',
            '?' => 'r',
            '?' => 'S',
            '?' => 's',
            '?' => 'S',
            '?' => 's',
            '?' => 'S',
            '?' => 's',
            '?' => 'S',
            '?' => 's',
            '?' => 'T',
            '?' => 't',
            '?' => 'T',
            '?' => 't',
            '?' => 'T',
            '?' => 't',
            '?' => 'U',
            '?' => 'u',
            '��' => 'U',
            '��' => 'u',
            '?' => 'U',
            '?' => 'u',
            '?' => 'U',
            '?' => 'u',
            '?' => 'U',
            '?' => 'u',
            '?' => 'U',
            '?' => 'u',
            '?' => 'W',
            '?' => 'w',
            '?' => 'Y',
            '?' => 'y',
            '?' => 'Y',
            '?' => 'Z',
            '?' => 'z',
            '?' => 'Z',
            '?' => 'z',
            '?' => 'Z',
            '?' => 'z',
            '?' => 's',
            // Decompositions for Latin Extended-B
            '?' => 'S',
            '?' => 's',
            '?' => 'T',
            '?' => 't',
            // Euro Sign
            '�' => 'E',
            // GBP (Pound) Sign
            '��' => '',
            // Vowels with diacritic (Vietnamese)
            // unmarked
            '?' => 'O',
            '?' => 'o',
            '?' => 'U',
            '?' => 'u',
            // grave accent
            '?' => 'A',
            '?' => 'a',
            '?' => 'A',
            '?' => 'a',
            '?' => 'E',
            '?' => 'e',
            '?' => 'O',
            '?' => 'o',
            '?' => 'O',
            '?' => 'o',
            '?' => 'U',
            '?' => 'u',
            '?' => 'Y',
            '?' => 'y',
            // hook
            '?' => 'A',
            '?' => 'a',
            '?' => 'A',
            '?' => 'a',
            '?' => 'A',
            '?' => 'a',
            '?' => 'E',
            '?' => 'e',
            '?' => 'E',
            '?' => 'e',
            '?' => 'I',
            '?' => 'i',
            '?' => 'O',
            '?' => 'o',
            '?' => 'O',
            '?' => 'o',
            '?' => 'O',
            '?' => 'o',
            '?' => 'U',
            '?' => 'u',
            '?' => 'U',
            '?' => 'u',
            '?' => 'Y',
            '?' => 'y',
            // tilde
            '?' => 'A',
            '?' => 'a',
            '?' => 'A',
            '?' => 'a',
            '?' => 'E',
            '?' => 'e',
            '?' => 'E',
            '?' => 'e',
            '?' => 'O',
            '?' => 'o',
            '?' => 'O',
            '?' => 'o',
            '?' => 'U',
            '?' => 'u',
            '?' => 'Y',
            '?' => 'y',
            // acute accent
            '?' => 'A',
            '?' => 'a',
            '?' => 'A',
            '?' => 'a',
            '?' => 'E',
            '?' => 'e',
            '?' => 'O',
            '?' => 'o',
            '?' => 'O',
            '?' => 'o',
            '?' => 'U',
            '?' => 'u',
            // dot below
            '?' => 'A',
            '?' => 'a',
            '?' => 'A',
            '?' => 'a',
            '?' => 'A',
            '?' => 'a',
            '?' => 'E',
            '?' => 'e',
            '?' => 'E',
            '?' => 'e',
            '?' => 'I',
            '?' => 'i',
            '?' => 'O',
            '?' => 'o',
            '?' => 'O',
            '?' => 'o',
            '?' => 'O',
            '?' => 'o',
            '?' => 'U',
            '?' => 'u',
            '?' => 'U',
            '?' => 'u',
            '?' => 'Y',
            '?' => 'y',
            // Vowels with diacritic (Chinese, Hanyu Pinyin)
            '��' => 'a',
            // macron
            '��' => 'U',
            '��' => 'u',
            // acute accent
            '��' => 'U',
            '��' => 'u',
            // caron
            '��' => 'A',
            '��' => 'a',
            '��' => 'I',
            '��' => 'i',
            '��' => 'O',
            '��' => 'o',
            '��' => 'U',
            '��' => 'u',
            '��' => 'U',
            '��' => 'u',
            // grave accent
            '��' => 'U',
            '��' => 'u',
            );

        // Used for locale-specific rules
        $locale = get_locale();

        if ('de_DE' == $locale || 'de_DE_formal' == $locale || 'de_CH' == $locale ||
            'de_CH_informal' == $locale) {
            $chars['?'] = 'Ae';
            $chars['?'] = 'ae';
            $chars['?'] = 'Oe';
            $chars['?'] = 'oe';
            $chars['��'] = 'Ue';
            $chars['��'] = 'ue';
            $chars['?'] = 'ss';
        } elseif ('da_DK' === $locale) {
            $chars['?'] = 'Ae';
            $chars['?'] = 'ae';
            $chars['?'] = 'Oe';
            $chars['?'] = 'oe';
            $chars['?'] = 'Aa';
            $chars['?'] = 'aa';
        } elseif ('ca' === $locale) {
            $chars['l��l'] = 'll';
        } elseif ('sr_RS' === $locale) {
            $chars['?'] = 'DJ';
            $chars['?'] = 'dj';
        }

        $string = strtr($string, $chars);
    } else {
        $chars = array();
        // Assume ISO-8859-1 if not UTF-8
        $chars['in'] = "\x80\x83\x8a\x8e\x9a\x9e" . "\x9f\xa2\xa5\xb5\xc0\xc1\xc2" . "\xc3\xc4\xc5\xc7\xc8\xc9\xca" .
            "\xcb\xcc\xcd\xce\xcf\xd1\xd2" . "\xd3\xd4\xd5\xd6\xd8\xd9\xda" . "\xdb\xdc\xdd\xe0\xe1\xe2\xe3" .
            "\xe4\xe5\xe7\xe8\xe9\xea\xeb" . "\xec\xed\xee\xef\xf1\xf2\xf3" . "\xf4\xf5\xf6\xf8\xf9\xfa\xfb" .
            "\xfc\xfd\xff";

        $chars['out'] = "EfSZszYcYuAAAAAACEEEEIIIINOOOOOOUUUUYaaaaaaceeeeiiiinoooooouuuuyy";

        $string = strtr($string, $chars['in'], $chars['out']);
        $double_chars = array();
        $double_chars['in'] = array(
            "\x8c",
            "\x9c",
            "\xc6",
            "\xd0",
            "\xde",
            "\xdf",
            "\xe6",
            "\xf0",
            "\xfe");
        $double_chars['out'] = array(
            'OE',
            'oe',
            'AE',
            'DH',
            'TH',
            'ss',
            'ae',
            'dh',
            'th');
        $string = str_replace($double_chars['in'], $double_chars['out'], $string);
    }

    return $string;
}