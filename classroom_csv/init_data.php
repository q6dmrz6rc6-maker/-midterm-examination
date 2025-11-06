<?php

$dataFile = __DIR__ . '/data/data.csv';

$rows = [
    ['studentID','name','sex','birth'],
    ['22110136','Nguyen Van A','Male','2004-01-01'],
    ['22110155','Nguyen Quang Minh','Male','2004-11-28'],
];

$f = fopen($dataFile, 'w');
if ($f === false) {
    fwrite(STDERR, "Cannot open {$dataFile} for writing\n");
    exit(1);
}
foreach ($rows as $r) fputcsv($f, $r);
fclose($f);
echo "Created {$dataFile}\n";