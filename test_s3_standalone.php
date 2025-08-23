<?php
require 'vendor/autoload.php';

use Aws\S3\S3Client;
use Aws\Exception\AwsException;

$bucket = getenv('AWS_BUCKET') ?: 'udsacadbucket';
$key = 'test-php-aws-sdk.txt';
$content = 'Hello from standalone PHP AWS SDK!';

$s3 = new S3Client([
    'version' => 'latest',
    'region'  => getenv('AWS_DEFAULT_REGION') ?: 'eu-north-1',
    'credentials' => [
        'key'    => getenv('AWS_ACCESS_KEY_ID') ?: 'AKIATYP4CWYRV5RMNN37',
        'secret' => getenv('AWS_SECRET_ACCESS_KEY') ?: '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',
    ],
]);

try {
    $result = $s3->putObject([
        'Bucket' => $bucket,
        'Key'    => $key,
        'Body'   => $content,
        'ACL'    => 'public-read',
    ]);
    echo "Success: " . $result['ObjectURL'] . "\n";
} catch (AwsException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
