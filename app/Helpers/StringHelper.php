<?php

    namespace App\Helpers;

    class StringHelper
    {
        /**
         * Remplace les caractères accentués par leurs équivalents non accentués.
         *
         * @param string $string
         * @return string
         */
        public static function removeAccents($string)
        {
            $accents = [
                'á', 'à', 'â', 'ä', 'ã', 'å', 'ā', 'ă', 'ą',
                'Á', 'À', 'Â', 'Ä', 'Ã', 'Å', 'Ā', 'Ă', 'Ą',
                'é', 'è', 'ê', 'ë', 'ē', 'ė', 'ę',
                'É', 'È', 'Ê', 'Ë', 'Ē', 'Ė', 'Ę',
                'í', 'ì', 'î', 'ï', 'ī', 'į', 'ĩ',
                'Í', 'Ì', 'Î', 'Ï', 'Ī', 'Į', 'Ĩ',
                'ó', 'ò', 'ô', 'ö', 'õ', 'ō', 'ő', 'ø',
                'Ó', 'Ò', 'Ô', 'Ö', 'Õ', 'Ō', 'Ő', 'Ø',
                'ú', 'ù', 'û', 'ü', 'ū', 'ů', 'ű',
                'Ú', 'Ù', 'Û', 'Ü', 'Ū', 'Ů', 'Ű',
                'ç', 'Ç', 'ñ', 'Ñ', 'ý', 'Ý'
            ];

            $replacements = [
                'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
                'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A',
                'e', 'e', 'e', 'e', 'e', 'e', 'e',
                'E', 'E', 'E', 'E', 'E', 'E', 'E',
                'i', 'i', 'i', 'i', 'i', 'i', 'i',
                'I', 'I', 'I', 'I', 'I', 'I', 'I',
                'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o',
                'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O',
                'u', 'u', 'u', 'u', 'u', 'u', 'u',
                'U', 'U', 'U', 'U', 'U', 'U', 'U',
                'c', 'C', 'n', 'N', 'y', 'Y'
            ];

            return str_replace($accents, $replacements, $string);
        }
    }
