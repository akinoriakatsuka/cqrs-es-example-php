<?php

declare(strict_types=1);

namespace App\Command\InterfaceAdaptor\GraphQL;

use App\Command\Processor\GroupChatCommandProcessor;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Schema as GraphQLSchema;

class Schema
{
    public static function build(GroupChatCommandProcessor $processor): GraphQLSchema
    {
        // CreateGroupChatInput型
        $create_group_chat_input = new InputObjectType([
            'name' => 'CreateGroupChatInput',
            'fields' => [
                'name' => Type::nonNull(Type::string()),
                'executorId' => Type::nonNull(Type::string()),
            ],
        ]);

        // CreateGroupChatPayload型
        $create_group_chat_payload = new ObjectType([
            'name' => 'CreateGroupChatPayload',
            'fields' => [
                'groupChatId' => Type::nonNull(Type::id()),
            ],
        ]);

        // RenameGroupChatInput型
        $rename_group_chat_input = new InputObjectType([
            'name' => 'RenameGroupChatInput',
            'fields' => [
                'groupChatId' => Type::nonNull(Type::id()),
                'name' => Type::nonNull(Type::string()),
                'executorId' => Type::nonNull(Type::string()),
            ],
        ]);

        // RenameGroupChatPayload型
        $rename_group_chat_payload = new ObjectType([
            'name' => 'RenameGroupChatPayload',
            'fields' => [
                'groupChatId' => Type::nonNull(Type::id()),
            ],
        ]);

        // DeleteGroupChatInput型
        $delete_group_chat_input = new InputObjectType([
            'name' => 'DeleteGroupChatInput',
            'fields' => [
                'groupChatId' => Type::nonNull(Type::id()),
                'executorId' => Type::nonNull(Type::string()),
            ],
        ]);

        // DeleteGroupChatPayload型
        $delete_group_chat_payload = new ObjectType([
            'name' => 'DeleteGroupChatPayload',
            'fields' => [
                'groupChatId' => Type::nonNull(Type::id()),
            ],
        ]);

        // AddMemberInput型
        $add_member_input = new InputObjectType([
            'name' => 'AddMemberInput',
            'fields' => [
                'groupChatId' => Type::nonNull(Type::id()),
                'userAccountId' => Type::nonNull(Type::string()),
                'role' => Type::nonNull(Type::string()),
                'executorId' => Type::nonNull(Type::string()),
            ],
        ]);

        // AddMemberPayload型
        $add_member_payload = new ObjectType([
            'name' => 'AddMemberPayload',
            'fields' => [
                'groupChatId' => Type::nonNull(Type::id()),
            ],
        ]);

        // RemoveMemberInput型
        $remove_member_input = new InputObjectType([
            'name' => 'RemoveMemberInput',
            'fields' => [
                'groupChatId' => Type::nonNull(Type::id()),
                'userAccountId' => Type::nonNull(Type::string()),
                'executorId' => Type::nonNull(Type::string()),
            ],
        ]);

        // RemoveMemberPayload型
        $remove_member_payload = new ObjectType([
            'name' => 'RemoveMemberPayload',
            'fields' => [
                'groupChatId' => Type::nonNull(Type::id()),
            ],
        ]);

        // PostMessageInput型
        $post_message_input = new InputObjectType([
            'name' => 'PostMessageInput',
            'fields' => [
                'groupChatId' => Type::nonNull(Type::id()),
                'content' => Type::nonNull(Type::string()),
                'executorId' => Type::nonNull(Type::string()),
            ],
        ]);

        // PostMessagePayload型
        $post_message_payload = new ObjectType([
            'name' => 'PostMessagePayload',
            'fields' => [
                'groupChatId' => Type::nonNull(Type::id()),
                'messageId' => Type::nonNull(Type::id()),
            ],
        ]);

        // EditMessageInput型
        $edit_message_input = new InputObjectType([
            'name' => 'EditMessageInput',
            'fields' => [
                'groupChatId' => Type::nonNull(Type::id()),
                'messageId' => Type::nonNull(Type::id()),
                'content' => Type::nonNull(Type::string()),
                'executorId' => Type::nonNull(Type::string()),
            ],
        ]);

        // EditMessagePayload型
        $edit_message_payload = new ObjectType([
            'name' => 'EditMessagePayload',
            'fields' => [
                'groupChatId' => Type::nonNull(Type::id()),
            ],
        ]);

        // DeleteMessageInput型
        $delete_message_input = new InputObjectType([
            'name' => 'DeleteMessageInput',
            'fields' => [
                'groupChatId' => Type::nonNull(Type::id()),
                'messageId' => Type::nonNull(Type::id()),
                'executorId' => Type::nonNull(Type::string()),
            ],
        ]);

        // DeleteMessagePayload型
        $delete_message_payload = new ObjectType([
            'name' => 'DeleteMessagePayload',
            'fields' => [
                'groupChatId' => Type::nonNull(Type::id()),
            ],
        ]);

        // Mutation型
        $mutation_type = new ObjectType([
            'name' => 'Mutation',
            'fields' => [
                'createGroupChat' => [
                    'type' => $create_group_chat_payload,
                    'args' => [
                        'input' => Type::nonNull($create_group_chat_input),
                    ],
                    'resolve' => function ($root, $args) use ($processor) {
                        $input = $args['input'];
                        $group_chat_id = $processor->createGroupChat(
                            $input['name'],
                            $input['executorId']
                        );

                        return [
                            'groupChatId' => $group_chat_id,
                        ];
                    },
                ],
                'renameGroupChat' => [
                    'type' => $rename_group_chat_payload,
                    'args' => [
                        'input' => Type::nonNull($rename_group_chat_input),
                    ],
                    'resolve' => function ($root, $args) use ($processor) {
                        $input = $args['input'];
                        $processor->renameGroupChat(
                            $input['groupChatId'],
                            $input['name'],
                            $input['executorId']
                        );

                        return [
                            'groupChatId' => $input['groupChatId'],
                        ];
                    },
                ],
                'deleteGroupChat' => [
                    'type' => $delete_group_chat_payload,
                    'args' => [
                        'input' => Type::nonNull($delete_group_chat_input),
                    ],
                    'resolve' => function ($root, $args) use ($processor) {
                        $input = $args['input'];
                        $processor->deleteGroupChat(
                            $input['groupChatId'],
                            $input['executorId']
                        );

                        return [
                            'groupChatId' => $input['groupChatId'],
                        ];
                    },
                ],
                'addMember' => [
                    'type' => $add_member_payload,
                    'args' => [
                        'input' => Type::nonNull($add_member_input),
                    ],
                    'resolve' => function ($root, $args) use ($processor) {
                        $input = $args['input'];
                        $processor->addMember(
                            $input['groupChatId'],
                            $input['userAccountId'],
                            $input['role'],
                            $input['executorId']
                        );

                        return [
                            'groupChatId' => $input['groupChatId'],
                        ];
                    },
                ],
                'removeMember' => [
                    'type' => $remove_member_payload,
                    'args' => [
                        'input' => Type::nonNull($remove_member_input),
                    ],
                    'resolve' => function ($root, $args) use ($processor) {
                        $input = $args['input'];
                        $processor->removeMember(
                            $input['groupChatId'],
                            $input['userAccountId'],
                            $input['executorId']
                        );

                        return [
                            'groupChatId' => $input['groupChatId'],
                        ];
                    },
                ],
                'postMessage' => [
                    'type' => $post_message_payload,
                    'args' => [
                        'input' => Type::nonNull($post_message_input),
                    ],
                    'resolve' => function ($root, $args) use ($processor) {
                        $input = $args['input'];
                        $message_id = $processor->postMessage(
                            $input['groupChatId'],
                            $input['content'],
                            $input['executorId']
                        );

                        return [
                            'groupChatId' => $input['groupChatId'],
                            'messageId' => $message_id,
                        ];
                    },
                ],
                'editMessage' => [
                    'type' => $edit_message_payload,
                    'args' => [
                        'input' => Type::nonNull($edit_message_input),
                    ],
                    'resolve' => function ($root, $args) use ($processor) {
                        $input = $args['input'];
                        $processor->editMessage(
                            $input['groupChatId'],
                            $input['messageId'],
                            $input['content'],
                            $input['executorId']
                        );

                        return [
                            'groupChatId' => $input['groupChatId'],
                        ];
                    },
                ],
                'deleteMessage' => [
                    'type' => $delete_message_payload,
                    'args' => [
                        'input' => Type::nonNull($delete_message_input),
                    ],
                    'resolve' => function ($root, $args) use ($processor) {
                        $input = $args['input'];
                        $processor->deleteMessage(
                            $input['groupChatId'],
                            $input['messageId'],
                            $input['executorId']
                        );

                        return [
                            'groupChatId' => $input['groupChatId'],
                        ];
                    },
                ],
            ],
        ]);

        return new GraphQLSchema([
            'mutation' => $mutation_type,
        ]);
    }
}
