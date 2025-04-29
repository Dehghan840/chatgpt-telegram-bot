<?php
// ثبت شروع اجرای اسکریپت
error_log("🚀 شروع اجرای اسکریپت");

$content = file_get_contents("php://input");
error_log("📩 ورودی دریافتی: " . $content);

$update = json_decode($content, true);

if (isset($update["message"])) {
    error_log("✅ پیام جدید از تلگرام دریافت شد");

    $chat_id = $update["message"]["chat"]["id"];
    $user_message = $update["message"]["text"];
    error_log("👤 پیام کاربر: " . $user_message);

    // ===== ارسال به OpenAI =====
    $openai_api_key = "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";

    $data = [
        "model" => "gpt-3.5-turbo",
        "messages" => [
            ["role" => "system", "content" => "شما یک دستیار روان‌شناسی کودک هستید."],
            ["role" => "user", "content" => $user_message]
        ],
        "temperature" => 0.7
    ];

    error_log("📡 در حال ارسال درخواست به OpenAI...");

    $ch = curl_init("https://api.openai.com/v1/chat/completions");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $openai_api_key
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    $openai_response = curl_exec($ch);

    if (curl_errno($ch)) {
        error_log("❌ خطای CURL: " . curl_error($ch));
    } else {
        error_log("📥 پاسخ دریافتی از OpenAI: " . $openai_response);
    }

    curl_close($ch);

    $response_data = json_decode($openai_response, true);

    if (isset($response_data["choices"][0]["message"]["content"])) {
        $gpt_reply = $response_data["choices"][0]["message"]["content"];
        error_log("✅ پاسخ پردازش شد: " . $gpt_reply);
    } else {
        $gpt_reply = "متأسفم، مشکلی در دریافت پاسخ از ChatGPT پیش آمد.";
        error_log("⚠️ ساختار پاسخ ChatGPT صحیح نبود");
    }

    // ===== ارسال پاسخ به تلگرام =====
    $telegram_token = "7806102112:AAGmspt9e7p8qY1NMsieuqlgQhWsWisZPcA";
    $telegram_url = "https://api.telegram.org/bot$telegram_token/sendMessage";
    $post_fields = [
        'chat_id' => $chat_id,
        'text' => $gpt_reply
    ];

    error_log("📤 در حال ارسال پاسخ به تلگرام...");

    $ch = curl_init(); 
    curl_setopt($ch, CURLOPT_URL, $telegram_url); 
    curl_setopt($ch, CURLOPT_POST, 1); 
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
    $telegram_result = curl_exec($ch); 

    if (curl_errno($ch)) {
        error_log("❌ خطای ارسال به تلگرام: " . curl_error($ch));
    } else {
        error_log("✅ پاسخ به تلگرام ارسال شد: " . $telegram_result);
    }

    curl_close($ch);
} else {
    error_log("⚠️ پیام معتبری از تلگرام دریافت نشد");
}
?>
