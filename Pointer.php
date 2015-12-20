<?php

/**
 *
 * Github: https://github.com/dulabs/pointer-co-id
 *
 */

class Pointer
{

    // Requirements
    public static $API_KEY;
    public static $USERNAME;
    public static $PASSWORD;
    public static $FORMAT;

    // Airlines Constant
    const SRIWIJAYA = "sriwijaya";
    const GARUDA    = "garuda";
    const AIRASIA   = "airasia";
    const TRIGANA   = "trigana";
    const KALSTAR   = "kalstar";
    const LIONAIR   = "lionair";
    const CITILINK  = "citilink";
    const TRANSNUSA = "transnusa";
    const EXPRESS   = "express";
    const TIGER     = "tiger";

    // Format
    const FORMAT_XML = "xml";
    const FORMAT_JSON = "json";

    public static function serverUrl($relpath)
    {
        $url = "https://login.pointer.co.id/api/".$relpath;
        $url = static::appendFormat($url);
        return $url;
    }

    public static function appendFormat($url)
    {
        $format = static::$FORMAT;

        switch(strtoupper($format)){
            case "JSON":
                $url .= "/format/json";
            break;
            case "XML":
            default:
                $url .= "/format/xml";
            break;
        }

        return $url;
    }

    public static function listCountry()
    {
        $url = static::serverUrl("airport/country");

        return static::send($url,null);
    }

    public static function listAirport($country,$iata=null)
    {
        $url = static::serverUrl("airport/get/idCountry/$country/");

        if(!empty($iata))
        {
            $url .= "iata/".$iata;
        }

        return static::send($url);
    }


    public static function AvailableAirlines($origin,$destination)
    {
        $url = static::serverUrl("airlines/check");

        $post = ["from_city" => $origin,
                 "to_city" => $destination];

        return static::send($url,$post);
    }

    public static function FlightCheck($airline,$origin,$destination,$departure,$adult=1,$children=0,$infant=0,$pagination=null,$page=null)
    {
        return static::Flight($airline,$origin,$destination,$departure,$adult=1,$children=0,$infant=0,$pagination=null,$page=null,true);
    }    

    public static function FlightAvailable($airline,$origin,$destination,$departure,$adult=1,$children=0,$infant=0,$pagination=null,$page=null)
    {
        return static::Flight($airline,$origin,$destination,$departure,$adult=1,$children=0,$infant=0,$pagination=null,$page=null);
    }    

    public static function Flight($airline,$origin,$destination,$departure,$adult=1,$children=0,$infant=0,$pagination=null,$page=null,$price=false)
    {
        $url = $price ? static::serverUrl("flight/check") : static::serverUrl("flight/available");

        $post = ["airline" => $airline,
                 "from_city" => $origin,
                 "to_city" => $destination,
                 "tgl_flight" => $departure,
                 "jml_penumpang" => $adult,
                 "jml_chidren" => $children,
                 "jml_infant" => $infant];

        if(!empty($pagination))
        {
            $post['pagination'] = $pagination;
        }

        if(!empty($page))
        {
            $post['page'] = $page;
        }

        return static::send($url,$post);
    }

    public static function FareUpdate($fid)
    {
        $url = static::serverUrl("flight/fare_update");
        $post = ['ftid' => $fid];

        return static::send($url,$post);
    }

    public static function BuildPNR($fid)
    {
        $url = static::serverUrl("flight/buildpnr");
        $post = ['ftid' => $fid];

        return static::send($url,$post);
    }

    public static function Booking($fid,$contact,$airline,$adult,$children,$infant)
    {
        $url = static::serverUrl("flight/book");
        
        $post['ftid'] = $fid;
        $post = array_merge($post,$contact);

        return static::send($url,$post);
    }

    public static function ViewBooking($airline=null,$status=null,$PNR=null,$flightdate=null,$passname=null,$limit=null,$offset=null)
    {
        $url = static::serverUrl("airlines/viewbooks");

        if(!empty($airline)) $post['airline'] = $airline;
        if(!empty($status)) $post['status'] = $status;
        if(!empty($PNR)) $post['booking_code'] = $PNR;
        if(!empty($flightdate)) $post['flightdate'] = $flightdate;
        if(!empty($passname)) $post['passname'] = $passname;
        if(!empty($limit)) $post['limit'] = $limit;
        if(!empty($offset)) $post['offset'] = $offset;

        return static::send($url,$post);
    }

    public function CancelBooking($PNR)
    {
        $url = static::serverUrl("flight/cancel/format/json");
        $post['booking_code'] = $PNR;
        return static::send($url,$post);
    }

    public function IssuedBooking($PNR)
    {
        $url = static::serverUrl("flight/issued/format/json");
        $post['booking_code'] = $PNR;
        return static::send($url,$post);
    }

    public static function send($url,$post=null)
    {
        if(is_array($post))
        {
           $post = http_build_query($post);
        }

        $api_key = static::$API_KEY;
        $username = static::$USERNAME;
        $password = static::$PASSWORD;

        if(empty($api_key)) throw Exception("No API_KEY");
        if(empty($username)) throw Exception("No USERNAME");
        if(empty($password)) throw Exception("No PASSWORD");
        
        $header[] = "MARS-API-KEY: ".$api_key;
        $auth = [$username,$password];

        $options = array(
            CURLOPT_URL             => $url,
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_HTTPAUTH        => CURLAUTH_DIGEST,
            CURLOPT_USERPWD         => implode(":",$auth),
            CURLOPT_HTTPHEADER      => $header,
            CURLOPT_SSL_VERIFYPEER  => false,
        );

        if(!empty($post))
        {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = $post;
        }

        $ch = curl_init();
        curl_setopt_array($ch, $options);
        $content = curl_exec($ch);
        curl_close($ch);

        return $content;
    }

}

Pointer::$API_KEY  = ""; // API_KEY
Pointer::$USERNAME = ""; // EMAIL
Pointer::$PASSWORD = ""; // PASSWORD
Pointer::$FORMAT   = Pointer::FORMAT_JSON; // FORMAT_JSON or FORMAT_XML

$output = Pointer::FlightCheck(Pointer::SRIWIJAYA,"CGK","DPS","21/01/2016",1);

echo $output;
