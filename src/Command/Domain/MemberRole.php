<?php

namespace Akinoriakatsuka\CqrsEsExamplePhp\Command\Domain;

enum MemberRole: int {
    case MEMBER_ROLE = 0;
    case ADMIN_ROLE = 1;

    public function toString(): string {
        return match ($this) {
            self::MEMBER_ROLE => "member",
            self::ADMIN_ROLE => "admin",
        };
    }

    public static function fromString(string $role): MemberRole {
        return match ($role) {
            "member" => self::MEMBER_ROLE,
            "admin" => self::ADMIN_ROLE,
            default => throw new \InvalidArgumentException("unknown role: $role"),
        };
    }
}
