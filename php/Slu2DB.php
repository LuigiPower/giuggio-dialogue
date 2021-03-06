<?php
/**
 * Class for Attribute-Value & Utterance label to SQL Query Conversion
 *
 * @author estepanov
 * @modified federico giuggioloni
 */
class Slu2DB {

    private static $title = "title";
    private static $actors = "actors";
    private static $director = "director";
    private static $genres = "genres";
    private static $country = "country";
    private static $year = "year";
    private static $language = "language";
    private static $duration = "duration";
    private static $color = "color";
    private static $budget = "budget";
    private static $plot_keywords = "plot_keywords";
    private static $gross = "gross";
    private static $imdb_score = "imdb_score";
    private static $movie_facebook_likes = "movie_facebook_likes";
    private static $movie_imdb_link = "movie_imdb_link";

    private static $always = "DISTINCT movie_imdb_link, movie_facebook_likes";

    /**
     * Map SLU concepts & utterance classes to DB columns
     */
    private $class_map = array(
        "award" => "imdb_score",
        "award_category" => "imdb_score", //TODO from IMDB
        "award_category_count" => "imdb_score", //TODO from IMDB
        "award_ceremony" => "imdb_score", //TODO from IMDB
        "award_count" => "imdb_score", //TODO from IMDB
        "actor" => "actors",
        "actor_name" => "actors",
        "birth_date" => "actors", //TODO from IMDB
        "budget" => "budget",
        "rating" => "imdb_score",
        "star_rating" => "imdb_score",
        "review" => "imdb_score", //TODO special case, get reviews from IMDB?
        "person" => "actors", //TODO this is wrong?
        "person_name" => "actors", //todo this is wrong?
        "movie" => "title, actors, director, genres, country, year, language, duration, color, budget, plot_keywords, gross, imdb_score",
        "movie_name" => "title",
        "movie_count" => "COUNT(title) AS movieCount", //TODO group by...
        "movie_other" => "title, actors, director, genres, country, year, language, duration, color, budget, plot_keywords, gross, imdb_score",
        "other" => "title, actors, director, genres, country, year, language, duration, color, budget, plot_keywords, gross, imdb_score",
        "picture" => "title", //TODO from IMDB
        "release_date" => "year",
        "language" => "language",
        "genre" => "genres",
        "subjects" => "subject",
        "revenue" => "gross",
        "country" => "country",
        "date" => "year",
        "director" => "director",
        "director_name" => "director",
        "character" => "actors", //todo get from IMDB
        "producer" => "director", //todo get from IMDB
        "producer_count" => "director", //TODO get from IMDB
        "runtime" => "duration",
        "synopsis" => "title", //todo get from IMDB
        "media" => "title", //todo get from IMDB
        "trailer" => "title", //todo get from IMDB
        "writer" => "title", //todo get from IMDB
        "theater" => "title", //todo get from IMDB
        "organization" => "title", //todo get from IMDB
        "composer" => "title" //todo get from IMDB
    );

    public static $answer_map = array(
        "award" => "imdb_score",
        "award_category" => "imdb_score", //TODO from IMDB
        "award_category_count" => "imdb_score", //TODO from IMDB
        "award_ceremony" => "imdb_score", //TODO from IMDB
        "award_count" => "imdb_score", //TODO from IMDB
        "actor" => "actors",
        "actor_name" => "actors",
        "birth_date" => "actors", //TODO from IMDB
        "budget" => "budget",
        "rating" => "imdb_score",
        "star_rating" => "imdb_score",
        "review" => "imdb_score", //TODO special case, get reviews from IMDB?
        "person" => "actors", //TODO this is wrong?
        "person_name" => "actors", //todo this is wrong?
        "movie" => "title",
        "movie_name" => "title",
        "movie_count" => "movieCount", //TODO group by...
        "movie_other" => "title",
        "other" => "titlee",
        "picture" => "title", //TODO from IMDB
        "release_date" => "year",
        "language" => "language",
        "genre" => "genres",
        "subjects" => "subject",
        "revenue" => "gross",
        "country" => "country",
        "date" => "year",
        "director" => "director",
        "director_name" => "director",
        "character" => "actors", //todo get from IMDB
        "producer" => "director", //todo get from IMDB
        "producer_count" => "director", //TODO get from IMDB
        "runtime" => "duration",
        "synopsis" => "title", //todo get from IMDB
        "media" => "title", //todo get from IMDB
        "trailer" => "title", //todo get from IMDB
        "writer" => "title", //todo get from IMDB
        "theater" => "title", //todo get from IMDB
        "organization" => "title", //todo get from IMDB
        "composer" => "title" //todo get from IMDB
    );

    public static $numeric_map = array(
        "title" => false,
        "actors" => false,
        "director" => false,
        "genres" => false,
        "country" => false,
        "year" => true,
        "language" => false,
        "duration" => true,
        "color" => false,
        "budget" => true,
        "plot_keywords" => false,
        "gross" => true,
        "movie_facebook_likes" => true,
        "movie_imdb_link" => false
    );

    public static $concept_map = array(
        "award" => "imdb_score",
        "actor.name" => "actors",
        "actor.nationality" => "country",
        "actor.type" => "actors",
        "actor" => "actors",
        "rating.name" => "imdb_score",
        "person.name" => "actors", //todo this is kinda wrong, but the info is not in the db
        "person.nationality" => "country", //todo this is kinda wrong, but the info is not in the db
        "person" => "actors",
        "country.name" => "country", //todo wrong
        "movie.name" => "title",
        "movie.subject" => "plot_keywords",
        "movie.genre" => "genres",
        "movie.release_date" => "year",
        "movie.language" => "language",
        "movie.gross_revenue" => "gross",
        "movie.location" => "country", //todo wrong
        "movie.release_region" => "country",
        "movie.star_rating" => "imdb_score",
        "movie.duration" => "duration",
        "movie.budget" => "budget",
        "movie.keywords" => "plot_keywords",
        "movie.likes" => "movie_facebook_likes",
        "movie" => "title",
        "director.name" => "director",
        "director.nationality" => "country", //todo wrong
        "director" => "director",
        "character.name" => "actors", // todo wrong
        "character" => "actors", //todo wrong
        "producer.name" => "director", //todo maybe wrong
        "producer" => "director" //todo maybe wrong
    );

    /**
     * Returns db column w.r.t. $str
     */
    public function class_mapping($str) {
        return $this->class_map[$str];
    }

    /**
     * Returns db column w.r.t. $str
     */
    public function concept_mapping($str) {
        return Slu2DB::$concept_map[$str];
    }

    /**
     * Returns db column w.r.t. $str
     */
    public function answer_mapping($str) {
        return Slu2DB::$answer_map[$str];
    }

    public function is_num_field($str) {
        debugEcho("is_num_field $str");
        if(array_key_exists($str, Slu2DB::$numeric_map))
        {
            debugEcho("found it, ".Slu2DB::$numeric_map[$str]);
            return Slu2DB::$numeric_map[$str];
        }
        debugEcho("nope");
        return false;
    }

    /**
     * Meta function to
     * - map slu concepts to DB
     * - map utterance classifier class to db
     * - construct sql query
     */
    public function slu2sql($concepts, $class, $operand = null, $count = false)
    {
        $db_class = Slu2DB::$always.", ".$this->class_mapping($class);
        debugEcho("Db class is $class, [$db_class]");

        if(!strpos($db_class, "title"))
        {
            $db_class .= ", title";
        }

        $db_concepts = array();
        foreach ($concepts as $attr => $val)
        {
            $db_concepts[$this->concept_mapping($attr)] = $val;
        }

        $start = "SELECT DISTINCT * FROM movie WHERE ";
        //if($count)
        //{
            #$start = "SELECT COUNT(*) as resultCount FROM movie WHERE ";
        //    $start = "SELECT * FROM movie WHERE ";
        //}

        // construct SQL query


        $possible_queries = array();

        if(!$count) // If looking for a count, skip directly to the wildcards
        {
            $query = $start;
            $tmp = array();
            foreach ($db_concepts as $attr => $val) {
                //$tmp[] = $attr . " LIKE "%" . $val . "%"";
                // replace(replace(phone, '+', ''), '-', '')
                debugEcho("Operand is $operand");
                if($this->is_num_field($attr) && $operand)
                {
                    $tmp[] = "$attr $operand $val";
                }
                else
                {
                    $tmp[] = "REPLACE(TRIM(BOTH ' ' FROM ". $attr . "), ':', '') LIKE '" . $val . "'";
                }
            }
            $query .= implode(" AND ", $tmp);
            $query .= ";";
            $possible_queries[] = $query;

            $query = $start;
            $tmp = array();
            foreach ($db_concepts as $attr => $val) {
                //$tmp[] = $attr . " LIKE "%" . $val . "%"";
                if($this->is_num_field($attr) && $operand)
                {
                    $tmp[] = "$attr $operand $val";
                }
                else
                {
                    $tmp[] = "REPLACE(". $attr . ", ':', '') LIKE '" . $val . "'";
                }
            }
            $query .= implode(" AND ", $tmp);
            $query .= ";";
            $possible_queries[] = $query;
        }


        $query = $start;
        $tmp = array();
        foreach ($db_concepts as $attr => $val) {
            //$tmp[] = $attr . " LIKE "%" . $val . "%"";
            if($this->is_num_field($attr) && $operand)
            {
                $tmp[] = "$attr $operand $val";
            }
            else
            {
                $tmp[] = "REPLACE(". $attr . ", ':', '') LIKE '" . $val . "%'";
            }
        }
        $query .= implode(" AND ", $tmp);
        $query .= ";";
        $possible_queries[] = $query;


        $query = $start;
        $tmp = array();
        foreach ($db_concepts as $attr => $val) {
            //$tmp[] = $attr . " LIKE "%" . $val . "%"";
            if($this->is_num_field($attr) && $operand)
            {
                $tmp[] = "$attr $operand= $val";
            }
            else
            {
                $tmp[] = "REPLACE(". $attr . ", ':', '') LIKE '%" . $val . "%'";
            }
        }
        $query .= implode(" AND ", $tmp);
        $query .= ";";
        $possible_queries[] = $query;

        debugPrint($concepts);
        debugPrint($db_concepts);
        debugPrint($possible_queries);
        return $possible_queries;
    }
}
