<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Session;
use App\Page;
use Socialite;
use Auth;

class MainbotController extends Controller
{
    
    public function receive(Request $request)
    {
        $data = $request->all();
        //get the user’s id
        $pageId = $data["entry"][0]["id"];
        $id = $data["entry"][0]["messaging"][0]["sender"]["id"];
        $checkPage = Page::Where('page_id', $pageId)->first();
        $this->sendTextMessage($id, 'response :D ', $checkPage->page_token);
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


    $ch = curl_init('https://graph.facebook.com/v2.6/me/messages?access_token='.$pageToken); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($messageData));
        curl_exec($ch);
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

    public function pageLinked(){
        return view('done');
    }

    
    public function link($page_id, $page_name, $page_token){

        //return $page_token;
        
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

}
