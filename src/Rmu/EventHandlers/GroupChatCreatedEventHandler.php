<?php

declare(strict_types=1);

namespace Akinoriakatsuka\CqrsEsExamplePhp\Rmu\EventHandlers;

use Akinoriakatsuka\CqrsEsExamplePhp\Rmu\GroupChatDao;
use Psr\Log\LoggerInterface;

/**
 * GroupChatCreatedイベントハンドラ
 */
class GroupChatCreatedEventHandler
{
    public function __construct(
        private GroupChatDao $dao,
        private LoggerInterface $logger
    ) {
    }

    /**
     * GroupChatCreatedイベントを処理
     *
     * @param array $event イベントデータ (payload JSONをデコードしたもの)
     * @return void
     * @throws \Exception
     */
    public function handle(array $event): void
    {
        $this->logger->info('GroupChatCreatedEventHandler: start', ['event' => $event]);

        // グループチャットIDを取得
        $groupChatId = $event['aggregate_id']['value'];

        // グループチャット名を取得
        $name = $event['name']['value'];

        // executorIdを取得 (オーナー)
        $executorId = $event['executor_id']['value'];

        // 発生日時を取得 (ミリ秒 -> Y-m-d H:i:s形式に変換)
        $occurredAtMs = $event['occurred_at'];
        $occurredAt = $this->convertToDateTime($occurredAtMs);

        // グループチャットを作成
        $this->dao->createGroupChat($groupChatId, $name, $executorId, $occurredAt);
        $this->logger->info('GroupChatCreatedEventHandler: group chat created');

        // Administratorメンバーを追加
        $members = $event['members']['values'];
        if (count($members) > 0) {
            $administrator = $members[0]; // 最初のメンバーがAdministrator
            $memberId = $administrator['id']['value'];
            $userAccountId = $administrator['user_account_id']['value'];
            $role = $this->convertRole($administrator['role']);

            $this->dao->addMember($memberId, $groupChatId, $userAccountId, $role, $occurredAt);
            $this->logger->info('GroupChatCreatedEventHandler: administrator member added');
        }

        $this->logger->info('GroupChatCreatedEventHandler: finished');
    }

    /**
     * ミリ秒エポック時刻をY-m-d H:i:s形式に変換
     */
    private function convertToDateTime(int $milliseconds): string
    {
        $seconds = intdiv($milliseconds, 1000);
        $dateTime = new \DateTime('@' . $seconds);
        return $dateTime->format('Y-m-d H:i:s');
    }

    /**
     * ロール値を文字列に変換
     * 0 = MEMBER, 1 = ADMINISTRATOR
     */
    private function convertRole(int $roleValue): string
    {
        return match ($roleValue) {
            0 => 'member',
            1 => 'admin',
            default => throw new \InvalidArgumentException("Invalid role value: {$roleValue}"),
        };
    }
}
