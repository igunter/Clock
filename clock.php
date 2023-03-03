<?php

$Clock = new Clock();

class Clock {
	var $apiURI	= 'http://worldtimeapi.org/api/';

    function formatRow($src) {
        // Initialise new object
        $row = new stdClass();

        // Set variables in the object
        $row->abbreviation = $src->abbreviation;
        $row->timezone = $src->timezone;
        $row->datetime = $src->datetime;
        $row->utc_offset = $src->utc_offset;

        // Return the object
        return $row;
    }

    function getZone($zone) {
        // Set the api call for this specific timezone
        $call = 'timezone/'.$zone;

        // Get and return the api result
        return $this->apiRequest($call);
    }

    function getZones() {
        // Set the api call to get ALL available timezones
        $call = 'timezone';

        // Get and return the api result
        return $this->apiRequest($call);
    }

    function getTimeZones($numZones=2,$myZone=false) {
        // Initialise the rows array
        $rows = array();

        // Is a home zone set?
        if (isset($myZone)) {
            // Get the time for the home zone and add to the start of the results
            $rows[] = $this->formatRow($this->getZone($myZone));
        }

        // Get all the available timezones
        $allZones = $this->getZones();

        // Shuffle the timezones as we will be picking from the beginning of the array
        shuffle($allZones);

        // Get the required number of timezones from the array
        for ($z=0; $z<$numZones; $z++) {
            // Does this timezone match our home zone?
            if ($allZones[$z] == $myZone) {
                // Increase the count by 1 and don't add this zone to the results (as we already have it at the beginning)
                $numZones++;
            } else {
                // Add this timezone to the results
                $rows[] = $this->formatRow($this->getZone($allZones[$z]));
            }
        }

        // Return the results
        return $rows;
    }

    function apiRequest($call) {
        // Build the API URI
        $uri = $this->apiURI.$call;

        // Build the default CURL options array
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT        => 120
        ); 

        // Initialise CURL
        $ch = curl_init($uri);

        // Set the CURL options
        curl_setopt_array($ch, $options);

        // Get the results from CURL
        $result = curl_exec($ch);

        // Close the CURL
        curl_close($ch);

        // Return the results
        return json_decode($result);
    }
}

// Set my variables
$myZone = 'Europe/London';
$numZones = rand(5,10);

// Get the requested timezones
$rows = $Clock->getTimeZones($numZones,$myZone);

// Sort the results into the datetime descending order
array_multisort(array_column($rows,'datetime'), SORT_DESC, SORT_STRING|SORT_FLAG_CASE,
                array_column($rows,'timezone'), SORT_ASC, SORT_STRING|SORT_FLAG_CASE,
                $rows);

// Display the results in a table and hightlight my home zone in blue
echo "\n".'<table cellspacing="0" cellpadding="2" style="border: 1px #000 solid;">';
echo "\n\t".'<thead style="border: 1px #000 solid;">';
echo "\n\t\t".'<tr>';
echo "\n\t\t\t".'<th style="text-align:left;">Timezone Name</th>';
echo "\n\t\t\t".'<th>Abbr.</th>';
echo "\n\t\t\t".'<th>Current Date and Time</th>';
echo "\n\t\t\t".'<th>Offset</th>';
echo "\n\t\t".'</tr>';
echo "\n\t".'</thead>';
echo "\n\t".'<tbody>';
foreach ($rows as $row) {
    echo "\n\t\t".'<tr'.(strtolower($row->timezone)==strtolower($myZone)?' style="background-color:#AABBDD;"':'').'>';
    echo "\n\t\t\t".'<td>'.$row->timezone.'</td>';
    echo "\n\t\t\t".'<td>'.$row->abbreviation.'</td>';
    echo "\n\t\t\t".'<td>'.(new DateTime($row->datetime))->format('l jS F \a\t g:ia').'</td>';
    echo "\n\t\t\t".'<td style="text-align:right;">'.$row->utc_offset.'</td>';
    echo "\n\t\t".'</tr>';
}
echo "\n\t".'</tbody>';
echo "\n".'</table>';
?>