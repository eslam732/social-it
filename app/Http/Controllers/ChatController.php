<?php

namespace App\Http\Controllers;

use App\Events\SendMessageEvent;
use App\Models\Chat;
use App\Models\ChatUsers;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function sendMessage()
    {
        try {
            //check if data is not complete
            if (sendMessageRules()) {
                return sendMessageRules();
            }
            //check if the chat exists
            if (!count(Chat::where('id', request()->chatId)->get())) {
                return response()->json(['chat with this id not found'], 400);

            }
            //check if user is in this chat
            $userExistIntChat = ChatUsers::where('chat_id', request()->chatId)->where('user_id', Auth()->id())->get();

            if (!count($userExistIntChat)) {
                return response()->json(['you are not allowed to semd message to this chat'], 404);

            }

            //create the message
            $messageData = [];
            $messageData['user_id'] = Auth::user()->id;
            $messageData['message'] = request()->message;
            $messageData['chat_id'] = request()->chatId;

            $message = Message::create($messageData);
            broadcast(new SendMessageEvent(request()->chatId, $message))->toOthers();

        } catch (\Exception$e) {

            return response()->json(['some error has happened ' => $e->getMessage()], 400);

        }

        return response()->json(['messages' => $message], 200);
    }

    public function getUserChat($userId)
    {
        try {
            $authedUserId = Auth()->id();
            $user = User::find($userId);
            if (!$user) {
                return response()->json(['user not found'], 404);
            }

            $chat = DB::select(DB::raw(
                "
            SELECT chats.id as chatId
            FROM chats
            JOIN chat_users cu1 ON chats.id = cu1.chat_id
            JOIN chat_users cu2 ON chats.id = cu2.chat_id
            WHERE cu1.user_id = $authedUserId
              AND cu2.user_id = $userId
              AND chats.type = 'private'
             "
            ));
            if ($chat) {
                $message = Message::where('chat_id', [$chat[0]->chatId])->orderBy('created_at', 'desc')->limit('10')->get();
                return response()->json(['chat' => $chat, 'messages' => $message], 200);
            }

            $chat = Chat::create();

            DB::insert('INSERT INTO chat_users (user_id, chat_id)
            VALUES (?, ?), (?, ?)', [$authedUserId, $chat->id, $userId, $chat->id]);
        } catch (\Exception$e) {
            return response()->json(['error' => 'Failed to create chatdue to ' . $e->getMessage()], 500);
        }

        return response()->json(['chat' => $chat], 200);

    }
    public function getChatMessages()
    {
        try {
            if (!request()->chatId) {
                return response()->json(['chat id is required'], 404);
            }

            $chat = Chat::where('id', request()->chatId)->get();
            if (!$chat) {
                return response()->json(['chat not found'], 404);
            }

            $userExistIntChat = ChatUsers::where('chat_id', request()->chatId)->where('user_id', Auth()->id())->get();

            if (!count($userExistIntChat)) {
                return response()->json(['you are not allowed to get message from this chat'], 404);

            }

            $messages = Message::where('chat_id', request()->chatId)->with(['user' => function ($query) {
                $query->select('name', 'id', 'picture');
            }])->orderBy('created_at', 'desc')->paginate(3);} catch (\Exception$e) {
            return response()->json(['some error has happened due to' => $e->getMessage()], 500);

        }

        return response()->json(['messages' => $messages], 200);

    }
 
    public function createGroup()
    {
        try {
            if (createGroupRules()) {
                return createGroupRules();
            }

            $data = request()->all();
            if (request()->hasFile('picture')) {
                $path = request()->file('picture')->store('chatImages');
                $data['picture'] = $path;
            }

            $chat = Chat::create($data);
            $chat_id = $chat->id;
            $participants = array_unique($data['participants_id']);

            foreach ($participants as $part) {
                if (!User::find($part)) {
                    return response()->json(['the user with id ' . $part . ' is not found'], 400);

                }
                $userChat['user_id'] = $part;
                $userChat['chat_id'] = $chat_id;

                ChatUsers::create($userChat);
            }
        } catch (\Exception$e) {
            return response()->json(['some error' => $e->getMessage()], 200);

        }
        return response()->json(['group created' => $chat], 200);
    }

}
