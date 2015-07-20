# Oplan

Software zur Raumplanung


# Installation

Erstelle eine Mysql-Datenbank und importiere oplan.sql

Erstelle eine Datei .htconfig.php mit folgendem Inhalt:

```
<?php
$DB_HOST="localhost";
$DB_NAME="oplan";
$DB_USER="root";
$DB_PASS="changeme";

$tucan_root = 'https://www.tucan.tu-darmstadt.de';
$tucan_myid = '357524424469685';
```


