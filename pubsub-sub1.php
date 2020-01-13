<?php
putenv('GOOGLE_APPLICATION_CREDENTIALS=C:\xampp\htdocs\indiatimes\key.json');
require __DIR__ . '/vendor/autoload.php';
use Google\Cloud\PubSub\PubSubClient;

$projectId = 'my-project1-1563953661044';
$pubsub = new PubSubClient([
    'projectId' => $projectId
]);


function pull_messages($pubsub, $projectId, $subscriptionName)
{
    $subscription = $pubsub->subscription($subscriptionName);
    $count=1;
    foreach ($subscription->pull() as $message) {
        print_r('Message: ' . PHP_EOL. $message->data());
        echo '<br>'; 
        //print_r('ackId: ' . PHP_EOL. $message->ackId());
        //echo '<br>';       
        print_r('Id: ' . PHP_EOL. $message->id());
        //echo '<br>';  
        //echo 'Info: '; 
        var_dump($message->info());

        echo '<br>';
        // Acknowledge the Pub/Sub message has been received, so it will not be pulled multiple times.
        $subscription->acknowledge($message);
        
    }

    echo $subscriptionName.' ends here'.'<br><br>';
}

$subscriptionName = 'sub1';
pull_messages($pubsub, $projectId, $subscriptionName);
