<?php
$iniFile = "stistab.ini";   
$iniData = parse_ini_file($iniFile);    // read all options from ini file

if (isset($_GET['version']))    // show version when come request in GET parameter
{
    echo("script version: 1.0");
    exit();
}
if ($iniData === FALSE) // can not read options from ini file
{
    echo("Err01: Can not open ini file $iniFile or its missing.");
    exit();
}
if (!isset($iniData['getUrl']) || !isset($iniData['urlStart']) || !isset($iniData['urlEnd']) || !isset($iniData['teamStartUrl']))   // required options from ini file
{
    echo("Err02: One or more required initialization settings are missing in ini file $iniFile.");
    exit();
}
if ($_GET && $iniData['getUrl'] == 1)   // if there are GET parameters and option for applie them then overide them to actual readed options
{
    $iniData = array_replace($iniData, $_GET);
}
if (!isset($iniData['url']))    // url option must be set
{
    echo("Err03: Missing option 'url'.");
    exit();
}

$pathNodes = getDataPathNodes($iniData);

$iniData = slahTrim($iniData);

$soutez = getSoutez($iniData);

$url = getUrl($iniData);

$jsonData = getJsonData($url, $soutez);

printTable($iniData, $jsonData, $pathNodes);

// *** functions ***

/**
 * set nodes for JSON data path
 *
 * @param array $iniData
 * @return array
 */
function getDataPathNodes(array $iniData):array
{
    if (!isset($iniData['table']))  // total match table
    {
        $table = "tables";
        $num = 0;
    }
    else 
    {   
        switch($iniData['table'])
        {
            case 0: // total match table
                $table = "tables";
                $num = 0;
                break;
            case 1: // home match table
                $table = "tables_doho";
                $num = 0;
                break;
            case 2: // home match table
                $table = "tables_doho";
                $num = 1;
                break;
            default:    // total match table
                $table = "tables";
                $num = 0;
                break;
        }
    }
    return array('table' => $table, 'num' => $num);
}

/**
 * remove slash from the end of url string
 *
 * @param array $iniData
 * @return array
 */
function slahTrim(array $iniData):array
{
    if (substr($iniData['url'], -1) === '/')    // remove if last char of url string is '/'
    {
        $iniData['url'] = rtrim($iniData['url'], "/");
    }
    return $iniData;
}

/**
 * get soutez number for CURL POST request
 *
 * @param array $iniData
 * @return string
 */
function getSoutez(array $iniData):string
{
    // get number from URL for CURL setting CURLOPT_POSTFIELDS
    $soutez = "";
    $urlParse = explode("/", $iniData['url']);
    foreach($urlParse as $piece)
    {
        $onePiece = explode("-", $piece);
        if ($onePiece[0] == "soutez")
        {
            $soutez = $onePiece[1];
            break;
        }
    }
    return $soutez;
}

/**
 * prepare url for CURL POST request
 *
 * @param [type] $iniData
 * @return string
 */
function getUrl(array $iniData):string
{
    // get URL for CURL setting CURLOPT_URL
    $url = $iniData['url'] . $iniData['urlEnd'];
    $url = str_replace("https://stis.ping-pong.cz/tabulka/", $iniData['urlStart'], $url);
    return $url;
}

/**
 * CURL POST request for JSON data
 *
 * @param string $url request URL
 * @param string $soutez value for request CURLOPT_POSTFIELDS
 * @return array decoded JSON data in array
 */
function getJsonData(string $url, string $soutez):array
{
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true,  
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS => array('action' => 'tabulka','soutez' => $soutez),  
    ));
    
    $response = curl_exec($curl);    
    curl_close($curl);    
    $jsonData = json_decode($response, TRUE);

    return $jsonData;
}

/**
 * table processing and direct print to site
 *
 * @param array $iniData options from ini file or GET method
 * @param array $jsonData data from CURL request
 * @param array $pathNodes nodes needed for table data
 * @return void
 */
function printTable(array $iniData, array $jsonData, array $pathNodes)
{
    echo("<table " . $iniData['tableAttributes'] . ">");    // start with <table> element and its attributes
    echo("<tr><th></th><th></th><th>PU</th><th>V</th><th>R</th><th>P</th><th>K</th><th>Skóre</th><th>Body</th></tr>");  // header of table
    
    foreach($jsonData[$pathNodes['table']][$pathNodes['num']]['data'] as $i => $data)   // over all teams in competition
    {   
        if ($data['nazev'] == $iniData['teamName'] && isset($iniData['teamRowAttributes']))
        {
            echo("<tr " . $iniData['teamRowAttributes'] . ">");
        }
        else    
            echo("<tr>");
        
        echo("<td>" . ($i + 1) . "</td>");  // col 1 - number of order    
        
        $nazev = $data['nazev'];
        if (($data['nazev'] == $iniData['teamName']) && isset($iniData['teamNameElemStart']) && isset($iniData['teamNameElemEnd']))
        {
            $nazev = $iniData['teamNameElemStart'] . $nazev . $iniData['teamNameElemEnd'];
        }          
        if ($iniData['addLinks'] == 1 && $iniData['blankLinks'] != 1)
        {
            $nazev = '<a href="' . $iniData['teamStartUrl'] . $data['id_druzstvo'] . '">' . $nazev . '</a>';
        }
        if ($iniData['addLinks'] == 1 && $iniData['blankLinks'] == 1)
        {
            $nazev = '<a target="_blank" href="' . $iniData['teamStartUrl'] . $data['id_druzstvo'] . '">' . $nazev . '</a>';
        }
    
        echo("<td>" . $nazev . "</td>");    // col 2 - name of team
        echo("<td>" . $data['pocet'] . "</td>");    // col 3 - number of matches
        echo("<td>" . $data['vyhry'] . "</td>");    // col 4 - number of wins
        echo("<td>" . $data['remizy'] . "</td>");   // col 5 - number of draws
        echo("<td>" . $data['prohry'] . "</td>");   // col 6 - number of losses
        echo("<td>" . $data['kontprohry'] . "</td>");   // col 7 - number of default losses 
        echo("<td>" . $data['vyhrbody'] .":" . $data['prohrbody'] . "</td>");   // col 8 - score won:lost singles
        echo("<td>" . $data['body'] . "</td>"); // col 9 - number of points
        echo("</tr>");
    }
    
    echo("</table>");   // end with closing </table> element :-)
}