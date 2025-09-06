<?php
// ===== CONFIGURATION =====
$botToken = "8035060155:AAETIlb-0Ifjyj5yePmXIOs5iso6I74ImZo"; 
$website  = "https://api.telegram.org/bot" . $botToken;
$groupId  = -1003008196522; // your group ID
$channelUsername = "fflikeforevetyone"; // without @

// ===== GET UPDATES =====
$update = json_decode(file_get_contents("php://input"), true);
$chatId = $update["message"]["chat"]["id"] ?? null;
$text   = $update["message"]["text"] ?? "";
$userId = $update["message"]["from"]["id"] ?? null;

// ===== FUNCTION TO SEND MESSAGE =====
function sendMessage($chatId, $text) {
    global $website;
    file_get_contents($website."/sendMessage?chat_id=".$chatId."&text=".urlencode($text));
}

// ===== FUNCTION TO CHECK CHANNEL JOIN =====
function isMember($userId) {
    global $website, $channelUsername;
    $res = file_get_contents($website."/getChatMember?chat_id=@".$channelUsername."&user_id=".$userId);
    $res = json_decode($res, true);
    if (isset($res["result"]["status"])) {
        $status = $res["result"]["status"];
        return in_array($status, ["member", "administrator", "creator"]);
    }
    return false;
}

// ===== ONLY WORK IN YOUR GROUP =====
if ($chatId != $groupId) {
    exit;
}

// ===== COMMAND HANDLING =====
if (strpos($text, "/like") === 0) {
    $parts = explode(" ", $text);

    // Check if user joined channel
    if (!isMember($userId)) {
        sendMessage($chatId, "⚠️ Join our channel to use this bot: https://t.me/".$channelUsername);
        exit;
    }

    // Validate format
    if (count($parts) != 3) {
        sendMessage($chatId, "❌ Wrong format!\n✅ Correct usage: /like <region> <uid>");
        exit;
    }

    $region = $parts[1];
    $uid    = $parts[2];

    // Send processing message
    $processingMsg = sendMessage($chatId, "⏳ Processing your request... Please wait!");

    // Call API
    $apiUrl = "https://godjexarxfreefiremaxlikes.vercel.app/like?server_name=".$region."&uid=".$uid."&key=GARENA2025".$uid;
    $response = file_get_contents($apiUrl);
    $data = json_decode($response, true);

    if ($data && isset($data["status"]) && $data["status"] == 1) {
        $msg = "✅ Likes Added Successfully!\n\n"
             . "👤 Nickname: ".$data["PlayerNickname"]."\n"
             . "🆔 UID: ".$data["UID"]."\n"
             . "👍 Likes Before: ".$data["LikesbeforeCommand"]."\n"
             . "✨ Likes Added: ".$data["LikesGivenByAPI"]."\n"
             . "🔥 Likes After: ".$data["LikesafterCommand"]."\n"
             . "📅 Expire: ".$data["expire_date"]."\n\n"
             . "👑 Owner: @Legend_official0";
    } else {
        $msg = "❌ Failed to process request. Please try again later!\n👑 Owner: @Legend_official0";
    }

    sendMessage($chatId, $msg);
}
?>