<?php

$config = require 'config.php';

$api_key = $config['apikey'];
$base_url = 'https://api.openai.com/v1/completions';

$prompts = array(
//    'What is five times three?',
//    'Multiply that by two',
//    'Please explain how you calculated the previous two responses',
    'Explain in 50 words or less why the sky is blue',
    'How many words did you use?',
    'Try again with only 20 words',
);


$prompt = $_POST['message'] ?? null;
$prompt = trim($prompt);

if (empty($prompt)) {
    echo 'No prompt sent.';
    exit;
}

$history = array();



$enriched_prompt = '';

foreach ( $history as $key => $pair ) {
    $enriched_prompt .= 'Prompt:' . $key . ': ' . $pair['prompt'] . PHP_EOL;
    $enriched_prompt .= 'Response:' . $key . ': ' . $pair['response'] . PHP_EOL;
}

//$enriched_prompt .= 'Please only respond to the new prompt below: ' . PHP_EOL;

$enriched_prompt .= PHP_EOL . 'New prompt: ' . $prompt . PHP_EOL;
$enriched_prompt .= 'Response: ';




$data = array(
    'model' => 'text-davinci-003',
    'prompt' => $enriched_prompt,
    //'max_tokens' => 7,
    'temperature' => 0.5,
    //'top_p' => 0.1,
    'stop' => [
        'Prompt:',
        'Response:',
    ],
    'stream' => true,
    'max_tokens' => 2048,
);

//    echo '------------------------------' . PHP_EOL;
//    echo 'Request params: ' . PHP_EOL;
//    var_dump($data);
$data_string = json_encode($data);

$headers = array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
);

$this_response = '';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $base_url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) use (&$this_response) {
    $lines = explode("\n", $data);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line == '') {
            continue;
        }
        if ($line == 'data: [DONE]') {
            return strlen($data);
        }

        // cut off the 'data: ' prefix
        $line = substr($line, 6);

        $response = json_decode($line, true);

        $actual_data = $response['choices'][0]['text'];

        $this_response .= $actual_data;
        echo $actual_data;
        flush();
        ob_flush();
    }
    return strlen($data);
});

$result = curl_exec($ch);
$error = curl_error($ch);

curl_close($ch);

if ($error) {
    echo 'Error: ' . $error . PHP_EOL;
} else {

    //$response = json_decode($result, true);
    $history[] = array(
        'prompt' => $prompt,
        'response' => trim( $this_response ),
    );
}


//echo PHP_EOL . PHP_EOL . ' HISTORY: ' . PHP_EOL . PHP_EOL;
//
//// Output history
//foreach ($history as $item) {
//    echo 'Prompt: ' . $item['prompt'] . PHP_EOL;
//    echo 'Response: ' . print_r($item['response'], true) . PHP_EOL;
//    echo '------------------------------' . PHP_EOL;
//}