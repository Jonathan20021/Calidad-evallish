<?php

namespace App\Config;

class Config
{
    const APP_NAME = 'Evallish BPO';
    const BASE_URL = '/';
    const TIMEZONE = 'America/Bogota';
    const GEMINI_API_KEY = 'AIzaSyBRZlOrFW0yambbpsjszvVkNcC7s9kLjg8';
    const GEMINI_MODEL = 'gemini-3-flash-preview';
    const GEMINI_CONNECT_TIMEOUT = 10;
    const GEMINI_TIMEOUT = 60;

    public static function init()
    {
        date_default_timezone_set(self::TIMEZONE);
        session_start();
    }
}
