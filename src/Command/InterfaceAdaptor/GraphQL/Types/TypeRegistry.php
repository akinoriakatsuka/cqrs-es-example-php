<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\InterfaceAdaptor\GraphQL\Types;

final class TypeRegistry {
    private static ?GroupChatType $groupChatType = null;
    private static ?MemberType $memberType = null;
    private static ?MessageType $messageType = null;
    private static ?MemberRoleType $memberRoleType = null;

    public static function groupChatType(): GroupChatType {
        if (self::$groupChatType === null) {
            self::$groupChatType = new GroupChatType();
        }
        return self::$groupChatType;
    }

    public static function memberType(): MemberType {
        if (self::$memberType === null) {
            self::$memberType = new MemberType();
        }
        return self::$memberType;
    }

    public static function messageType(): MessageType {
        if (self::$messageType === null) {
            self::$messageType = new MessageType();
        }
        return self::$messageType;
    }

    public static function memberRoleType(): MemberRoleType {
        if (self::$memberRoleType === null) {
            self::$memberRoleType = new MemberRoleType();
        }
        return self::$memberRoleType;
    }
}
