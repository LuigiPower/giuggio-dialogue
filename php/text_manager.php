<?php

class TextManager
{
    public static $acks = array(
        "Yes",
        "Ok",
        "Got it",
        "Understood",
        "Alright",
        "Gotcha",
        "Ah"
    );

    public static $affirmative = array(
        "yes",
        "yea",
        "yeah",
        "sure",
        "yep",
        "correct",
        "that is correct",
        "right",
        "that one",
        "absolutely",
        "affirmative"
    );

    public static $negative = array(
        "no",
        "nope",
        "nay",
        "nada",
        "that is wrong",
        "wrong",
        "not at all",
        "not that",
        "negative"
    );

    public static $ignore = array(
        "ignore",
        "remove",
        "mistake",
        "no ignore",
        "no remove",
        "yes remove",
        "yes ignore",
        "yes it is a mistake",
        "no it is a mistake",
        "it is a mistake"
    );

    public static $greater = array(
        "greater than",
        "longer than",
        "bigger than",
        "more than",
        "after"
    );

    public static $lesser = array(
        "lesser than",
        "shorter than",
        "lower than",
        "smaller than",
        "less than",
        "before"
    );

    public static $count = array(
        "how many",
        "count",
        "tell me the number of"
    );

    public static $user_answer_to_field = array(
        "award" => "award",
        "actor name" => "actor.name",
        "actor nationality" => "actor.nationality",
        "actor type" => "actor.type",
        "actor" => "actor",
        "rating" => "rating.name",
        "person.nationality" => "person.nationality",
        "person" => "person",
        "country name" => "country.name",
        "country" => "country.name",
        "movie name" => "movie.name",
        "subject" => "movie.subject",
        "genre" => "movie.genre",
        "release date" => "movie.release_date",
        "language" => "movie.language",
        "gross revenue" => "movie.gross_revenue",
        "location" => "movie.location",
        "release region" => "movie.release_region",
        "star rating" => "movie.star_rating",
        "duration" => "movie.duration",
        "budget" => "movie.budget",
        "keywords" => "movie.plot_keywords",
        "likes" => "movie.likes",
        "movie" => "movie",
        "director name" => "director.name",
        "director nationality" => "director.nationality",
        "director" => "director",
        "character name" => "character.name",
        "character" => "character",
        "producer name" => "producer.name",
        "producer" => "producer"
    );
}

?>
