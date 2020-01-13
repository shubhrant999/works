<?php
putenv('GOOGLE_APPLICATION_CREDENTIALS=C:\xampp\htdocs\indiatimes\key.json');
require __DIR__ . '/vendor/autoload.php';
use Google\Cloud\PubSub\PubSubClient;

$projectId = 'my-project1-1563953661044';
$pubsub = new PubSubClient([
    'projectId' => $projectId
]);

// # The name for the new topic
// $topicName = 'topic2';
// # Creates the new topic
// $topic = $pubsub->createTopic($topicName);
// echo 'Topic ' . $topic->name() . ' created.';

function publish_message($pubsub, $projectId, $topicName, $message)
{
   
    $topic = $pubsub->topic($topicName);
    //$topic->publish(['data' => $message]);


    $topic->publish(['data' => $message,
        'attributes' => [
            'id' => '2019',
            'userName' => 'shubhrant',
            'location' => 'India'
        ]
    ]);

    print('Message published' . PHP_EOL);
    echo '<br>';
}
$currentTime = date('YmdHisU');

for($i=0;$i<1;$i++){
    $message = 'http://pb.xapads.com/postback.php?clickid='.$currentTime.'&advertiserId=2d231cea-8e6a-4146-a542-51e6755126c7&android-id=&app-id=shubhrantX.app&wifi=false&install-unix-ts=1563188296&click-ts=2019-07-15%2010%3A56%3A39.557&campaign=&publisher_id=&country-code=IN&city=Delhi&device-brand=samsung&carrier=Jio%204G&ip=47.30.253.151&device-model=SM-A505F&language=English&appsflyer-device-id=1563188290303-3574854968756249896&sdk-version=v4.10.0&app-version-name=3.16.0&user-agent=Dalvik%2F2.1.0%20%28Linux%3B%20U%3B%20Android%209%3B%20SM-A505F%20Build%2FPPR1.180610.011%29&vendorId=&os-version=9&app-name=&match-type=fp&gp_referrer_click_ts=&gp_referrer_install_ts=&event-name=install&monetary=&orig-monetary=&currency=INR&timestamp=2019-07-15%2010%3A58%3A16.414&apps-status=1';    
    $topicName = 'topic1';
    $message = 'new url : http://indiatimes.com/pubsub_newurl1.php';
    publish_message($pubsub, $projectId, $topicName, $message);
}



/*******

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
        //var_dump($message->info());

        echo '<br>';
        // Acknowledge the Pub/Sub message has been received, so it will not be pulled multiple times.
        $subscription->acknowledge($message);
        
    }

    echo $subscriptionName.' ends here'.'<br><br>';
}



$subscriptionName = 'sub1';
pull_messages($pubsub, $projectId, $subscriptionName);
$subscriptionName = 'sub2';
pull_messages($pubsub, $projectId, $subscriptionName);

******/