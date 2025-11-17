<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Models\Message;

$messages = Message::where('title','like','%arama deneme%')->get();
if($messages->isEmpty()){
    echo "No matching messages found\n";
    exit(0);
}
foreach($messages as $m){
    echo $m->id . ' | ' . $m->title . "\n";
}
