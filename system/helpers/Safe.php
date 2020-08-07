<?php
namespace system\helpers;

/**
 * Safe
 */
class Safe
{

    /**
     * Converts a number of special characters into their HTML entities.
     * @return string The encoded text with HTML entities.
     */
    public function specialchars($string, $quote_style = ENT_NOQUOTES)
    {
        $string = (string) $string;

        if (0 === strlen($string)) {
            return '';
        }

        // Don't bother if there are no specialchars - saves some processing
        if (! preg_match('/[&<>"\']/', $string)) {
            return $string;
        }

        // Account for the previous behaviour of the function when the $quote_style is not an accepted value
        if (empty($quote_style)) {
            $quote_style = ENT_NOQUOTES;
        } elseif (! in_array($quote_style, array( 0, 2, 3, 'single', 'double' ), true)) {
            $quote_style = ENT_QUOTES;
        }

        $_quote_style = $quote_style;

        if ($quote_style === 'double') {
            $quote_style  = ENT_COMPAT;
            $_quote_style = ENT_COMPAT;
        } elseif ($quote_style === 'single') {
            $quote_style = ENT_NOQUOTES;
        }

        $string = htmlspecialchars($string, $quote_style, 'UTF-8', false);

        // Back-compat.
        if ('single' === $_quote_style) {
            $string = str_replace("'", '&#039;', $string);
        }

        return $string;
    }

    /**
     * Escaping for HTML attributes.
     */
    public function esc_attr($text="")
    {
        $safe_text = $this->specialchars($text, ENT_QUOTES);
        return $safe_text;
    }
}
