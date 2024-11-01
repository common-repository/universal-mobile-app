<?php

class uni_plg_FacebookPost 
{ 
    public $id;
    public $created_time;
    public $type;
    public $link;
    public $permalink_url;
    public $properties;
    public $name;
    public $message;
    public $caption;
    public $full_picture;
    public $picture;
    public $comments;
    public $likes;
    public $shares;

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

    public function getPushID()
    {
        if (isset($this->id) || trim($this->id) !=='')
        {
            $push_id = explode("_", $this->id);
            return $push_id[1];
        }
        else return "";
    }

    public function getTitle()
    {
        switch ($this->type) {
            case 'video':
                return $this->name;
                break;
            case 'link':
                return $this->name;
                break;
            case 'photo':
                return $this->message;
                break;
            case 'offer':
                return $this->message;
                break;
            default:
                return "Un nuovo post memorabile è disponibile";
                break;
        }
    }

    public function getBody()
    {
        return $this->message;
    }

    public function isWordpressPost()
    {
        if ($this->type == "link")
        {
            $domain = str_ireplace('www.', '', parse_url($this->link, PHP_URL_HOST));
            if ($domain === "commentimemorabili.it") //replace here with host domain
                return true;
        }
        return false;
    }

}