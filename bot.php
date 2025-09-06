<?php
// Telegram Bot Token and Group ID
define('BOT_TOKEN', '8035060155:AAETIlb-0Ifjyj5yePmXIOs5iso6I74ImZo');
define('GROUP_ID', -1003008196522);

// Get incoming update
$update = json_decode(file_get_contents('php://input'), true);
$message = $update['message'] ?? null;
$chat_id = $message['chat']['id'] ?? null;
$text = trim($message['text'] ?? '');

// Function to send message and return message_id
function sendMessage($chat_id, $text) {
    $url = "https://api.telegram.org/bot".BOT_TOKEN."/sendMessage";
    $data = [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => 'HTML'
    ];
    $response = file_get_contents($url . "?" . http_build_query($data));
    $result = json_decode($response, true);
    return $result['result']['message_id'] ?? null;
}

// Function to edit message
function editMessage($chat_id, $message_id, $text) {
    $url = "https://api.telegram.org/bot".BOT_TOKEN."/editMessageText";
    $data = [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
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
    $processing_id = sendMessage($chat_id, "⏳ Processing your request...");

    // Prepare API URL
    $api_url = "https://godjexarxfreefiremaxlikes.vercel.app/like?server_name={$region}&uid={$uid}&key=GARENA2025{$uid}";

    // Call API
    $api_response = @file_get_contents($api_url);

    if(!$api_response) {
        editMessage($chat_id, $processing_id, "❌ Failed to connect to API. Please try again later.");
        exit;
    }

    $data = json_decode($api_response, true);

    if(!$data) {
        editMessage($chat_id, $processing_id, "❌ API did not return valid JSON.\nRaw response: " . $api_response);
        exit;
    }

    // Handle API response
    if(isset($data['status'])) {
        if($data['status'] == 1) {
            // SUCCESS response
            $response_text = "✅ Likes Processed!\n\n"
                ."Player: <b>{$data['PlayerNickname']}</b>\n"
                ."UID: <b>{$data['UID']}</b>\n"
                ."Likes Before Command: <b>{$data['LikesbeforeCommand']}</b>\n"
                ."Likes Given By API: <b>{$data['LikesGivenByAPI']}</b>\n"
                ."Likes After Command: <b>{$data['LikesafterCommand']}</b>\n"
                ."Expires On: <b>{$data['expire_date']}</b>";
        } elseif($data['status'] == 2) {
            // MAX LIKE response
            $response_text = "⚠️ {$data['message']}\n\n"
                ."Player: <b>{$data['PlayerNickname']}</b>\n"
                ."UID: <b>{$uid}</b>\n"
                ."Likes Now: <b>{$data['LikesNow']}</b>\n"
                ."Expires On: <b>{$data['expire_date']}</b>";
        } else {
            $response_text = "❌ Something went wrong. Please try again later.";
        }
    } else {
        $response_text = "❌ Invalid API response.";
    }

    // Edit processing message to final response
    editMessage($chat_id, $processing_id, $response_text);
}
?>