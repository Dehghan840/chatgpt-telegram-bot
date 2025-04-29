<?php
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (isset($update["message"])) {
    $chat_id = $update["message"]["chat"]["id"];
    $user_message = $update["message"]["text"];

    $openai_api_key = "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" ; // 🔑 کلید API خودت اینجا بذار

    $data = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "system", "content" => "شما یک دستیار روان‌شناسی کودک هستید."],
            ["role" => "user", "content" => $user_message]
        ],
        "temperature" => 0.7
    ];

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $openai_api_key
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $openai_response = curl_exec($ch);
    curl_close($ch);

    $response_data = json_decode($openai_response, true);

    if (isset($response_data["choices"][0]["message"]["content"])) {
        $gpt_reply = $response_data["choices"][0]["message"]["content"];
    } else {
        $gpt_reply = "متأسفم، مشکلی در دریافت پاسخ از ChatGPT پیش آمد.";
    }

    // ارسال پاسخ به تلگرام
    $telegram_token = "7806102112:AAGmspt9e7p8qY1NMsieuqlgQhWsWisZPcA"; // 🔑 توکن ربات تلگرام
    $telegram_url = "https://api.telegram.org/bot$telegram_token/sendMessage";
    $post_fields = [
        'chat_id' => $chat_id,
        'text' => $gpt_reply
    ];

    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $telegram_url); 
    curl_setopt($ch, CURLOPT_POST, 1); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    curl_exec($ch); 
    curl_close($ch);
}
?>
