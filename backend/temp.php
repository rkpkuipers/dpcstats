<?

$datum = date("U");

echo date("Y-m-d H:i", strtotime("+13 minutes", $datum));
?>
