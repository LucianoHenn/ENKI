<?php

namespace App\Services;


class KeywordChecker
{
    /**
     * checks is the $keyword string stringcontains email
     *
     * @param  string $keyword
     *
     * @return boolean
     */
    public static function checkEmail($keyword)
    {
        preg_match('/([a-z0-9_]+|[a-z0-9_]+\.[a-z0-9_]+)@(([a-z0-9]|[a-z0-9]+\.[a-z0-9]+)+\.([a-z]{2,4}))/', $keyword, $match);
        return !empty($match[0]);
    }

    /**
     * checks is the $keyword contains url
     *
     * @param string $keyword
     *
     * @return boolean
     */
    public static function checkUrl($keyword)
    {
        //$pattern = "\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]";
        $pattern = "(http:\/\/www\.|https:\/\/www\.|http:\/\/|https:\/\/)?[a-z0-9]+([\-\.]{1}[a-z0-9]+)*\.[a-z]{2,5}(:[0-9]{1,5})?(\/.*)?";
        preg_match('/' . $pattern . '/i', $keyword, $match);
        return !empty($match);
    }


    public static function splitWords($text)
    {
        return preg_split('/[^\pL\pN\-]/u', $text, -1, PREG_SPLIT_NO_EMPTY);
    }

    /** 
     * checks is the $words_array contains number
     *
     * @param string $keyword
     *
     * @return boolean
     */
    public static function checkNumber($keyword) 
    {   
        $words_array = static::splitWords($keyword);

        if (!is_array($words_array)) return null;

        $num_array = array_filter($words_array, 'is_numeric');
        return (count($words_array) == count($num_array));   
       
    }
}
