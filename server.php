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

$history = array();

foreach ($prompts as $prompt) {

    $enriched_prompt = <<<INTRO
This is a conversation between an AI chat bot and a human. Every input from the human starts with "New prompt:" and ends at the end
of the text. There is also a history of previous prompts and responsed from the chat bot. 
Every previous prompt starts with "Prompt:" and every previous response to that prompt starts
with "Response:". Answer in the context of the existing prompts and responses below, your response should not contain the words
 "response", "answer" or any similar words, only return the actual response. Do not generate prompts, only generated responses.
 
INTRO;

    $enriched_prompt .= PHP_EOL . PHP_EOL;

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
                // Done event, close connection and return false to stop further processing
               // curl_close($ch);
                return strlen($data);
            }

            // cut off the 'data: ' prefix
            $line = substr($line, 6);

            $response = json_decode($line, true);

            $actual_data = $response['choices'][0]['text'];

            $this_response .= $actual_data;
            echo $actual_data;
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
}


echo PHP_EOL . PHP_EOL . ' HISTORY: ' . PHP_EOL . PHP_EOL;

// Output history
foreach ($history as $item) {
    echo 'Prompt: ' . $item['prompt'] . PHP_EOL;
    echo 'Response: ' . print_r($item['response'], true) . PHP_EOL;
    echo '------------------------------' . PHP_EOL;
}