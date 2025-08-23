<?php
require __DIR__ . '/vendor/autoload.php';

use Aws\S3\S3Client;

$client = new S3Client([
    'version' => 'latest',
    'region'  => 'eu-north-1', // Mets la région exacte de ton bucket
    'credentials' => [
        'key'    => 'AKIATYP4CWYRV5RMNN37',
        'secret' => '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',
    ],
]);

$bucket = 'udsacadbucket';

try {
    $client->putObject([
        'Bucket' => $bucket,
        'Key'    => 'test.txt',
        'Body'   => 'Hello AWS S3!',
        'ACL'    => 'public-read'
    ]);
    echo "Upload réussi !";
} catch (\Aws\S3\Exception\S3Exception $e) {
    echo "Erreur S3 : " . $e->getMessage();
}
