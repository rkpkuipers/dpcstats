#!/usr/bin/php
<?

include('/home/rkuipers/public_html/classes.php');

$prefix = $argv[1];

echo 'Creating tables for prefix ' . $prefix . "\n";

$query = 'CREATE TABLE ' . $prefix . '_memberoffset LIKE fah_memberoffset';
$db->selectQuery($query);

$query = 'CREATE TABLE ' . $prefix . '_teamoffset LIKE fah_teamoffset';
$db->selectQuery($query);

$query = 'CREATE TABLE ' . $prefix . '_subteamoffset LIKE fah_subteamoffset';
$db->selectQuery($query);

$query = 'CREATE TABLE ' . $prefix . '_individualoffset LIKE fah_individualoffset';
$db->selectQuery($query);

$db->disconnect();
?>
