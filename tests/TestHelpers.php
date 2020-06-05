<?php

namespace StillAlive\RateMeanCalculator\Tests;

function get_fixture_content(string $fixture): string
{
    return file_get_contents(FIXTURES_PATH . $fixture);
}

function random_float(): float
{
    return round((float)sprintf("%s.%s", mt_rand(10,1000), mt_rand(1000, 9999)), 4);
}
