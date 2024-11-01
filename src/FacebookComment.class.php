<?php

class uni_plg_FacebookComment
{ 

    public $id;
    public $like_count;
    public $message;
    public $from;
    public $created_time;

    public function __construct() 
    { 

    }

    public function bind($data) 
    { 
        //do parsing and populate properties
        foreach ($data as $key => $value) 
        {
            if (property_exists($this,$key))
                $this->{$key} = $value;
        }
    } 

}