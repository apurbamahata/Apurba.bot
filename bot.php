<?php
// Telegram Bot Token and Group ID
define('BOT_TOKEN', '8035060155:AAETIlb-0Ifjyj5yePmXIOs5iso6I74ImZo');
define('GROUP_ID', -1003008196522); // your group ID

// Get incoming update
$update = json_decode(file_get_contents('php://input'), true);
$message = $update['message'] ?? null;
$chat_id = $message['chat']['id'] ?? null;
$text = trim($message['text'] ?? '');

// Function to send message
function sendMessage($chat_id, $text) {
    $url = "https://api.telegram.org/bot".BOT_TOKEN."/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    file_get_contents($url . "?" . http_build_query($data));
}

// Only allow bot to work in your group
if($chat_id != GROUP_ID) exit;

// Check if message is command /like
if(stripos($text, '/like') === 0) {
    $parts = explode(' ', $text);
    
    // Validate command format
    if(count($parts) != 3) {
        sendMessage($chat_id, "❌ Incorrect format!\nUse: /like <region> <uid>\nExample: /like ind 5897937250");
        exit;
    }

    $region = $parts[1];
    $uid = $parts[2];

    // Send processing message
    sendMessage($chat_id, "⏳ Processing your request...");

    // Call API
    $api_url = "https://godjexarxfreefiremaxlikes.vercel.app/like?server_name={$region}&uid={$uid}&key=GARENA2025{$uid}";
    $api_response = file_get_contents($api_url);
    $data = json_decode($api_response, true);

    // Prepare response
    if(isset($data['status']) && $data['status'] == 1) {
        $response_text = "✅ Likes Processed!\n\n"
            ."Player: <b>{$data['PlayerNickname']}</b>\n"
            ."UID: <b>{$data['UID']}</b>\n"
            ."Likes Before: <b>{$data['LikesbeforeCommand']}</b>\n"
            ."Likes Added: <b>{$data['LikesGivenByAPI']}</b>\n"
            ."Likes After: <b>{$data['LikesafterCommand']}</b>\n"
            ."Expires On: <b>{$data['expire_date']}</b>";
    } else {
        $response_text = "❌ Something went wrong. Please try again later.";
    }

    sendMessage($chat_id, $response_text);
}
?>