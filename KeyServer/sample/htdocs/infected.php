<?php
require_once('../vendor/autoload.php');
require_once('shared.php');

if (!isValidBearer($db)) {
    sendJsonErrorResponse(401, 'Invalid bearer token');
    exit;
}

$useBinary = false;

if (array_key_exists('HTTP_ACCEPT', $_SERVER)) {
    $accept = $_SERVER['HTTP_ACCEPT'];
    $useBinary = strpos($accept, 'msgpack') !== false;
}

// https://github.com/rybakit/msgpack.php
use MessagePack\Packer;


// TODO: Validate bearer token and throw 401 if not valid

$minTime = time() - (86400 * 14);
$time = $minTime;

if (array_key_exists('since', $_GET)) {
    try {
        $dateTime = new DateTime($_GET['since']);
        $time = max($minTime, $dateTime->getTimestamp());
    }
    catch (Exception $e) {
	
    }
}

$stmt = $db->prepare('SELECT infected_key, rolling_start_number FROM infected_keys WHERE status = :s AND status_updated >= :t');
$stmt->bindValue(':t', $time, SQLITE3_INTEGER);
$stmt->bindValue(':s', 'A', SQLITE3_TEXT);

$result = $stmt->execute();

$keys = array();

$packer = new Packer();


if ($useBinary) {
    while (($row = $result->fetchArray(SQLITE3_NUM))) {
        $keys[] = array(
            'd' => base64_decode($row[0]),
            'r' => (int) $row[1]
        );
    }
}
else {
    while (($row = $result->fetchArray(SQLITE3_NUM))) {
        $keys[] = array(
	    'd' => $row[0],
	    'r' => (int) $row[1]
        );
    }
}

$date = new DateTime();

$json = array(
    'status' => 'OK',
    'date' => $date->format(DateTimeInterface::ISO8601),
    'keys' => $keys,
    'deleted_keys' => array()
);

if ($useBinary) {
    $packed = $packer->pack($json);

    header('Content-type: application/x-msgpack');
    header(sprintf('Content-length: %d', strlen($packed)));

    echo $packed;
}
else {
    sendJsonResponse(200, $json);
}

