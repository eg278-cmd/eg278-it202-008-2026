<?php

/**
 * This file is a wrapper for our API calls.
 * Here, each endpoint needed will be exposes as a function.
 * The function will take the parameters needed for the API call and return the result.
 * The function will also handle the API key and endpoint.
 * Requires the api_helpers.php file and load_api_keys.php file.
 */

/**
 * Fetches the golf tournament schedule for a given org and year.
 */
function fetch_golf_schedule($orgId = 1, $year = 2024)
{
    $data = [
        "orgId" => $orgId, 
        "year"  => $year
    ];
    $endpoint = "https://live-golf-data.p.rapidapi.com/schedule";
    $isRapidAPI = true;
    $rapidAPIHost = "live-golf-data.p.rapidapi.com";

    $result = get($endpoint, "GOLF_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    error_log("GOLF API RESPONSE: " . var_export($result, true));
    //example of cached data to save the quotas, don't forget to comment out the get() if using the cached data for testing
    
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        $result = [];
    }
    $transformedResult = [];
    // transform data to match our DB structure
    if (isset($result["tournaments"])) {
       foreach ($result["tournaments"] as $t) {
       $transformedResult[] = [
       "tourn_id" =>  $t["tournament_id"],
       "name"     =>  $t["tournament_name"],
       "start_date" =>  substr($t["startDate"], 0, 10),
       "end_date"   =>  substr($t["endDate"], 0, 10,),
       "is_api"     => 1
    ];
}
    }
    return $transformedResult;
}
function search_companies($search)
{
    $data = ["function" => "SYMBOL_SEARCH", "keywords" => $search, "datatype" => "json"];
    $endpoint = "https://alpha-vantage.p.rapidapi.com/query";
    $isRapidAPI = true;
    $rapidAPIHost = "alpha-vantage.p.rapidapi.com";
    $result = get($endpoint, "GOLF_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    //example of cached data to save the quotas, don't forget to comment out the get() if using the cached data for testing
    /* $result = ["status" => 200, "response" => {
        "bestMatches": [
            {
                "1. symbol": "TESTF", 
                "2. name": "Test Foreign Security",
                "3. type": "Equity",  
                "4. region": "United States",
                "5. marketOpen": "09:30",
                "6. marketClose": "16:00",
                "7. timezone": "UTC-04",
                "8. currency": "USD", 
                "9. matchScore": "0.8889"
            },
            {
                "1. symbol": "TESTJ", 
                "2. name": "Test Security J1",
                "3. type": "Equity",  
                "4. region": "United States",
                "5. marketOpen": "09:30",
                "6. marketClose": "16:00",
                "7. timezone": "UTC-04",
                "8. currency": "USD", 
                "9. matchScore": "0.8889"
            },
            {
                "1. symbol": "TESTM", 
                "2. name": "Demo Test Security",
                "3. type": "Equity",  
                "4. region": "United States",
                "5. marketOpen": "09:30",
                "6. marketClose": "16:00",
                "7. timezone": "UTC-04",
                "8. currency": "USD", 
                "9. matchScore": "0.8889"
            },
            {
                "1. symbol": "TESTT", 
                "2. name": "Test Security T",
                "3. type": "Equity",  
                "4. region": "United States",
                "5. marketOpen": "09:30",
                "6. marketClose": "16:00",
                "7. timezone": "UTC-04",
                "8. currency": "USD", 
                "9. matchScore": "0.8889"
            }
        ]
    }'];*/
    error_log("GOLF API Response: " . var_export($result, true));
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        $result = [];
    }
    // transform data
    if(isset($result["bestMatches"])){
        $result = $result["bestMatches"];
        $transformedResult = [];
        foreach ($result as $r) {

            // fixed keys
            foreach ($r as $k => $v) {
                // "1. symbol"
                // ["1.", "symbol"]
                // "symbol"
                $nk = str_replace(" ", "_", explode(" ", $k, 2)[1]);
                $r[$nk] = $v;
                unset($r[$k]);
            }
            if (strlen($r["symbol"]) > 6) {
                continue;
            }
            // map/extract desired information
            $data = [
                "symbol" => $r["symbol"],
                "name" => $r["name"],
                "type" => $r["type"],
                "region" => $r["region"],
                "currency" => $r["currency"],
                "is_api" => 1
            ];
            array_push($transformedResult, $data);
        }
    }
    return $transformedResult;
}

function uppercaseSymbolCurrency($data)
{
    if (!is_array($data)) {
        throw new InvalidArgumentException('$data must be an array');
    }
    foreach ($data as $i => $obj) {
        foreach ($obj as $k => $v) {
            if (in_array($k, ["symbol", "currency"])) {
                $data[$i][$k] = strtoupper($v);
            }
        }
    }
    return $data;
}