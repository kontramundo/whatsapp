<?php

namespace App\Livewire;

use App\Models\Chat;
use App\Models\Contact;
use App\Models\Message;
use App\Notifications\NewMessage;
use App\Notifications\UserTyping;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Livewire\Component;

class ChatComponent extends Component
{
    public $search;
    public $contactChat;
    public $chat;
    public $bodyMessage;
    public $chat_id;
    public $users;

    public function mount()
    {
        $this->users = collect();
    }

    //OYENTES
    public function getListeners()
    {
        $user_id = auth()->user()->id;
        // Private Channel
        return [
            // Private Channel
            "echo-notification:App.Models.User.{$user_id},notification" => 'render',

            // Presence Channel
            "echo-presence:chat.1,here" => 'chatHere',
            "echo-presence:chat.1,joining" => 'chatJoining',
            "echo-presence:chat.1,leaving" => 'chatLeaving',
        ];
    }

    //PROPIEDADES COMPUTADAS
    public function getContactsProperty()
    {
        return Contact::where('user_id', auth()->id())
            ->when($this->search, function ($query) {

                $query->where(function ($query) {
                    $query->where('name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('user', function ($query) {
                            $query->where('email', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->get() ?? [];
    }

    public function getMessagesProperty()
    {
        return $this->chat ? $this->chat->messages : [];
    }

    public function getChatsProperty()
    {
        return auth()->user()->chats->sortByDesc('last_message_at');
    }

    public function getUsersNotificationsProperty()
    {
        return $this->chat ? $this->chat->users->where('id', '!=', auth()->id()) : collect();
    }

    public function getActiveProperty()
    {
        return $this->users->contains($this->users_notifications->first()->id);
    }

    //CICLO DE VIDA
    public function updatedBodyMessage($value)
    {
        if ($value != '') {
            Notification::send($this->users_notifications, new UserTyping($this->chat->id, true));
        } else {
            Notification::send($this->users_notifications, new UserTyping($this->chat->id, false));
        }
    }



    //METODOS
    public function open_chat_contact(Contact $contact)
    {
        $chat = auth()->user()->chats()->whereHas('users', function ($query) use ($contact) {
            $query->where('user_id', $contact->contact_id);
        })
            ->has('users', 2)
            ->first();

        if ($chat) {
            $this->chat = $chat;
            $this->chat_id = $chat->id;
            $this->reset('bodyMessage', 'contactChat', 'search');
        } else {
            $this->contactChat = $contact;
            $this->reset('bodyMessage', 'chat', 'search');
        }

        return $chat;
    }

    public function open_chat(Chat $chat)
    {
        $this->chat = $chat;
        $this->chat_id = $chat->id;
        $this->reset('bodyMessage', 'contactChat');
    }

    public function sendMessage()
    {
        $this->validate([
            'bodyMessage' => 'required'
        ]);

        if (!$this->chat) {
            $this->chat = Chat::create();
            $this->chat_id = $this->chat->id;
            $this->chat->users()->attach([auth()->user()->id, $this->contactChat->contact_id]);
        }

        $this->chat->messages()->create([
            'body'    => $this->bodyMessage,
            'user_id' => auth()->user()->id
        ]);

        Notification::send($this->users_notifications, new UserTyping($this->chat->id, false));
        Notification::send($this->users_notifications, new NewMessage());

        $this->reset('bodyMessage', 'contactChat');
    }

    public function chatHere($users)
    {
        $this->users = collect($users)->pluck('id');
    }

    public function chatJoining($user)
    {
        $this->users->push($user['id']);
    }

    public function chatLeaving($user)
    {
        $this->users = $this->users->filter(function ($id) use ($user) {
            return $id != $user['id'];
        });
    }

    public function render()
    {
        if ($this->chat) {
            $this->dispatch('scroll-bottom');

            $this->chat->messages()->where('user_id', '!=', auth()->id())->where('is_read', false)->update([
                'is_read' => true
            ]);
        }

        return view('livewire.chat-component');
    }
}
