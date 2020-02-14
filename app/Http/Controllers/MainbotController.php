<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;
use App\Page;
use Socialite;
use App\Store;
use App\Conversation;
use Auth;
use Cookie;

class MainbotController extends Controller
{

    public function receive(Request $request)
    {
        $data = $request->all();
        //get the user’s id
        $pageId = $data["entry"][0]["id"];
        $id = $data["entry"][0]["messaging"][0]["sender"]["id"];
        $idr = $data["entry"][0]["messaging"][0]["recipient"]["id"];
        $msg = $data["entry"][0]["messaging"][0]["message"]["text"];
        $res = $this->getResponse($msg, $id, $idr);
        if ($res == '')
            $res = "Sorry i don't understand";
        $checkPage = Page::Where('page_id', $pageId)->first();
        $this->sendTextMessage($id, $res, $checkPage->page_token);
    }

    private function sendTextMessage($recipientId, $messageText, $pageToken)
    {   
        
        $messageData = [
            "recipient" => [
                "id" => $recipientId
            ],
            "message" => [
                "text" => $messageText
            ]
        ];
        
        
/*
        $messageData = [
            'recipient' => 
            [
              'id' => $recipientId,
            ],
            'messaging_type' => 'RESPONSE',
            'message' => 
            [
              'text' => $messageText,
              'quick_replies' => 
              [
                [
                  'content_type' => 'text',
                  'title' => 'Red',
                  'payload' => 'This is the value you get back',
                  'image_url' => 'http://ecap.pcd.go.th/public/assets/img/green.png',
                ],
                [
                    'content_type' => 'text',
                    'title' => 'Bilal',
                    'payload' => 'This is the value you get back',
                    'image_url' => 'http://ecap.pcd.go.th/public/assets/img/gray.png',
                ],
                [
                    'content_type' => 'text',
                    'title' => 'Yassine',
                    'payload' => 'This is the value you get back',
                    'image_url' => 'http://ecap.pcd.go.th/public/assets/img/yello.png',
                ],
                [
                    'content_type' => 'text',
                    'title' => 'Rida',
                    'payload' => 'This is the value you get back',
                    'image_url' => 'http://ecap.pcd.go.th/public/assets/img/red.png',
                ],            
              ],
            ],
        ];
        */


       // $json = 


    // $cookiestore = tempnam(storage_path('/cookies'), '_cookiejar_' );
    
    $ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$pageToken); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
        // curl_setopt( $ch, CURLOPT_COOKIEFILE, $cookiestore );
        // curl_setopt( $ch, CURLOPT_COOKIEJAR, $cookiestore );
        curl_exec($ch);

        /*
        $client = new Client();
        $result = $client->GET('https://graph.facebook.com/v2.6/me/messages?access_token='.$pageToken, [
            'form_params' => $messageData,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]
        ]);
        */
    }

    public function getToken($pageId){

        $checkPage = Page::Where('page_id', $pageId)->first();

        if ($checkPage)
        {
            $client = new Client();
            $result = $client->get('https://graph.facebook.com/v6.0/'.$pageId.'?fields=access_token', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer '.Session::get('access_token')
                ]
            ]);

            $jsonResult = json_decode($result->getBody());
            return $jsonResult->access_token;
        }
        
    }

    // to show list view of pages
    public function showListPages(){

        $client = new Client();
        $result = $client->get('https://graph.facebook.com/v6.0/3693880870623784/accounts', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.Session::get('access_token')
            ]
        ]);

        $pages = json_decode($result->getBody());

        return view('home', compact('pages'));
    }

    // just a redirection to view
    public function pageLinked(){
        return view('done');
    }

    // link page if not added
    public function link($page_id, $page_name, $page_token){
        
        $checkPage = Page::Where('page_id', $page_id)->first();
        if (!$checkPage){
            $page = new Page;
            $page->page_name = $page_name;
            $page->page_id   = $page_id;
            $page->page_token= $page_token;

            if ($page->save())
                return redirect()->route('done');

        }
        else
            return redirect()->route('listpages');
        
        $client = new Client();
        $result = $client->get('https://graph.facebook.com/v6.0/3693880870623784/accounts', [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.Session::get('access_token')
            ]
        ]);
    }

    public function getResponse($msg, $sender, $recipient){

        $client = new Client();
        $result = $client->get('https://api.wit.ai/message?v=20200206&q='.$msg, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ZBQE6FF7EGSC3ILKNQWBZ2MIYYILBUJL'
            ]
        ]);
        // Parsing body content to json.
        $dataIntent = json_decode($result->getBody()); 

        foreach ($dataIntent->entities as $key => $val)
        {
            if ($key == 'greetings'){
                return "Hello, how can i help you ?". Session::get('greetings');
                break;
            }
            else if ($val[0]->value == 'learn'){
                return "What do you learn ?";
             break;
            }
            
            else if ($key == 'langs'){
                foreach($val as $key2 => $val2){
                    if($val2->confidence * 100 > 70){
                        $this->setConversation($sender,$recipient, "What do you learn ?", $msg);
                        return "What's your level ?";
                        break;
                    }
                }
            }
            else if ($key == 'levels'){
                $this->setConversation($sender,$recipient, "What's your level ?", $msg);
                return "Where are you from ?";
            }
            else if ($key == 'location'){
                $this->setConversation($sender,$recipient, "Where are you from ?", $msg);
                return "Please give us your email address.";
                break;
            }
            else if ($key == 'email'){
                $this->setConversation($sender,$recipient, "Please give us your email address.", $msg);
                return "Please enter you phone number.";
                break;
            }
            else if($key == 'phone_number')
            {
                $this->setConversation($sender,$recipient, "Please enter you phone number.", $msg);
                $conversation = Conversation::Where('sender', $sender)->Where('recipient', $recipient)->GET();
                return ('You want to learn : '. $conversation[0]->answer . 
                    ' Your level is : '. $conversation[1]->answer .
                    ' Your are from : '. $conversation[2]->answer .
                    ' Your address email : '. $conversation[3]->answer .
                    ' Your phone number : '. $conversation[4]->answer
            );
                break;
            }
        }

    }

    public function getInformation(){
        $quikreply = [
            'recipient' => 
            [
              'id' => '<PSID>',
            ],
            'messaging_type' => 'RESPONSE',
            'message' => 
            [
              'text' => 'Pick a color:',
              'quick_replies' => 
              [
                [
                  'content_type' => 'text',
                  'title' => 'Red',
                  'payload' => '<POSTBACK_PAYLOAD>',
                  'image_url' => 'http://example.com/img/red.png',
                ],
              ],
            ],
        ];

        return $quikreply;
    }

    public function setConversation($sender, $recipient, $question, $answer){
        
        $checkIt = Conversation::WHERE('sender', $sender)
                    ->WHERE('recipient', $recipient)
                    ->WHERE('question', $question)
                    ->GET()
                    ->COUNT();

        if ($checkIt > 0)
            {
                $update = Conversation::WHERE('sender', $sender)
                ->WHERE('recipient', $recipient)
                ->WHERE('question', $question)
                ->UPDATE(['answer' => $answer]);

                if ($update)
                    return true;
            }
        else
            {
                $conversation = new Conversation;
                $conversation->sender = $sender;
                $conversation->recipient = $recipient;
                $conversation->question = $question;
                $conversation->answer = $answer;

                if($conversation->save())
                    return true;
            }


            return false;

    }

}
