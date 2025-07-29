<?php
include 'config.php';

$update = json_decode(file_get_contents('php://input'));
$message = $update->message;
$cid = $message->chat->id;
$uid = $message->from->id;
$text = $message->text;
$name = $message->from->first_name;

$btn = json_encode(['resize_keyboard' => true, 'keyboard' => [
    [['text'=>"🎥 FutbolTV"],['text'=>"🏀 SportUZ"]],
    [['text'=>"⚽ Match Futbol 3"],['text'=>"🥅 Match Futbol 2"]]
]]);

$tasix_links = [
    "🎥 FutbolTV" => "https://tas-ix.media/player/xta/playerjs.html?file=https://cda.tas-ix.tv/futboltv/index.m3u8?token=...",
    "🏀 SportUZ" => "https://tas-ix.media/player/xta/playerjs.html?file=https://cda.tas-ix.tv/sportuz/index.m3u8?token=...",
    "⚽ Match Futbol 3" => "https://tas-ix.media/player/xta/playerjs.html?file=https://cda.tas-ix.tv/match_futbol3/index.m3u8?token=...",
    "🥅 Match Futbol 2" => "https://tas-ix.media/player/xta/playerjs.html?file=https://cda.tas-ix.tv/match_futbol2/index.m3u8?token=..."
];

$user_file = "data/users.txt";
$join_file = "data/join.txt";

if(!file_exists("data")) mkdir("data");

if(!file_exists($user_file)) file_put_contents($user_file, "");
if(!file_exists($join_file)) file_put_contents($join_file, "");

$users = explode("\n", file_get_contents($user_file));
if(!in_array($uid, $users)){
    file_put_contents($user_file, $uid."\n", FILE_APPEND);
}

$join_channels = explode("\n", trim(file_get_contents($join_file)));
if(count($join_channels) >= 1){
    foreach($join_channels as $ch){
        $check = bot('getChatMember', ['chat_id'=>$ch, 'user_id'=>$uid]);
        if($check['result']['status'] == "left" || $check['ok'] == false){
            $buttons = [['text'=>"📥 Join Channel 1",'url'=>"https://t.me/".$join_channels[0]]];
            if(isset($join_channels[1])) $buttons[] = ['text'=>"📥 Join Channel 2",'url'=>"https://t.me/".$join_channels[1]];
            $buttons[] = ['text'=>"✅ I Joined"];
            bot('sendMessage', [
                'chat_id'=>$cid,
                'text'=>"❗ Please join the required channels to continue.",
                'reply_markup'=>json_encode(['inline_keyboard'=>[$buttons]])
            ]);
            exit;
        }
    }
}

if($text == "/start"){
    bot('sendMessage', [
        'chat_id'=>$cid,
        'text'=>"👋 Hi $name! Select a channel to watch:",
        'reply_markup'=>$btn
    ]);
}

if(array_key_exists($text, $tasix_links)){
    bot('sendMessage', [
        'chat_id'=>$cid,
        'text'=>"▶️ Click the button below to watch:",
        'reply_markup'=>json_encode([
            'inline_keyboard'=>[
                [['text'=>"📺 Watch Now", 'url'=>$tasix_links[$text]]]
            ]
        ])
    ]);
}

if($text == "/panel" && $uid == ADMIN_ID){
    bot('sendMessage', [
        'chat_id'=>$cid,
        'text'=>"👮 Admin Panel:\n/setjoin - Set force join\n/sendall - Broadcast message",
    ]);
}

if($text == "/setjoin" && $uid == ADMIN_ID){
    file_put_contents("data/$uid-setjoin.step", "awaiting");
    bot('sendMessage', [
        'chat_id'=>$cid,
        'text'=>"✍️ Send up to 2 channel usernames (without @), one per line."
    ]);
}

if(file_exists("data/$uid-setjoin.step")){
    if($uid == ADMIN_ID){
        unlink("data/$uid-setjoin.step");
        file_put_contents($join_file, $text);
        bot('sendMessage', [
            'chat_id'=>$cid,
            'text'=>"✅ Force join channels updated."
        ]);
    }
}

if($text == "/sendall" && $uid == ADMIN_ID){
    file_put_contents("data/$uid-broadcast.step", "awaiting");
    bot('sendMessage', ['chat_id'=>$cid, 'text'=>"📢 Send the message to broadcast"]);
}

if(file_exists("data/$uid-broadcast.step")){
    unlink("data/$uid-broadcast.step");
    $all_users = explode("\n", file_get_contents($user_file));
    $sent = 0;
    foreach($all_users as $id){
        bot('sendMessage', ['chat_id'=>$id, 'text'=>$text]);
        $sent++;
    }
    bot('sendMessage', ['chat_id'=>$cid, 'text'=>"✅ Sent to $sent users"]);
}