<?php
header("Content-Type: application/json");

if (!isset($_POST['message'])) {
    echo json_encode(["error" => "No message sent"]);
    exit;
}

$userMessage = trim($_POST['message']);

// 🔒 System instruction (aap change kar sakte ho)
$systemPrompt = "You are a helpful assistant for a website chatbot.
Answer clearly and politely.";

$data = [
    "model" => "llama3",
    "prompt" => $systemPrompt . "\nUser: " . $userMessage,
    "stream" => false
];

$ch = curl_init("http://localhost:11434/api/generate");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json"
]);

$response = curl_exec($ch);

if ($response === false) {
    echo json_encode(["error" => "Ollama not responding"]);
    exit;
}

curl_close($ch);
echo $response;
