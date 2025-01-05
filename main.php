<?php

require __DIR__ . '/../api/vendor/autoload.php';
require __DIR__ . '/customer.php';

use skrtdev\NovaGram\Bot;
use skrtdev\Telegram\Message;
use skrtdev\Telegram\Invoice;
use skrtdev\Telegram\CallbackQuery;

define("BOT_TOKEN", '7931682226:AAEAXHqqgqnh9jF8lSm4YyOHlA_AYyLMfEw');
define("DEVELOPER_ID", 7630124540);
define("CHANNEL_ID", -1002252209432);
define("LOGGING_ID", --1002349588672);

$Bot = new Bot(BOT_TOKEN, [
    'parse_mode' => 'MarkdownV2',
    'debug' => LOGGING_ID,
    'skip_old_updates' => true
]);

$Bot->addErrorHandler(function (Throwable $e) use ($Bot) {
    $Bot->debug((string)$e);
});

$Bot->onCommand('start', function (Message $message) use ($Bot) {
    if($message->chat->type != 'private') return;
    $customer = new Customer($message->from->id);

    $chat = $Bot->getChat(CHANNEL_ID);
    $response = $Bot->getChatMember(CHANNEL_ID, $message->from->id);
    if(!in_array($response->status, ['member', 'administrator', 'creator'])) {
        $message->reply("Join our *Channel* in order to continue\.\nthen send /start", [
            'reply_markup' => json_encode([
                'resize_keyboard' => true,
                'inline_keyboard' => [
                    [
                        [
                            'text' => "{$chat->title}",
                            'url' => "{$chat->invite_link}"
                        ]
                    ]
                ]
            ])
        ]);
        return;
    }

    $msg = "Welcome to *CPMAizal*\!\nWe're excited to have you on board\.\n";
    $msg .= "Here are some important details about your account:\n\n";
    $msg .= "*Telegram ID*: `{$customer->getTelegramID()}`\n";
    $msg .= "*Access Key*: ||{$customer->getAccessKey()}||\n";
    $msg .= "*Balance*: `" . ($customer->getIsUnlimited()? 'Unlimited' : number_format($customer->getCredits())) . "`\n\n";
    $msg .= "Feel free to explore the features we offer\.\n";
    $msg .= "If you have any questions or need assistance, just let us know\.\n\n";
    $msg .= "Enjoy your experience\!";
    $message->reply($msg, [
        'reply_markup'=> json_encode([
            'resize_keyboard' => true,
            'inline_keyboard' => [
                [
                    [
                        'text' => 'Revoke Access Key',
                        'callback_data' => 'revoke_access_key'
                    ]
                ],
                // [
                //     [
                //         'text' => 'Buy Credits',
                //         'callback_data' => 'get_credits'
                //     ]
                // ]
            ]
        ])
    ]);
});

$Bot->onCallbackData('back_home', function (CallbackQuery $callback_query) {
    $customer = new Customer($callback_query->from->id);

    $msg = "Hello, and welcome to *CPMAizal*\!\nWe're excited to have you on board\.\n\n";
    $msg .= "Here are some important details about your account:\n";
    $msg .= "*Telegram ID*: `{$customer->getTelegramID()}`\n";
    $msg .= "*Access Key*: ||{$customer->getAccessKey()}||\n";
    $msg .= "*Balance*: `" . ($customer->getIsUnlimited()? 'Unlimited' : number_format($customer->getCredits())) . "`\n\n";
    $msg .= "Feel free to explore the features we offer\.\n";
    $msg .= "If you have any questions or need assistance, just let us know\.\n\n";
    $msg .= "Enjoy your experience\!";
    $callback_query->message->editText($msg, [
        'reply_markup'=> json_encode([
            'resize_keyboard' => true,
            'inline_keyboard' => [
                [
                    [
                        'text' => 'Revoke Access Key',
                        'callback_data' => 'revoke_access_key'
                    ]
                ],
                // [
                //     [
                //         'text' => 'Buy Credits',
                //         'callback_data' => 'get_credits'
                //     ]
                // ]
            ]
        ])
    ]);
});

$Bot->onCallbackData('revoke_access_key', function (CallbackQuery $callback_query) {
    $customer = new Customer($callback_query->from->id);
    $nak = $customer->revokeAccessKey();
    $msg = "Your new Access Key is: ||{$nak}||";
    $callback_query->answer('Your Access Key Changed Successfully !');
    $callback_query->message->editText($msg, [
        'reply_markup' => json_encode([
            'resize_keyboard' => true,
            'inline_keyboard' => [
                [
                    [
                        'text' => 'back 🔙',
                        'callback_data' => "back_home"
                    ]
                ]
            ]
        ])
    ]);
});

// $Bot->onCallbackData('get_credits', function (CallbackQuery $callback_query) {
//     $msg = "Here are the prices packages:";
//     $callback_query->message->editText($msg, [
//         'reply_markup' => json_encode([
//             'resize_keyboard' => true,
//             'inline_keyboard' => [
//                 [
//                     [
//                         'text' => 'Buy 4k Credits',
//                         'callback_data' => "buy_credits:4k:50"
//                     ],
//                     [
//                         'text' => 'Buy 8k Credits',
//                         'callback_data' => "buy_credits:8k:150"
//                     ]
//                 ],
//                 [
//                     [
//                         'text' => 'Buy 15k Credits',
//                         'callback_data' => "buy_credits:15k:250"
//                     ],
//                     [
//                         'text' => 'Buy 35k Credits',
//                         'callback_data' => "buy_credits:35k:500"
//                     ]
//                 ],
//                 [
//                     [
//                         'text' => 'Buy 45k Credits',
//                         'callback_data' => "buy_credits:45k:750"
//                     ],
//                     [
//                         'text' => 'Buy 65k Credits',
//                         'callback_data' => "buy_credits:65k:1000"
//                     ]
//                 ],
//                 [
//                     [
//                         'text' => 'Buy Unlimited Credits',
//                         'callback_data' => "buy_credits:unlimited:1500"
//                     ]
//                 ],
//                 [
//                     [
//                         'text' => 'back 🔙',
//                         'callback_data' => "back_home"
//                     ]
//                 ]
//             ]
//         ])
//     ]);
// });

// $Bot->onCallbackQuery(function (CallbackQuery $callback_query) use ($Bot) {
//     if(str_starts_with($callback_query->data, 'buy_credits')) {
//         $Bot->deleteMessage($callback_query->message->chat->id, $callback_query->message->message_id);
//         $split = explode(":", $callback_query->data);//$callback_query->data;
//         $amount = $split[1];
//         $price = $split[2];
//         $Bot->sendInvoice(
//             $callback_query->from->id,
//             "Credits Purchasement",
//             "Thank you for your order!\nThis invoice confirms your purchase of Credits for CPMNuker.",
//             md5("{$callback_query->from->id}-{$price}-{$amount}"),
//             null, null,
//             "XTR",
//             [
//                 [
//                     "label" => "Buying {$amount} Credits.",
//                     "amount" => $price
//                 ]
//             ]
//         );
//     }
// });

// $Bot->onPreCheckoutQuery(function (CallbackQuery $callback_query) use ($Bot) {

// });

$Bot->onCommand('check', function (Message $message, array $args = []) use ($Bot) {
    $user = $Bot->getChatMember(CHANNEL_ID, $message->from->id);
    if(!in_array($user->status, ['administrator', 'creator'])) return ;
    if(in_array($message->chat->type, ['supergroup', 'group'])) $customer_id = $message->reply_to_message->from->id ?? $args[0];
    else $customer_id = $args[0];
    if(is_numeric($customer_id)) {
        $client = $Bot->getChatMember(CHANNEL_ID, $customer_id);
        if($client->user->is_bot || $customer_id == NULL){
            $message->reply("bot can't use this service \!");
        } else {
            $customer = new Customer($customer_id);
            $msg  = "Customer Information:\n\n";
            $msg .= "*Telegram ID*: `{$customer->getTelegramID()}`\n";
            if($user->status == "creator" && $message->chat->type == 'private') $msg .= "*Access Key*: ||{$customer->getAccessKey()}||\n";
            $msg .= "*Balance*: `" . ($customer->getIsUnlimited()? "Unlimited" : number_format($customer->getCredits())) . "`\n";
            $msg .= "*Blocked*: `" . ($customer->getIsBlocked()? "Yes" : "No") . "`";
            $message->reply($msg);
        }
    } else {
        $message->reply('You entered invalid data \!');
    }
});

$Bot->onCommand('give', function (Message $message, array $args = []) use ($Bot) {
    $user = $Bot->getChatMember(CHANNEL_ID, $message->from->id);
    if(!in_array($user->status, ['administrator', 'creator'])) return ;
    if(in_array($message->chat->type, ['supergroup', 'group'])){
        $customer_id = $message->reply_to_message->from->id;
        $balance = $args[0];
    } else {
        $customer_id = $args[0];
        $balance = $args[1];
    }
    $client = $Bot->getChatMember(CHANNEL_ID, $customer_id);
    if($client->user->is_bot){
        $message->reply("bot can't use this service \!");
    } elseif($user->status != "creator" && $customer_id == DEVELOPER_ID) {
        $message->reply("this is not a customer, it is my creator \!\!\!");
    } else {
        $customer = new Customer($customer_id);
        if(is_numeric($customer_id) && is_numeric($balance) && $balance > 0) {
            if(!$customer->getIsUnlimited()){
                $customer->setCredits($balance, "[+]");
                $msg = "*Balance added successfully*\n\n";
                $msg .= "old Balance: `" . number_format($customer->getCredits()) . "`\n";
                $msg .= "new Balance: `" . number_format($customer->getCredits() + $balance) . "`";
                $message->reply($msg);
                if($user->status == 'administrator'){
                    $msg = "\!\! *Admin gived Customer Credits* \!\!\n\n";
                    $msg .= "Given Credits: ||{$balance}||\n";
                    $msg .= "Admin: ||" . (isset($user->user->username)? "@{$user->user->username}" : "[{$user->user->first_name}](tg://user?id={$message->from->id})") . "||\n";
                    $msg .= "Customer: ||" . (isset($client->user->username)? "@{$client->user->username}" : "[{$customer_id}](tg://user?id={$customer_id})") . "||";
                    $Bot->sendMessage(LOGGING_ID, $msg);
                }
            } else {
                $message->reply("This Customer has the Unlimited Subscription \!");
            }
        } else {
            $message->reply('You entered invalid data \!');
        }
    }
});

$Bot->onCommand('take', function (Message $message, array $args = []) use ($Bot) {
    $user = $Bot->getChatMember(CHANNEL_ID, $message->from->id);
    if(!in_array($user->status, ['administrator', 'creator'])) return ;
    if(in_array($message->chat->type, ['supergroup', 'group'])){
        $customer_id = $message->reply_to_message->from->id;
        $balance = $args[0];
    } else {
        $customer_id = $args[0];
        $balance = $args[1];
    }
    $client = $Bot->getChatMember(CHANNEL_ID, $customer_id);
    if($client->user->is_bot){
        $message->reply("bot can't use this service \!");
    } elseif($user->status != "creator" && $customer_id == DEVELOPER_ID) {
        $message->reply("this is not a customer, it is my creator \!\!\!");
    } else {
        $customer = new Customer($customer_id);
        if(is_numeric($customer_id) && is_numeric($balance) && $balance > 0) {
            if(!$customer->getIsUnlimited()){
                if(($customer->getCredits() - $balance) <= 0){
                    $balance = 0;
                    $customer->setCredits($balance);
                } else {
                    $customer->setCredits($balance, "[-]");
                }
                $msg = "*Balance taken successfully*\n\n";
                $msg .= "old Balance: `" . number_format($customer->getCredits()) . "`\n";
                $msg .= "new Balance: `" . (($balance == 0)? "0" : number_format($customer->getCredits() - $balance)) . "`";
                $message->reply($msg);
                if($user->status == 'administrator'){
                    $msg = "\!\! *Admin took Credits from Customer* \!\!\n\n";
                    $msg .= "Taken Credits: ||{$balance}||\n";
                    $msg .= "Admin: ||" . (isset($user->user->username)? "@{$user->user->username}" : "[{$user->user->first_name}](tg://user?id={$message->from->id})") . "||\n";
                    $msg .= "Customer: ||" . (isset($client->user->username)? "@{$client->user->username}" : "[{$customer_id}](tg://user?id={$customer_id})") . "||";
                    $Bot->sendMessage(LOGGING_ID, $msg);
                }
            } else {
                $message->reply("This Customer has the Unlimited Subscription \!");
            }
        } else {
            $message->reply('You entered invalid data \!');
        }
    }
});

$Bot->onCommand('block', function (Message $message, array $args = []) use ($Bot) {
    $user = $Bot->getChatMember(CHANNEL_ID, $message->from->id);
    if(!in_array($user->status, ['administrator', 'creator'])) return ;
    if(in_array($message->chat->type, ['supergroup', 'group'])){
        $customer_id = $message->reply_to_message->from->id;
    } else {
        $customer_id = $args[0];
    }
    $client = $Bot->getChatMember(CHANNEL_ID, $customer_id);
    if($client->user->is_bot){
        $message->reply("bot can't use this service \!");
    } elseif($user->status != "creator" && $customer_id == DEVELOPER_ID) {
        $message->reply("this is not a customer, it is my creator \!\!\!");
    } else {
        if(is_numeric($customer_id)) {
            $customer = new Customer($customer_id);
            if(!$customer->getIsBlocked()){
                $customer->setIsBlocked(true);
                $message->reply("Customer Blocked successfully");
                if($user->status == 'administrator'){
                    $msg = "\!\! *Admin Blocked Customer* \!\!\n\n";
                    $msg .= "Admin: ||" . (isset($user->user->username)? "@{$user->user->username}" : "[{$user->user->first_name}](tg://user?id={$message->from->id})") . "||\n";
                    $msg .= "Customer: ||" . (isset($client->user->username)? "@{$client->user->username}" : "[{$customer_id}](tg://user?id={$customer_id})") . "||";
                    $Bot->sendMessage(LOGGING_ID, $msg);
                }
            } else {
                $message->reply("This Customer is aleady Blocked \!");
            }
        } else {
            $message->reply('You entered invalid data \!');
        }
    }
});

$Bot->onCommand('unblock', function (Message $message, array $args = []) use ($Bot) {
    $user = $Bot->getChatMember(CHANNEL_ID, $message->from->id);
    if(!in_array($user->status, ['administrator', 'creator'])) return ;
    if(in_array($message->chat->type, ['supergroup', 'group'])){
        $customer_id = $message->reply_to_message->from->id;
    } else {
        $customer_id = $args[0];
    }
    $client = $Bot->getChatMember(CHANNEL_ID, $customer_id);
    if($client->user->is_bot){
        $message->reply("bot can't use this service \!");
    } elseif($user->status != "creator" && $customer_id == DEVELOPER_ID) {
        $message->reply("this is not a customer, it is my creator \!\!\!");
    } else {
        $customer = new Customer($customer_id);
        if(is_numeric($customer_id)) {
            if($customer->getIsBlocked()){
                $customer->setIsBlocked(false);
                $message->reply("Customer Unblocked successfully");
                if($user->status == 'administrator'){
                    $msg = "\!\! *Admin Unblocked Customer* \!\!\n\n";
                    $msg .= "Admin: ||" . (isset($user->user->username)? "@{$user->user->username}" : "[{$user->user->first_name}](tg://user?id={$message->from->id})") . "||\n";
                    $msg .= "Customer: ||" . (isset($client->user->username)? "@{$client->user->username}" : "[{$customer_id}](tg://user?id={$customer_id})") . "||";
                    $Bot->sendMessage(LOGGING_ID, $msg);
                }
            } else {
                $message->reply("This Customer is not Blocked \!");
            }
        } else {
            $message->reply('You entered invalid data \!');
        }
    }
});

$Bot->onCommand('unlimited', function (Message $message, array $args = []) use ($Bot) {
    $user = $Bot->getChatMember(CHANNEL_ID, $message->from->id);
    if(!in_array($user->status, ['administrator', 'creator'])) return ;
    if(in_array($message->chat->type, ['supergroup', 'group'])){
        $customer_id = $message->reply_to_message->from->id;
    } else {
        $customer_id = $args[0];
    }
    $client = $Bot->getChatMember(CHANNEL_ID, $customer_id);
    if($client->user->is_bot){
        $message->reply("bot can't use this service \!");
    } elseif($user->status != "creator" && $customer_id == DEVELOPER_ID) {
        $message->reply("this is not a customer, it is my creator \!\!\!");
    } else {
        $customer = new Customer($customer_id);
        if(is_numeric($customer_id)) {
            if(!$customer->getIsUnlimited()){
                $customer->setIsUnlimited(true);
                $message->reply("Customer now has Unlimited Subscription");
                if($user->status == 'administrator'){
                    $msg = "\!\! *Admin gived Customer Unlimited Subscription* \!\!\n\n";
                    $msg .= "Admin: ||" . (isset($user->user->username)? "@{$user->user->username}" : "[{$user->user->first_name}](tg://user?id={$message->from->id})") . "||\n";
                    $msg .= "Customer: ||" . (isset($client->user->username)? "@{$client->user->username}" : "[{$customer_id}](tg://user?id={$customer_id})") . "||";
                    $Bot->sendMessage(LOGGING_ID, $msg);
                }
            } else {
                $message->reply("This Customer already has Unlimited Subscription \!");
            }
        } else {
            $message->reply('You entered invalid data \!');
        }
    }
});

$Bot->onCommand('limited', function (Message $message, array $args = []) use ($Bot) {
    $user = $Bot->getChatMember(CHANNEL_ID, $message->from->id);
    if(!in_array($user->status, ['administrator', 'creator'])) return ;
    if(in_array($message->chat->type, ['supergroup', 'group'])){
        $customer_id = $message->reply_to_message->from->id;
    } else {
        $customer_id = $args[0];
    }
    $client = $Bot->getChatMember(CHANNEL_ID, $customer_id);
    if($client->user->is_bot){
        $message->reply("bot can't use this service \!");
    } elseif($user->status != "creator" && $customer_id == DEVELOPER_ID) {
        $message->reply("this is not a customer, it is my creator \!\!\!");
    } else {
        $customer = new Customer($customer_id);
        if(is_numeric($customer_id)) {
            if($customer->getIsUnlimited()){
                $customer->setIsUnlimited(false);
                $message->reply("Customer now has limited Subscription");
                if($user->status == 'administrator'){
                    $msg = "\!\! *Admin took Unlimited Subscription from Customer* \!\!\n\n";
                    $msg .= "Admin: ||" . (isset($user->user->username)? "@{$user->user->username}" : "[{$user->user->first_name}](tg://user?id={$message->from->id})") . "||\n";
                    $msg .= "Customer: ||" . (isset($client->user->username)? "@{$client->user->username}" : "[{$customer_id}](tg://user?id={$customer_id})") . "||";
                    $Bot->sendMessage(LOGGING_ID, $msg);
                }
            } else {
                $message->reply("This Customer doesn't have Unlimited Subscription \!");
            }
        } else {
            $message->reply('You entered invalid data \!');
        }
    }
});

$Bot->onCommand('balance', function (Message $message) use ($Bot) {
    // if(!in_array($user->status, ['administrator', 'creator'])) return ;
    $customer_id = $message->from->id;
    $customer = new Customer($customer_id);
    $msg  = "Customer Information:\n\n";
    $msg .= "*Telegram ID*: `{$customer->getTelegramID()}`\n";
    $msg .= "*Balance*: `" . ($customer->getIsUnlimited()? "Unlimited" : number_format($customer->getCredits())) . "`\n";
    $msg .= "*Blocked*: `" . ($customer->getIsBlocked()? "Yes" : "No") . "`";
    $message->reply($msg);
});